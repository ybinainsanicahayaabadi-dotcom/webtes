<?php
/**
 * Model penempatan guru per tahun ajaran (tabel `guru_penempatan`): satu guru = satu unit per tahun.
 * Juga menyinkronkan users.unit_id dengan penempatan pada tahun ajaran aktif.
 */
final class Penempatan
{
    public const JABATAN = ['Kepala Sekolah', 'Wakil Kepala Sekolah', 'Guru', 'Tenaga Kependidikan'];
    private const ROLE_UNIT = "('kepala_sekolah','wakil_kepala','guru')";

    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM guru_penempatan WHERE id = ?', [$id]);
    }

    public static function create(array $d): int
    {
        return Transaction::run(static function () use ($d) {
            $id = Database::insert(
                'INSERT INTO guru_penempatan (guru_id, tahun_ajaran_id, unit_id, jabatan, pangkat_golongan, status) VALUES (?,?,?,?,?,?)',
                [(int) $d['guru_id'], (int) $d['tahun_ajaran_id'], (int) $d['unit_id'], $d['jabatan'], $d['pangkat_golongan'], $d['status']]
            );
            self::syncUser((int) $d['guru_id']);
            return $id;
        });
    }

    public static function update(int $id, array $d): void
    {
        Transaction::run(static function () use ($id, $d) {
            $lama = self::find($id);
            Database::exec(
                'UPDATE guru_penempatan SET guru_id = ?, tahun_ajaran_id = ?, unit_id = ?, jabatan = ?, pangkat_golongan = ?, status = ? WHERE id = ?',
                [(int) $d['guru_id'], (int) $d['tahun_ajaran_id'], (int) $d['unit_id'], $d['jabatan'], $d['pangkat_golongan'], $d['status'], $id]
            );
            self::syncUser((int) $d['guru_id']);
            if ($lama && (int) $lama['guru_id'] !== (int) $d['guru_id']) {
                self::syncUser((int) $lama['guru_id']);
            }
        });
    }

    public static function delete(int $id): void
    {
        Transaction::run(static function () use ($id) {
            $lama = self::find($id);
            Database::exec('DELETE FROM guru_penempatan WHERE id = ?', [$id]);
            if ($lama) {
                self::syncUser((int) $lama['guru_id']);
            }
        });
    }

    /** Hal yang terikat pada penempatan (wali kelas & penugasan mengajar pada tahun ajaran tsb) */
    public static function dependencies(int $guruId, int $tahunId): array
    {
        $hasil = [];
        $wali = (int) Database::value(
            'SELECT COUNT(*) FROM kelas WHERE wali_guru_id = ? AND tahun_ajaran_id = ? AND deleted_at IS NULL', [$guruId, $tahunId]
        );
        if ($wali > 0) {
            $hasil['Wali kelas'] = $wali;
        }
        $tugas = (int) Database::value(
            'SELECT COUNT(*) FROM penugasan_guru pg JOIN kelas k ON k.id = pg.kelas_id WHERE pg.guru_id = ? AND k.tahun_ajaran_id = ?',
            [$guruId, $tahunId]
        );
        if ($tugas > 0) {
            $hasil['Penugasan mengajar'] = $tugas;
        }
        return $hasil;
    }

    /** Unit guru pada tahun ajaran aktif (null bila belum ditempatkan) */
    public static function unitAktif(int $guruId): ?int
    {
        $u = Database::value(
            "SELECT p.unit_id FROM guru_penempatan p JOIN tahun_ajaran t ON t.id = p.tahun_ajaran_id
             WHERE p.guru_id = ? AND t.is_aktif = 1 AND p.status = 'aktif' LIMIT 1",
            [$guruId]
        );
        return $u ? (int) $u : null;
    }

    /** Samakan users.unit_id seorang guru dengan penempatan tahun ajaran aktif */
    public static function syncUser(int $guruId): void
    {
        Database::exec(
            'UPDATE users SET unit_id = ? WHERE guru_id = ? AND role IN ' . self::ROLE_UNIT,
            [self::unitAktif($guruId), $guruId]
        );
    }

    /** Samakan semua akun (dipanggil saat tahun ajaran aktif berganti) */
    public static function syncSemua(): void
    {
        Database::exec(
            "UPDATE users u
             LEFT JOIN (
                SELECT p.guru_id, p.unit_id FROM guru_penempatan p
                JOIN tahun_ajaran t ON t.id = p.tahun_ajaran_id AND t.is_aktif = 1
                WHERE p.status = 'aktif'
             ) x ON x.guru_id = u.guru_id
             SET u.unit_id = x.unit_id
             WHERE u.guru_id IS NOT NULL AND u.role IN " . self::ROLE_UNIT
        );
    }

    /** Salin penempatan antar tahun ajaran. @return array{0:int,1:int} [disalin, dilewati] */
    public static function salin(int $dariTahun, int $keTahun, ?int $unitId): array
    {
        return Transaction::run(static function () use ($dariTahun, $keTahun, $unitId) {
            $sql = "SELECT p.guru_id, p.unit_id, p.jabatan, p.pangkat_golongan
                    FROM guru_penempatan p JOIN guru g ON g.id = p.guru_id
                    WHERE p.tahun_ajaran_id = ? AND p.status = 'aktif' AND g.status = 'aktif' AND g.deleted_at IS NULL";
            $args = [$dariTahun];
            if ($unitId) {
                $sql .= ' AND p.unit_id = ?';
                $args[] = $unitId;
            }
            $disalin = 0;
            $dilewati = 0;
            foreach (Database::all($sql, $args) as $p) {
                $ada = (int) Database::value(
                    'SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ? AND tahun_ajaran_id = ?', [$p['guru_id'], $keTahun]
                );
                // Kepala sekolah tidak disalin bila posisi itu di unit tujuan sudah terisi
                $kepsekAda = $p['jabatan'] === 'Kepala Sekolah' && (int) Database::value(
                    "SELECT COUNT(*) FROM guru_penempatan WHERE unit_id = ? AND tahun_ajaran_id = ? AND jabatan = 'Kepala Sekolah' AND status = 'aktif'",
                    [$p['unit_id'], $keTahun]
                ) > 0;
                if ($ada > 0 || $kepsekAda) {
                    $dilewati++;
                    continue;
                }
                Database::exec(
                    "INSERT INTO guru_penempatan (guru_id, tahun_ajaran_id, unit_id, jabatan, pangkat_golongan, status) VALUES (?,?,?,?,?,'aktif')",
                    [$p['guru_id'], $keTahun, $p['unit_id'], $p['jabatan'], $p['pangkat_golongan']]
                );
                $disalin++;
            }
            self::syncSemua();
            return [$disalin, $dilewati];
        });
    }
}
