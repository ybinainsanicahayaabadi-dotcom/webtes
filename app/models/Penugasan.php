<?php
/**
 * Model penugasan mengajar (tabel `penugasan_guru`): guru - kelas - mapel semester.
 */
final class Penugasan
{
    public static function find(int $id): ?array
    {
        return Database::row(
            'SELECT pg.*, k.unit_id, ms.semester_id
             FROM penugasan_guru pg
             JOIN kelas k ON k.id = pg.kelas_id
             JOIN mapel_semester ms ON ms.id = pg.mapel_semester_id
             WHERE pg.id = ?',
            [$id]
        );
    }

    public static function create(int $guruId, int $kelasId, int $msId): int
    {
        return Database::insert('INSERT INTO penugasan_guru (guru_id, kelas_id, mapel_semester_id) VALUES (?,?,?)', [$guruId, $kelasId, $msId]);
    }

    public static function update(int $id, int $guruId, int $kelasId, int $msId): void
    {
        Database::exec('UPDATE penugasan_guru SET guru_id = ?, kelas_id = ?, mapel_semester_id = ? WHERE id = ?', [$guruId, $kelasId, $msId, $id]);
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM penugasan_guru WHERE id = ?', [$id]);
    }

    /** Pilihan guru, kelas, dan mapel untuk kombinasi semester + unit */
    public static function opsi(int $semesterId, int $unitId): array
    {
        $ta = (int) Database::value('SELECT tahun_ajaran_id FROM semester WHERE id = ?', [$semesterId]);
        if ($ta === 0 || $unitId === 0) {
            return ['guru' => [], 'kelas' => [], 'mapel' => []];
        }
        return [
            'guru'  => Kelas::guruTersedia($unitId, $ta),
            'kelas' => Database::all(
                'SELECT id, nama, tingkat FROM kelas WHERE unit_id = ? AND tahun_ajaran_id = ? AND deleted_at IS NULL
                 ORDER BY CAST(tingkat AS UNSIGNED), tingkat, nama',
                [$unitId, $ta]
            ),
            'mapel' => Database::all(
                'SELECT ms.id, m.nama, ms.tingkat, ms.jam_per_minggu
                 FROM mapel_semester ms JOIN mapel m ON m.id = ms.mapel_id
                 WHERE ms.semester_id = ? AND m.unit_id = ? AND m.deleted_at IS NULL ORDER BY m.nama, ms.tingkat',
                [$semesterId, $unitId]
            ),
        ];
    }

    /**
     * Periksa satu kombinasi penugasan. @return string|null pesan galat, null bila valid
     */
    public static function cek(int $semesterId, int $unitId, int $guruId, int $msId, int $kelasId, int $kecualiId): ?string
    {
        $sem = Semester::find($semesterId);
        if (!$sem) {
            return 'Semester tidak ditemukan.';
        }
        $kelas = Kelas::find($kelasId);
        if (!$kelas || (int) $kelas['unit_id'] !== $unitId || (int) $kelas['tahun_ajaran_id'] !== (int) $sem['tahun_ajaran_id']) {
            return 'Kelas tidak sesuai dengan unit dan tahun ajaran semester ini.';
        }
        $ms = MapelSemester::find($msId);
        if (!$ms || (int) $ms['semester_id'] !== $semesterId || (int) $ms['unit_id'] !== $unitId) {
            return 'Mapel tidak dibuka pada semester dan unit ini.';
        }
        if ($ms['tingkat'] !== '' && (string) $ms['tingkat'] !== (string) $kelas['tingkat']) {
            return 'Mapel ini khusus tingkat ' . $ms['tingkat'] . ', tidak cocok untuk ' . $kelas['nama'] . '.';
        }
        $placed = (int) Database::value(
            "SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ? AND unit_id = ? AND tahun_ajaran_id = ? AND status = 'aktif'",
            [$guruId, $unitId, (int) $sem['tahun_ajaran_id']]
        );
        if ($placed === 0) {
            return 'Guru belum ditempatkan di unit ini pada tahun ajaran semester tersebut.';
        }
        $lain = Database::row(
            'SELECT g.nama FROM penugasan_guru pg JOIN guru g ON g.id = pg.guru_id
             WHERE pg.kelas_id = ? AND pg.mapel_semester_id = ? AND pg.id <> ? LIMIT 1',
            [$kelasId, $msId, $kecualiId]
        );
        if ($lain) {
            return $kelas['nama'] . ' sudah memiliki pengajar untuk mapel ini (' . $lain['nama'] . ').';
        }
        return null;
    }
}
