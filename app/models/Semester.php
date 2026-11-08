<?php
/**
 * Model semester (tabel `semester`).
 */
final class Semester
{
    /** Tabel yang menyimpan data per semester (kolom semester_id) */
    private const TERKAIT = [
        'standar_penilaian' => 'Penilaian standar mutu',
        'penilaian'         => 'Penilaian PKKS/Supervisi',
        'jadwal_supervisi'  => 'Jadwal supervisi',
        'kuesioner_kode'    => 'Kode kuesioner',
        'kuesioner_respon'  => 'Respon kuesioner',
        'bukti_file'        => 'Bukti fisik',
        'mapel_semester'    => 'Mapel semester',
    ];

    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM semester WHERE id = ?', [$id]);
    }

    public static function create(array $d): int
    {
        return Transaction::run(static function () use ($d) {
            $id = Database::insert(
                'INSERT INTO semester (tahun_ajaran_id, nama, tgl_mulai, tgl_selesai, is_aktif, is_terkunci) VALUES (?,?,?,?,0,?)',
                [(int) $d['tahun_ajaran_id'], $d['nama'], $d['tgl_mulai'], $d['tgl_selesai'], (int) $d['is_terkunci']]
            );
            if (!empty($d['is_aktif'])) {
                self::jadikanAktif($id);
            }
            return $id;
        });
    }

    public static function update(int $id, array $d): void
    {
        Transaction::run(static function () use ($id, $d) {
            Database::exec(
                'UPDATE semester SET tahun_ajaran_id = ?, nama = ?, tgl_mulai = ?, tgl_selesai = ?, is_terkunci = ? WHERE id = ?',
                [(int) $d['tahun_ajaran_id'], $d['nama'], $d['tgl_mulai'], $d['tgl_selesai'], (int) $d['is_terkunci'], $id]
            );
            if (!empty($d['is_aktif'])) {
                self::jadikanAktif($id);
            } else {
                Database::exec('UPDATE semester SET is_aktif = 0 WHERE id = ?', [$id]);
            }
        });
    }

    /** Hanya satu semester aktif; tahun ajarannya otomatis ikut aktif, lalu akun disinkronkan ke penempatan */
    public static function jadikanAktif(int $id): void
    {
        $row = self::find($id);
        if (!$row) {
            return;
        }
        Transaction::run(static function () use ($id, $row) {
            Database::exec('UPDATE semester SET is_aktif = 0');
            Database::exec('UPDATE semester SET is_aktif = 1 WHERE id = ?', [$id]);
            Database::exec('UPDATE tahun_ajaran SET is_aktif = 0');
            Database::exec('UPDATE tahun_ajaran SET is_aktif = 1 WHERE id = ?', [(int) $row['tahun_ajaran_id']]);
            Penempatan::syncSemua();
        });
    }

    public static function setKunci(int $id, bool $kunci): void
    {
        Database::exec('UPDATE semester SET is_terkunci = ? WHERE id = ?', [$kunci ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM semester WHERE id = ?', [$id]);
    }

    public static function dependencies(int $id): array
    {
        $hasil = [];
        foreach (self::TERKAIT as $tabel => $label) {
            $n = (int) Database::value("SELECT COUNT(*) FROM `$tabel` WHERE semester_id = ?", [$id]);
            if ($n > 0) {
                $hasil[$label] = $n;
            }
        }
        return $hasil;
    }
}
