<?php
/**
 * Pengaturan aplikasi dari tabel `pengaturan` (di-cache per request).
 */
final class Setting
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (Database::all('SELECT kunci, nilai FROM pengaturan') as $r) {
                    self::$cache[$r['kunci']] = $r['nilai'];
                }
            } catch (Throwable $e) {
                // database belum siap: pakai nilai bawaan
            }
        }
        return self::$cache;
    }

    public static function get(string $key, $default = '')
    {
        $all = self::load();
        return (isset($all[$key]) && $all[$key] !== '') ? $all[$key] : $default;
    }

    public static function set(string $key, string $value, string $grup = 'umum'): void
    {
        Database::exec(
            'INSERT INTO pengaturan (kunci, nilai, grup) VALUES (?,?,?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)',
            [$key, $value, $grup]
        );
        self::$cache = null;
    }
}
