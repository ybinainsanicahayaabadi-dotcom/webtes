<?php
/**
 * SISLIA - config/config.php (CONTOH)
 * Sesuaikan nilai di bawah ini. Jangan unggah file ini ke repositori publik.
 */

// ---- Lingkungan ----
define('APP_ENV',   'development');   // 'development' (XAMPP) | 'production' (hosting)
define('APP_DEBUG', true);            // ubah ke false di hosting
define('APP_TZ',    'Asia/Jakarta');

// ---- Database ----
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'sislia');
define('DB_USER',    'root');         // XAMPP default; di hosting pakai user khusus
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ---- URL & Path ----
// XAMPP contoh: 'http://localhost/sislia/public'   | Hosting contoh: 'https://sislia.domainanda.sch.id'
define('BASE_URL',  'http://localhost/sislia/public');
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('VIEW_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');   // di luar akses web (dilindungi .htaccess)

// ---- Sesi & keamanan ----
define('SESSION_NAME',     'SISLIA_SESS');
define('SESSION_LIFETIME', 60 * 60 * 24 * 30);     // 30 hari
define('LOGIN_MAX_GAGAL',  5);
define('LOGIN_KUNCI_MENIT', 15);
define('PASSWORD_MIN',     8);

// ---- Upload ----
define('UPLOAD_MAX_MB', 5);

date_default_timezone_set(APP_TZ);
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');
}
