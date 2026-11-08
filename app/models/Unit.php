<?php
/**
 * Model unit sekolah (tabel `unit_sekolah`). Entri jenjang YAYASAN dipakai untuk logo/profil yayasan.
 */
final class Unit
{
    /** Tabel yang menyimpan data per unit (kolom unit_id) */
    private const TERKAIT = [
        'kelas'             => 'Kelas',
        'mapel'             => 'Mata pelajaran',
        'guru_penempatan'   => 'Penempatan guru',
        'users'             => 'Akun pengguna',
        'standar_penilaian' => 'Penilaian standar mutu',
        'penilaian'         => 'Penilaian PKKS/Supervisi',
        'jadwal_supervisi'  => 'Jadwal supervisi',
        'kuesioner_kode'    => 'Kode kuesioner',
        'kuesioner_respon'  => 'Respon kuesioner',
        'bukti_file'        => 'Bukti fisik',
    ];

    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM unit_sekolah WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    /** Unit sekolah (bukan yayasan) yang aktif, untuk pilihan di formulir */
    public static function sekolahAktif(): array
    {
        return Database::all(
            "SELECT id, kode, nama, jenjang FROM unit_sekolah
             WHERE deleted_at IS NULL AND is_aktif = 1 AND jenjang <> 'YAYASAN' ORDER BY urutan, id"
        );
    }

    private static function nilai(array $d): array
    {
        return [
            $d['kode'], $d['nama'], $d['jenjang'], $d['npsn'], $d['alamat'], $d['kelurahan'], $d['kecamatan'],
            $d['kab_kota'], $d['provinsi'], $d['telepon'], $d['email'], $d['website'], $d['logo'],
            (int) ($d['urutan'] ?? 0), (int) $d['is_aktif'],
        ];
    }

    public static function create(array $d): int
    {
        return Database::insert(
            'INSERT INTO unit_sekolah (kode, nama, jenjang, npsn, alamat, kelurahan, kecamatan, kab_kota, provinsi,
                telepon, email, website, logo, urutan, is_aktif) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            self::nilai($d)
        );
    }

    public static function update(int $id, array $d): void
    {
        Database::exec(
            'UPDATE unit_sekolah SET kode = ?, nama = ?, jenjang = ?, npsn = ?, alamat = ?, kelurahan = ?, kecamatan = ?,
                kab_kota = ?, provinsi = ?, telepon = ?, email = ?, website = ?, logo = ?, urutan = ?, is_aktif = ?
             WHERE id = ?',
            array_merge(self::nilai($d), [$id])
        );
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM unit_sekolah WHERE id = ?', [$id]);
    }

    public static function dependencies(int $id): array
    {
        $hasil = [];
        foreach (self::TERKAIT as $tabel => $label) {
            $n = (int) Database::value("SELECT COUNT(*) FROM `$tabel` WHERE unit_id = ?", [$id]);
            if ($n > 0) {
                $hasil[$label] = $n;
            }
        }
        return $hasil;
    }
}
