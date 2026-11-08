<?php
/**
 * Header keamanan dan pengaturan sesi.
 */
final class Security
{
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        return defined('TRUST_PROXY') && TRUST_PROXY
            && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        $cdn = 'https://cdn.jsdelivr.net'; // hanya dipakai bila library vendor belum diunduh lokal
        header("Content-Security-Policy: default-src 'self'; "
            . "script-src 'self' $cdn; "
            . "style-src 'self' 'unsafe-inline' $cdn; "
            . "img-src 'self' data: blob:; "
            . "font-src 'self' data: $cdn; "
            . "connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header_remove('X-Powered-By');
    }

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $path = STORAGE_PATH . '/sessions';
        if (!is_dir($path)) {
            @mkdir($path, 0750, true);
        }
        if (is_dir($path) && is_writable($path)) {
            session_save_path($path);
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');

        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}
