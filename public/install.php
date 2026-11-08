<?php
/**
 * SISLIA - public/install.php (skrip install ringan)
 * Fungsi: cek lingkungan, impor schema.sql + seed.sql (+ dummy.sql opsional),
 * buat akun Admin dengan password acak (tampil SEKALI), unduh logo, kunci instalasi.
 * HAPUS file ini setelah instalasi selesai.
 */
declare(strict_types=1);
session_start();

require dirname(__DIR__) . '/config/config.php';

$lock = STORAGE_PATH . '/installed.lock';
if (is_file($lock)) {
    http_response_code(403);
    exit('Instalasi sudah dilakukan. Hapus file install.php dari server.');
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/** Cek lingkungan server */
function cekLingkungan(): array
{
    $cek = [];
    $cek['PHP >= 8.0'] = version_compare(PHP_VERSION, '8.0.0', '>=');
    foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'curl', 'json', 'gd'] as $ext) {
        $cek['Ekstensi ' . $ext] = extension_loaded($ext);
    }
    foreach (['storage/uploads', 'storage/backup', 'storage/logs', 'storage/sessions', 'storage/cache', 'public/assets/img/logo'] as $dir) {
        $cek['Folder bisa ditulis: ' . $dir] = is_writable(ROOT_PATH . '/' . $dir);
    }
    return $cek;
}

/** Jalankan file SQL (pemisah statement: baris yang berakhiran ';') */
function jalankanSql(PDO $pdo, string $file): int
{
    $jumlah = 0;
    $buf = '';
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        $t = trim($line);
        if ($t === '' || strpos($t, '--') === 0) {
            continue;
        }
        $buf .= $line . "\n";
        if (substr($t, -1) === ';') {
            $pdo->exec($buf);
            $buf = '';
            $jumlah++;
        }
    }
    return $jumlah;
}

function passwordAcak(int $panjang = 14): string
{
    $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    $out = '';
    for ($i = 0; $i < $panjang; $i++) {
        $out .= $abjad[random_int(0, strlen($abjad) - 1)];
    }
    return $out;
}

function hashPassword(string $plain): string
{
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    return password_hash($plain, $algo);
}

/** Unduh logo (daftar URL tetap/whitelist) */
function unduhLogo(): array
{
    $logo = [
        'yayasan.png' => 'https://sitbinainsani.sch.id/wp-content/uploads/2026/07/Yayasan-100x100.png',
        'tkit.png'    => 'https://sitbinainsani.sch.id/wp-content/uploads/2026/07/TKIT.png',
        'tkit2.png'   => 'https://pmbsit.yabica.com/assets/img/2728/tkit2.png',
        'sdit.png'    => 'https://sitbinainsani.sch.id/wp-content/uploads/2026/07/SDIT-100x100.png',
        'sdit2.png'   => 'https://sitbinainsani.sch.id/wp-content/uploads/2026/07/SDIT-2-100x100.png',
        'smpit.png'   => 'https://sitbinainsani.sch.id/wp-content/uploads/2026/07/LOGO-1-100x100.png',
        'smait.png'   => 'https://sitbinainsani.sch.id/wp-content/uploads/2026/07/LOGO-2-100x100.png',
    ];
    $hasil = [];
    foreach ($logo as $nama => $url) {
        $ok = false;
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3]);
        $data = curl_exec($ch);
        $kode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($data !== false && $kode === 200 && strlen($data) < 512000) {
            $info = @getimagesizefromstring($data);
            if ($info !== false && $info[2] === IMAGETYPE_PNG) {
                $ok = file_put_contents(PUBLIC_PATH . '/assets/img/logo/' . $nama, $data) !== false;
            }
        }
        $hasil[$nama] = $ok;
    }
    return $hasil;
}

