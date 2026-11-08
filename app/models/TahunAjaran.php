<?php
/**
 * Model tahun ajaran (tabel `tahun_ajaran`).
 */
final class TahunAjaran
{
    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM tahun_ajaran WHERE id = ?', [$id]);
    }

    public static function create(array $d): int
    {
        return Transaction::run(static function () use ($d) {
            if (!empty($d['is_aktif'])) {
                Database::exec('UPDATE tahun_ajaran SET is_aktif = 0');
            }
            $id = Database::insert(
                'INSERT INTO tahun_ajaran (nama, tgl_mulai, tgl_selesai, is_aktif) VALUES (?,?,?,?)',
                [$d['nama'], $d['tgl_mulai'], $d['tgl_selesai'], (int) $d['is_aktif']]
            );
            if (!empty($d['is_aktif'])) {
                Penempatan::syncSemua();   // unit akun mengikuti penempatan tahun ajaran aktif
            }
            return $id;
        });
    }

    public static function update(int $id, array $d): void
    {
        Transaction::run(static function () use ($id, $d) {
            if (!empty($d['is_aktif'])) {
                Database::exec('UPDATE tahun_ajaran SET is_aktif = 0 WHERE id <> ?', [$id]);
            }
            Database::exec(
                'UPDATE tahun_ajaran SET nama = ?, tgl_mulai = ?, tgl_selesai = ?, is_aktif = ? WHERE id = ?',
                [$d['nama'], $d['tgl_mulai'], $d['tgl_selesai'], (int) $d['is_aktif'], $id]
            );
            Penempatan::syncSemua();
        });
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM tahun_ajaran WHERE id = ?', [$id]);
    }

    /** Data yang masih memakai tahun ajaran ini: ['Semester' => 2, ...] (kosong = aman dihapus) */
    public static function dependencies(int $id): array
    {
        $cek = [
            'Semester'           => 'SELECT COUNT(*) FROM semester WHERE tahun_ajaran_id = ?',
            'Kelas'              => 'SELECT COUNT(*) FROM kelas WHERE tahun_ajaran_id = ?',
            'Penempatan guru'    => 'SELECT COUNT(*) FROM guru_penempatan WHERE tahun_ajaran_id = ?',
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
