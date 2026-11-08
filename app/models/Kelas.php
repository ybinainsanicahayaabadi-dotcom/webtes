<?php
/**
 * Model kelas (tabel `kelas`). Kelas terikat tahun ajaran, bukan semester.
 */
final class Kelas
{
    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM kelas WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    public static function create(array $d): int
    {
        return Database::insert(
            'INSERT INTO kelas (unit_id, tahun_ajaran_id, tingkat, nama, wali_guru_id, jumlah_siswa) VALUES (?,?,?,?,?,?)',
            [(int) $d['unit_id'], (int) $d['tahun_ajaran_id'], $d['tingkat'], $d['nama'], $d['wali_guru_id'], $d['jumlah_siswa']]
        );
    }

    public static function update(int $id, array $d): void
    {
        Database::exec(
            'UPDATE kelas SET unit_id = ?, tahun_ajaran_id = ?, tingkat = ?, nama = ?, wali_guru_id = ?, jumlah_siswa = ? WHERE id = ?',
            [(int) $d['unit_id'], (int) $d['tahun_ajaran_id'], $d['tingkat'], $d['nama'], $d['wali_guru_id'], $d['jumlah_siswa'], $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM kelas WHERE id = ?', [$id]);
    }

    public static function dependencies(int $id): array
    {
        $cek = [
            'Penugasan mengajar' => 'SELECT COUNT(*) FROM penugasan_guru WHERE kelas_id = ?',
            'Penilaian'          => 'SELECT COUNT(*) FROM penilaian WHERE kelas_id = ?',
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

    /** Guru yang ditempatkan di unit pada tahun ajaran tertentu (untuk pilihan wali kelas) */
    public static function guruTersedia(int $unitId, int $tahunId): array
    {
        return Database::all(
            "SELECT g.id, g.nama, p.jabatan
             FROM guru_penempatan p JOIN guru g ON g.id = p.guru_id
             WHERE p.unit_id = ? AND p.tahun_ajaran_id = ? AND p.status = 'aktif'
               AND g.status = 'aktif' AND g.deleted_at IS NULL
             ORDER BY g.nama",
            [$unitId, $tahunId]
        );
    }

    /** Salin kelas dari satu tahun ajaran ke tahun lain. @return array{0:int,1:int} [disalin, dilewati] */
    public static function salin(int $dariTahun, int $keTahun, ?int $unitId): array
    {
        return Transaction::run(static function () use ($dariTahun, $keTahun, $unitId) {
            $sql = 'SELECT unit_id, tingkat, nama FROM kelas WHERE tahun_ajaran_id = ? AND deleted_at IS NULL';
            $args = [$dariTahun];
            if ($unitId) {
                $sql .= ' AND unit_id = ?';
                $args[] = $unitId;
            }
            $disalin = 0;
            $dilewati = 0;
            foreach (Database::all($sql, $args) as $k) {
                $ada = (int) Database::value(
                    'SELECT COUNT(*) FROM kelas WHERE unit_id = ? AND tahun_ajaran_id = ? AND nama = ? AND deleted_at IS NULL',
                    [$k['unit_id'], $keTahun, $k['nama']]
                );
                if ($ada > 0) {
                    $dilewati++;
                    continue;
                }
                Database::exec(
                    'INSERT INTO kelas (unit_id, tahun_ajaran_id, tingkat, nama) VALUES (?,?,?,?)',
                    [$k['unit_id'], $keTahun, $k['tingkat'], $k['nama']]
                );
                $disalin++;
            }
            return [$disalin, $dilewati];
        });
    }
}