$pesan = [];
$galat = '';
$kredensial = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            throw new RuntimeException('Token CSRF tidak valid. Muat ulang halaman.');
        }
        $pakaiDummy = !empty($_POST['dummy']);
        $pakaiLogo  = !empty($_POST['logo']);

        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $ex) {
            // Di hosting, user biasanya tidak boleh membuat database; lanjutkan jika sudah dibuat di cPanel.
        }
        $pdo->exec('USE `' . str_replace('`', '', DB_NAME) . '`');

        $ada = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if ($ada) {
            throw new RuntimeException('Tabel sudah ada. Kosongkan database lebih dulu bila ingin instal ulang.');
        }

        $pesan[] = 'schema.sql: ' . jalankanSql($pdo, ROOT_PATH . '/database/schema.sql') . ' statement.';
        $pesan[] = 'seed.sql: ' . jalankanSql($pdo, ROOT_PATH . '/database/seed.sql') . ' statement.';

        // Admin dengan password acak
        $pwAdmin = passwordAcak();
        $st = $pdo->prepare("INSERT INTO users (username,nama_lengkap,role,password_hash,must_change_pw,is_aktif) VALUES ('admin','Administrator','admin',?,1,1)");
        $st->execute([hashPassword($pwAdmin)]);
        $kredensial = ['username' => 'admin', 'password' => $pwAdmin, 'dummy' => null];

        if ($pakaiDummy) {
            $pesan[] = 'dummy.sql: ' . jalankanSql($pdo, ROOT_PATH . '/database/dummy.sql') . ' statement.';
            $pwDummy = 'Sislia#2026';
            $hashDummy = hashPassword($pwDummy);
            $ins = $pdo->prepare('INSERT INTO users (username,nama_lengkap,role,unit_id,guru_id,password_hash,must_change_pw,is_aktif) VALUES (?,?,?,?,?,?,0,1)');
            $ins->execute(['ketuabidang', 'Ketua Bidang Akademik (Contoh)', 'ketua_bidang', null, null, $hashDummy]);
            $map = ['Kepala Sekolah' => ['kepala_sekolah', 'kepsek'], 'Wakil Kepala Sekolah' => ['wakil_kepala', 'wakasek']];
            $rows = $pdo->query('SELECT g.id gid, g.nama, p.unit_id, p.jabatan, u.kode FROM guru_penempatan p JOIN guru g ON g.id=p.guru_id JOIN unit_sekolah u ON u.id=p.unit_id WHERE p.tahun_ajaran_id=1 ORDER BY g.id')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                [$role, $pre] = $map[$r['jabatan']] ?? ['guru', 'guru' . $r['gid']];
                $uname = $pre . '.' . strtolower($r['kode']);
                $ins->execute([$uname, $r['nama'], $role, $r['unit_id'], $r['gid'], $hashDummy]);
            }
            $kredensial['dummy'] = $pwDummy;
            $pesan[] = 'Akun contoh dibuat (' . (count($rows) + 1) . ' akun).';
        }

        if ($pakaiLogo) {
            $hasil = unduhLogo();
            $berhasil = count(array_filter($hasil));
            $pesan[] = "Logo terunduh: $berhasil dari " . count($hasil) . '. Yang gagal bisa diunggah manual dari menu Unit Sekolah.';
        }

        file_put_contents($lock, 'Terpasang: ' . date('c'));
        $pesan[] = 'Instalasi selesai. HAPUS public/install.php sekarang.';
    } catch (Throwable $ex) {
        $galat = $ex->getMessage();
    }
}

$_SESSION['csrf'] = bin2hex(random_bytes(16));
$cek = cekLingkungan();
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Instalasi SISLIA</title>
<style>
body{margin:0;font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:1rem}
.card{max-width:640px;margin:2rem auto;background:#1e293b;border-radius:14px;padding:1.5rem}
h1{margin-top:0;letter-spacing:.15em}
li{margin:.2rem 0}.ok{color:#4ade80}.no{color:#f87171}
.alert{padding:.75rem 1rem;border-radius:8px;margin:1rem 0}
.err{background:#7f1d1d}.warn{background:#78350f}.info{background:#14532d}
code{background:#0f172a;padding:.1rem .4rem;border-radius:4px;font-size:1.05em}
button{background:#4f46e5;color:#fff;border:0;border-radius:8px;padding:.7rem 1.2rem;font-size:1rem;cursor:pointer}
label{display:block;margin:.5rem 0}
</style>
</head>
<body>
<div class="card">
<h1>SISLIA</h1>
<p>Instalasi database dan akun awal.</p>

<?php if ($galat): ?><div class="alert err"><?= e($galat) ?></div><?php endif; ?>

<?php if ($kredensial): ?>
    <div class="alert warn">
        <strong>Simpan sekarang, password ini hanya tampil sekali.</strong><br>
        Admin: <code><?= e($kredensial['username']) ?></code> / <code><?= e($kredensial['password']) ?></code><br>
        Wajib mengganti password saat login pertama.
        <?php if ($kredensial['dummy']): ?><br>Akun contoh (semua peran) memakai password <code><?= e($kredensial['dummy']) ?></code>.<?php endif; ?>
    </div>
    <ul><?php foreach ($pesan as $p): ?><li><?= e($p) ?></li><?php endforeach; ?></ul>
    <p><a style="color:#a5b4fc" href="<?= e(BASE_URL) ?>/">Buka aplikasi</a></p>
<?php else: ?>
    <h3>Cek lingkungan</h3>
    <ul>
    <?php foreach ($cek as $nama => $ok): ?>
        <li class="<?= $ok ? 'ok' : 'no' ?>"><?= $ok ? '✔' : '✘' ?> <?= e($nama) ?></li>
    <?php endforeach; ?>
    </ul>
    <p>Database: <code><?= e(DB_NAME) ?></code> @ <code><?= e(DB_HOST) ?></code>. Ubah di <code>config/config.php</code> bila perlu.</p>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <label><input type="checkbox" name="logo" value="1" checked> Unduh logo unit dari server sekolah</label>
        <label><input type="checkbox" name="dummy" value="1" checked> Sertakan data contoh (guru, kelas, mapel, akun uji). Matikan di produksi.</label>
        <button type="submit">Pasang SISLIA</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
