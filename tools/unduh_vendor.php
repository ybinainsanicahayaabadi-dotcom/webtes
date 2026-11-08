<?php
/**
 * SISLIA - tools/unduh_vendor.php
 * Mengunduh library front-end (Bootstrap, jQuery, DataTables, Select2, SweetAlert2, Font Awesome)
 * ke public/assets/vendor agar aplikasi tidak bergantung pada CDN.
 *
 * Cara pakai (di komputer lokal, XAMPP):  php tools/unduh_vendor.php
 * Setelah selesai, unggah folder public/assets/vendor ke hosting.
 * Daftar dan versi file ada di config/vendor.php.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Jalankan lewat command line: php tools/unduh_vendor.php');
}

$root = dirname(__DIR__);
$manifest = require $root . '/config/vendor.php';
$tujuan = $root . '/public/assets/vendor/';

$daftar = [];
foreach ($manifest as $nama => $lib) {
    if ($nama === '_extra') {
        foreach ($lib as $f) {
            $daftar[] = $f;
        }
        continue;
    }
    foreach (['css', 'js'] as $tipe) {
        foreach ($lib[$tipe] as $f) {
            $daftar[] = $f;
        }
    }
}

$gagal = 0;
foreach ($daftar as [$lokal, $url]) {
    $path = $tujuan . $lokal;
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    $ctx = stream_context_create(['http' => ['timeout' => 30, 'user_agent' => 'SISLIA-vendor/1.0']]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false || strlen($data) < 200) {
        echo "GAGAL  $lokal  <- $url\n";
        $gagal++;
        continue;
    }
    file_put_contents($path, $data);
    echo "OK     $lokal (" . round(strlen($data) / 1024) . " KB)\n";
}

echo "\nSelesai. " . (count($daftar) - $gagal) . " berhasil, $gagal gagal.\n";
if ($gagal > 0) {
    echo "Periksa nama/versi paket di config/vendor.php lalu jalankan ulang.\n";
}
exit($gagal > 0 ? 1 : 0);
