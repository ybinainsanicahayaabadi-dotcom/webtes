<?php
/**
 * Model mapel yang dibuka per semester (tabel `mapel_semester`), termasuk penyalinan antar semester.
 * tingkat '' = berlaku untuk semua tingkat pada unit tersebut.
 */
final class MapelSemester
{
    public static function find(int $id): ?array
    {
        return Database::row(
            'SELECT ms.*, m.unit_id FROM mapel_semester ms JOIN mapel m ON m.id = ms.mapel_id
             WHERE ms.id = ? AND m.deleted_at IS NULL',
            [$id]
        );
    }

    public static function semesterTerkunci(int $semesterId): bool
    {
        return (int) Database::value('SELECT is_terkunci FROM semester WHERE id = ?', [$semesterId]) === 1;
    }

    public static function create(array $d): int
    {
        return Database::insert(
            'INSERT INTO mapel_semester (mapel_id, semester_id, tingkat, jam_per_minggu) VALUES (?,?,?,?)',
            [(int) $d['mapel_id'], (int) $d['semester_id'], (string) $d['tingkat'], $d['jam_per_minggu']]
        );
    }

    public static function update(int $id, array $d): void
    {
        Database::exec(
            'UPDATE mapel_semester SET mapel_id = ?, semester_id = ?, tingkat = ?, jam_per_minggu = ? WHERE id = ?',
            [(int) $d['mapel_id'], (int) $d['semester_id'], (string) $d['tingkat'], $d['jam_per_minggu'], $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM mapel_semester WHERE id = ?', [$id]);
    }

    public static function ada(int $mapelId, int $semesterId, string $tingkat, int $kecualiId): bool
    {
        return (int) Database::value(
            'SELECT COUNT(*) FROM mapel_semester WHERE mapel_id = ? AND semester_id = ? AND tingkat = ? AND id <> ?',
            [$mapelId, $semesterId, $tingkat, $kecualiId]
        ) > 0;
    }

    public static function dependencies(int $id): array
    {
        $n = (int) Database::value('SELECT COUNT(*) FROM penugasan_guru WHERE mapel_semester_id = ?', [$id]);
        return $n > 0 ? ['Penugasan mengajar' => $n] : [];
    }

    /**
     * Salin mapel semester (dan opsional penugasan guru) dari satu semester ke semester lain.
     * Penugasan hanya disalin bila kelas bernama sama ada di tahun ajaran tujuan dan gurunya
     * masih ditempatkan di unit yang sama pada tahun ajaran tujuan.
     * @return array{mapel:int, mapel_lewat:int, tugas:int, tugas_lewat:int}
     */
    public static function salin(int $dariSem, int $keSem, ?int $unitId, bool $denganTugas): array
    {
        return Transaction::run(static function () use ($dariSem, $keSem, $unitId, $denganTugas) {
            $hasil = ['mapel' => 0, 'mapel_lewat' => 0, 'tugas' => 0, 'tugas_lewat' => 0];
            $taDari = (int) Database::value('SELECT tahun_ajaran_id FROM semester WHERE id = ?', [$dariSem]);
            $taKe = (int) Database::value('SELECT tahun_ajaran_id FROM semester WHERE id = ?', [$keSem]);

            $sql = 'SELECT ms.id, ms.mapel_id, ms.tingkat, ms.jam_per_minggu, m.unit_id
                    FROM mapel_semester ms JOIN mapel m ON m.id = ms.mapel_id
                    WHERE ms.semester_id = ? AND m.deleted_at IS NULL';
            $args = [$dariSem];
            if ($unitId) {
                $sql .= ' AND m.unit_id = ?';
                $args[] = $unitId;
            }

            $peta = [];   // id mapel_semester lama => id baru
            foreach (Database::all($sql, $args) as $r) {
                $ada = Database::value(
                    'SELECT id FROM mapel_semester WHERE mapel_id = ? AND semester_id = ? AND tingkat = ?',
                    [$r['mapel_id'], $keSem, $r['tingkat']]
                );
                if ($ada) {
                    $peta[(int) $r['id']] = (int) $ada;
                    $hasil['mapel_lewat']++;
                    continue;
                }
                $peta[(int) $r['id']] = Database::insert(
                    'INSERT INTO mapel_semester (mapel_id, semester_id, tingkat, jam_per_minggu) VALUES (?,?,?,?)',
                    [$r['mapel_id'], $keSem, $r['tingkat'], $r['jam_per_minggu']]
                );
                $hasil['mapel']++;
            }

            if ($denganTugas && $peta) {
                $tugas = Database::all(
                    'SELECT pg.guru_id, pg.kelas_id, pg.mapel_semester_id, k.unit_id, k.nama AS kelas_nama
                     FROM penugasan_guru pg JOIN kelas k ON k.id = pg.kelas_id
                     WHERE pg.mapel_semester_id IN (' . implode(',', array_map('intval', array_keys($peta))) . ')'
                );
                foreach ($tugas as $t) {
                    $msBaru = $peta[(int) $t['mapel_semester_id']] ?? 0;
                    // kelas tujuan: sama bila tahun ajaran sama, jika tidak cari berdasarkan nama
                    $kelasBaru = $taDari === $taKe ? (int) $t['kelas_id'] : (int) Database::value(
                        'SELECT id FROM kelas WHERE unit_id = ? AND tahun_ajaran_id = ? AND nama = ? AND deleted_at IS NULL',
                        [$t['unit_id'], $taKe, $t['kelas_nama']]
                    );
                    $guruOk = $kelasBaru > 0 && (int) Database::value(
                        "SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ? AND unit_id = ? AND tahun_ajaran_id = ? AND status = 'aktif'",
                        [$t['guru_id'], $t['unit_id'], $taKe]
                    ) > 0;
                    $bentrok = $msBaru > 0 && $kelasBaru > 0 && (int) Database::value(
                        'SELECT COUNT(*) FROM penugasan_guru WHERE kelas_id = ? AND mapel_semester_id = ?', [$kelasBaru, $msBaru]
                    ) > 0;
                    if (!$msBaru || !$kelasBaru || !$guruOk || $bentrok) {
                        $hasil['tugas_lewat']++;
                        continue;
                    }
                    Database::exec(
                        'INSERT INTO penugasan_guru (guru_id, kelas_id, mapel_semester_id) VALUES (?,?,?)',
                        [$t['guru_id'], $kelasBaru, $msBaru]
                    );
                    $hasil['tugas']++;
                }
            }
            return $hasil;
        });
    }
}
