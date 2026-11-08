<?php
/**
 * SISLIA - fungsi bantu global.
 */

/** Escape output HTML */
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL absolut berbasis BASE_URL */
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/** URL aset dengan versi (cache busting) */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = PUBLIC_PATH . '/assets/' . $path;
    $v = is_file($file) ? filemtime($file) : 1;
    return url('assets/' . $path) . '?v=' . $v;
}

/** Redirect lalu hentikan eksekusi */
function redirect(string $path, int $code = 302): void
{
    $target = preg_match('#^https?://#i', $path) ? $path : url($path);
    header('Location: ' . $target, true, $code);
    exit;
}

/** Pesan flash: flash('success', 'Tersimpan') */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_pull(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return Csrf::field();
}

function setting(string $key, $default = '')
{
    return Setting::get($key, $default);
}

/** Ambil input POST sebagai string ter-trim */
function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (defined('TRUST_PROXY') && TRUST_PROXY && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $first = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            $ip = $first;
        }
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function user_agent(): string
{
    return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

/** Catat ke audit_log (gagal catat tidak boleh mengganggu aplikasi) */
function audit(string $aksi, ?string $tabel = null, ?int $recordId = null, $lama = null, $baru = null): void
{
    try {
        Database::exec(
            'INSERT INTO audit_log (user_id, aksi, tabel, record_id, data_lama, data_baru, ip_address, user_agent) VALUES (?,?,?,?,?,?,?,?)',
            [
                $_SESSION['uid'] ?? null, $aksi, $tabel, $recordId,
                $lama === null ? null : json_encode($lama, JSON_UNESCAPED_UNICODE),
                $baru === null ? null : json_encode($baru, JSON_UNESCAPED_UNICODE),
                client_ip(), user_agent(),
            ]
        );
    } catch (Throwable $e) {
        // diabaikan
    }
}

/** Tulis log aplikasi ke storage/logs/app.log */
function app_log(string $message): void
{
    $dir = STORAGE_PATH . '/logs';
    if (is_dir($dir) && is_writable($dir)) {
        @file_put_contents($dir . '/app.log', '[' . date('c') . '] ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function role_label(string $role): string
{
    return [
        'admin'          => 'Admin',
        'ketua_bidang'   => 'Ketua Bidang Akademik',
        'kepala_sekolah' => 'Kepala Sekolah',
        'wakil_kepala'   => 'Wakil Kepala Sekolah',
        'guru'           => 'Guru',
    ][$role] ?? $role;
}

/** 5 Nov 2026 atau 5 Nov 2026 14:30 */
function tanggal_id(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }
    $t = strtotime($date);
    if ($t === false) {
        return '-';
    }
    $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $out = date('j', $t) . ' ' . $bln[(int) date('n', $t)] . ' ' . date('Y', $t);
    return $withTime ? $out . ' ' . date('H:i', $t) : $out;
}

/** "Chrome di Windows" dari user-agent */
function ua_label(?string $ua): string
{
    $ua = (string) $ua;
    if ($ua === '') {
        return 'Perangkat tidak dikenal';
    }
    $browser = 'Browser';
    foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'] as $k => $v) {
        if (stripos($ua, $k) !== false) {
            $browser = $v;
            break;
        }
    }
    $os = 'OS lain';
    foreach (['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iPhone', 'iPad' => 'iPad', 'Mac OS' => 'macOS', 'Linux' => 'Linux'] as $k => $v) {
        if (stripos($ua, $k) !== false) {
            $os = $v;
            break;
        }
    }
    return $browser . ' di ' . $os;
}

/**
 * Cetak tag <link>/<script> library vendor.
 * Memakai file lokal di public/assets/vendor bila ada, jika tidak memakai CDN (cadangan).
 * Daftar library ada di config/vendor.php.
 */
function vendor_tags(string $type, array $libs): string
{
    static $manifest = null;
    if ($manifest === null) {
        $manifest = require ROOT_PATH . '/config/vendor.php';
    }
    $out = '';
    foreach ($libs as $lib) {
        foreach ($manifest[$lib][$type] ?? [] as [$local, $cdn]) {
            $src = is_file(PUBLIC_PATH . '/assets/vendor/' . $local) ? asset('vendor/' . $local) : $cdn;
            $out .= $type === 'css'
                ? '<link rel="stylesheet" href="' . e($src) . '">' . "\n"
                : '<script src="' . e($src) . '"></script>' . "\n";
        }
    }
    return $out;
}

/** URL logo bila filenya ada di public/, jika tidak null (tampilkan ikon pengganti) */
function logo_url(?string $path): ?string
{
    if (!$path) {
        return null;
    }
    return is_file(PUBLIC_PATH . '/' . ltrim($path, '/')) ? url($path) : null;
}
