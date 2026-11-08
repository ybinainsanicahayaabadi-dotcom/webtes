<?php
/**
 * SISLIA - app/bootstrap.php
 * Dimuat oleh public/index.php. Mengatur config, autoload, header keamanan, dan sesi.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/helpers/functions.php';

// Autoload sederhana: nama kelas = nama file
spl_autoload_register(static function (string $class): void {
    foreach (['core', 'controllers', 'models', 'middleware'] as $dir) {
        $file = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

Security::sendHeaders();
Security::startSession();
