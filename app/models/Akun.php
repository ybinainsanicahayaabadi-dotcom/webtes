<?php
/**
 * Model akun pengguna untuk modul Admin (tabel `users`). password_hash tidak pernah dikembalikan ke tampilan.
 */
final class Akun
{
    public const ROLE = ['admin', 'ketua_bidang', 'kepala_sekolah', 'wakil_kepala', 'guru'];
    /** Peran yang wajib terhubung ke data guru (unit diambil dari penempatan) */
    public const ROLE_GURU = ['kepala_sekolah', 'wakil_kepala', 'guru'];

    public static function find(int $id): ?array
    {
        return Database::row(
            'SELECT id, username, email, nama_lengkap, role, unit_id, guru_id, tanda_tangan, must_change_pw, is_aktif, last_login_at
             FROM users WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    public static function create(array $d, string $hash): int
    {
        return Database::insert(
            'INSERT INTO users (username, email, password_hash, nama_lengkap, role, unit_id, guru_id, must_change_pw, is_aktif)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [$d['username'], $d['email'], $hash, $d['nama_lengkap'], $d['role'], $d['unit_id'], $d['guru_id'],
                (int) $d['must_change_pw'], (int) $d['is_aktif']]
        );
    }

    public static function update(int $id, array $d, ?string $hash): void
    {
        Database::exec(
            'UPDATE users SET username = ?, email = ?, nama_lengkap = ?, role = ?, unit_id = ?, guru_id = ?, must_change_pw = ?, is_aktif = ? WHERE id = ?',
            [$d['username'], $d['email'], $d['nama_lengkap'], $d['role'], $d['unit_id'], $d['guru_id'],
                (int) $d['must_change_pw'], (int) $d['is_aktif'], $id]
        );
        if ($hash !== null) {
            Database::exec('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $id]);
        }
    }

    public static function setTtd(int $id, ?string $rel): void
    {
        Database::exec('UPDATE users SET tanda_tangan = ? WHERE id = ?', [$rel, $id]);
    }

    public static function setPassword(int $id, string $hash, bool $wajibGanti): void
    {
        Database::exec('UPDATE users SET password_hash = ?, must_change_pw = ? WHERE id = ?', [$hash, $wajibGanti ? 1 : 0, $id]);
    }

    public static function cabutSesi(int $id): void
    {
        Database::exec('DELETE FROM user_token WHERE user_id = ?', [$id]);
    }

    /** Hapus lunak: akun disembunyikan, riwayat penilaian tetap utuh */
    public static function hapusLunak(int $id): void
    {
        Database::exec(
            "UPDATE users SET deleted_at = NOW(), is_aktif = 0, username = CONCAT(LEFT(username, 40), '~del', id) WHERE id = ?",
            [$id]
        );
        self::cabutSesi($id);
    }

    public static function jumlahAdminAktif(int $kecualiId = 0): int
    {
        return (int) Database::value(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_aktif = 1 AND deleted_at IS NULL AND id <> ?",
            [$kecualiId]
        );
    }

    public static function passwordAcak(int $panjang = 12): string
    {
        $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        do {
            $out = '';
            for ($i = 0; $i < $panjang; $i++) {
                $out .= $abjad[random_int(0, strlen($abjad) - 1)];
            }
        } while (!preg_match('/[A-Za-z]/', $out) || !preg_match('/\d/', $out));
        return $out;
    }
}
