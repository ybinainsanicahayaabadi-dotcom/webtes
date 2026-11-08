<?php
/**
 * Model data diri guru/tenaga kependidikan (tabel `guru`). Unit ditentukan oleh tabel guru_penempatan.
 */
final class Guru
{
    public static function dirFoto(): string
    {
        return STORAGE_PATH . '/uploads/guru';
    }

    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM guru WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    public static function opsi(): array
    {
        return Database::all("SELECT id, nama, niy FROM guru WHERE deleted_at IS NULL AND status = 'aktif' ORDER BY nama");
    }

    private static function nilai(array $d): array
    {
        return [
            $d['niy'], $d['nuptk'], $d['nama'], $d['jenis_kelamin'], $d['tempat_lahir'], $d['tanggal_lahir'],
            $d['pendidikan_terakhir'], $d['tmt'], $d['email'], $d['telepon'], $d['alamat'], $d['foto'], $d['status'],
        ];
    }

    public static function create(array $d): int
    {
        return Database::insert(
            'INSERT INTO guru (niy, nuptk, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, pendidikan_terakhir, tmt,
                email, telepon, alamat, foto, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            self::nilai($d)
        );
    }

    public static function update(int $id, array $d): void
    {
        Database::exec(
            'UPDATE guru SET niy = ?, nuptk = ?, nama = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?,
                pendidikan_terakhir = ?, tmt = ?, email = ?, telepon = ?, alamat = ?, foto = ?, status = ? WHERE id = ?',
            array_merge(self::nilai($d), [$id])
        );
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM guru WHERE id = ?', [$id]);
    }

    public static function dependencies(int $id): array
    {
        $cek = [
            'Penempatan'          => 'SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ?',
            'Akun pengguna'       => 'SELECT COUNT(*) FROM users WHERE guru_id = ? AND deleted_at IS NULL',
            'Wali kelas'          => 'SELECT COUNT(*) FROM kelas WHERE wali_guru_id = ? AND deleted_at IS NULL',
            'Penugasan mengajar'  => 'SELECT COUNT(*) FROM penugasan_guru WHERE guru_id = ?',
            'Jadwal supervisi'    => 'SELECT COUNT(*) FROM jadwal_supervisi WHERE guru_id = ?',
            'Penilaian'           => 'SELECT COUNT(*) FROM penilaian WHERE guru_id = ?',
        ];
        $hasil = [];
        foreach ($cek as $label => $sql) {
            $n = (int) Database::value($sql, [$id]);
            if ($n > 0) {
                $hasil[$label] = $n;
            }
        }
        return $hasil;
    }
}
