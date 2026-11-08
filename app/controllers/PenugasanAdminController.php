<?php
/**
 * Admin > Penugasan Mengajar: guru - kelas - mapel semester, per semester.
 * Aturan: guru harus ditempatkan di unit itu pada tahun ajaran semester; kelas dan mapel harus milik unit yang sama;
 * satu kelas hanya punya satu pengajar untuk satu mapel; semester terkunci tidak bisa diubah.
 * Mode tambah boleh memilih beberapa kelas sekaligus; mode ubah hanya satu kelas.
 */
class PenugasanAdminController extends CrudController
{
    private const LABEL = [
        'semester_id' => 'Semester', 'unit_id' => 'Unit sekolah', 'guru_id' => 'Guru', 'mapel_semester_id' => 'Mapel',
    ];

    public function data(): void
    {
        $semester = (int) ($_GET['semester_id'] ?? 0);
        if ($semester <= 0) {
            $semester = (int) (Periode::current()['id'] ?? 0);
        }
        $where = 'ms.semester_id = ?';
        $params = [$semester];
        $unit = (int) ($_GET['unit_id'] ?? 0);
        if ($unit > 0) {
            $where .= ' AND k.unit_id = ?';
            $params[] = $unit;
        }
        DataTable::respond([
            'select' => 'pg.id, g.nama AS guru, k.nama AS kelas, m.nama AS mapel, ms.tingkat, ms.jam_per_minggu, u.nama AS unit',
            'from'   => 'penugasan_guru pg
                         JOIN guru g ON g.id = pg.guru_id
                         JOIN kelas k ON k.id = pg.kelas_id
                         JOIN mapel_semester ms ON ms.id = pg.mapel_semester_id
                         JOIN mapel m ON m.id = ms.mapel_id
                         JOIN unit_sekolah u ON u.id = k.unit_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'guru' => 'g.nama', 'kelas' => 'k.nama', 'mapel' => 'm.nama', 'unit' => 'u.nama',
                'jam_per_minggu' => 'ms.jam_per_minggu',
            ],
            'searchable' => ['guru', 'kelas', 'mapel', 'unit'],
            'order'      => 'u.urutan ASC, CAST(k.tingkat AS UNSIGNED) ASC, k.nama ASC, m.nama ASC',
        ]);
    }

    /** Pilihan guru/kelas/mapel untuk kombinasi semester + unit */
    public function opsi(): void
    {
        $this->ok('', Penugasan::opsi((int) ($_GET['semester_id'] ?? 0), (int) ($_GET['unit_id'] ?? 0)));
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Penugasan::find($id), 'Penugasan')]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rulesDasar(), self::LABEL);
        $kelasIds = $this->kelasIds();
        if (!$kelasIds) {
            $this->fail('Periksa kembali isian Anda.', ['kelas_id' => 'Pilih minimal satu kelas.']);
        }
        $this->cekSemester((int) $d['semester_id']);

        // Periksa semua kombinasi dulu; satu galat membatalkan seluruh penambahan
        $galat = [];
        foreach ($kelasIds as $kid) {
            $msg = Penugasan::cek((int) $d['semester_id'], (int) $d['unit_id'], (int) $d['guru_id'], (int) $d['mapel_semester_id'], $kid, 0);
            if ($msg !== null) {
                $galat[] = $msg;
            }
        }
        if ($galat) {
            $this->fail('Periksa kembali isian Anda.', ['kelas_id' => implode(' ', array_unique($galat))]);
        }

        $dibuat = Transaction::run(static function () use ($d, $kelasIds) {
            $n = 0;
            foreach ($kelasIds as $kid) {
                Penugasan::create((int) $d['guru_id'], $kid, (int) $d['mapel_semester_id']);
                $n++;
            }
            return $n;
        });
        audit('tambah', 'penugasan_guru', null, null, $d + ['kelas' => $kelasIds]);
        $this->ok("$dibuat penugasan ditambahkan.");
    }

    public function update(int $id): void
    {
        $lama = $this->found(Penugasan::find($id), 'Penugasan');
        $this->cekSemester((int) $lama['semester_id']);
        $d = $this->validate($this->rulesDasar(), self::LABEL);
        $this->cekSemester((int) $d['semester_id']);

        $kelasIds = $this->kelasIds();
        if (count($kelasIds) !== 1) {
            $this->fail('Periksa kembali isian Anda.', ['kelas_id' => 'Mode ubah hanya untuk satu kelas.']);
        }
        $msg = Penugasan::cek((int) $d['semester_id'], (int) $d['unit_id'], (int) $d['guru_id'], (int) $d['mapel_semester_id'], $kelasIds[0], $id);
        if ($msg !== null) {
            $this->fail('Periksa kembali isian Anda.', ['kelas_id' => $msg]);
        }
        Penugasan::update($id, (int) $d['guru_id'], $kelasIds[0], (int) $d['mapel_semester_id']);
        audit('ubah', 'penugasan_guru', $id, $lama, $d + ['kelas' => $kelasIds]);
        $this->ok('Penugasan diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Penugasan::find($id), 'Penugasan');
        $this->cekSemester((int) $row['semester_id']);
        Penugasan::delete($id);
        audit('hapus', 'penugasan_guru', $id, $row, null);
        $this->ok('Penugasan dihapus.');
    }

    // ------------------------------------------------------------------ bantu

    private function rulesDasar(): array
    {
        return [
            'semester_id'       => ['required', 'integer', 'exists:semester,id'],
            'unit_id'           => ['required', 'integer', 'exists:unit_sekolah,id'],
            'guru_id'           => ['required', 'integer', 'exists:guru,id'],
            'mapel_semester_id' => ['required', 'integer', 'exists:mapel_semester,id'],
        ];
    }

    /** @return int[] daftar id kelas unik dari kelas_id[] (atau kelas_id tunggal) */
    private function kelasIds(): array
    {
        $in = $_POST['kelas_id'] ?? [];
        $in = is_array($in) ? $in : [$in];
        $ids = [];
        foreach ($in as $v) {
            if (is_string($v) && preg_match('/^\d+$/', $v) && (int) $v > 0) {
                $ids[(int) $v] = true;
            }
        }
        return array_keys($ids);
    }

    private function cekSemester(int $semesterId): void
    {
        if (MapelSemester::semesterTerkunci($semesterId)) {
            $this->fail('Semester terkunci. Buka kunci di menu Periode sebelum mengubah.', [], 409);
        }
    }
}
