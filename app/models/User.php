<?php
/**
 * Model akun pengguna (tabel `users`).
 */
final class User
{
    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    public static function updateProfil(int $id, string $nama, ?string $email): void
    {
        Database::exec('UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?', [$nama, $email, $id]);
    }

    public static function updatePassword(int $id, string $hash): void
    {
        Database::exec('UPDATE users SET password_hash = ?, must_change_pw = 0 WHERE id = ?', [$hash, $id]);
    }

    public static function setTandaTangan(int $id, ?string $path): void
    {
        Database::exec('UPDATE users SET tanda_tangan = ? WHERE id = ?', [$path, $id]);
    }
}
