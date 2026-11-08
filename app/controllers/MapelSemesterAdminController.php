<?php
/**
 * Admin > Mapel per Semester: mapel yang dibuka pada semester tertentu (tiap semester bisa berganti).
 * tingkat kosong = semua tingkat. Semester terkunci tidak bisa diubah.
 */
class MapelSemesterAdminController extends CrudController
{
    private const LABEL = [
        'semester_id' => 'Semester', 'unit_id' => 'Unit sekolah', 'mapel_id' => 'Mapel',
        'tingkat' => 'Tingkat', 'jam_per_minggu' => 'Jam per minggu',
    ];

    public function data(): void
    {
        $semester = (int) ($_GET['semester_id'] ?? 0);
        if ($semester <= 0) {
            $semester = (int) (Periode::current()['id'] ?? 0);
        }
        $where = 'm.deleted_at IS NULL AND ms.semester_id = ?';
        $params = [$semester];
        $unit = (int) ($_GET['unit_id'] ?? 0);
        if ($unit > 0) {
            $where .= ' AND m.unit_id = ?';
            $params[] = $unit;
        }
        DataTable::respond([
            'select' => 'ms.id, m.nama AS mapel, m.kode, u.nama AS unit, ms.tingkat, ms.jam_per_minggu,
                         (SELECT COUNT(*) FROM penugasan_guru pg WHERE pg.mapel_semester_id = ms.id) AS jml_tugas',
            'from'   => 'mapel_semester ms JOIN mapel m ON m.id = ms.mapel_id JOIN unit_sekolah u ON u.id = m.unit_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'mapel' => 'm.nama', 'unit' => 'u.nama', 'tingkat' => 'ms.tingkat', 'jam_per_minggu' => 'ms.jam_per_minggu',
                'jml_tugas' => '(SELECT COUNT(*) FROM penugasan_guru pg WHERE pg.mapel_semester_id = ms.id)',
            ],
            'searchable' => ['mapel', 'unit'],
            'order'      => 'u.urutan ASC, m.nama ASC, ms.tingkat ASC',
        ]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(MapelSemester::find($id), 'Mapel semester')]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rules(), self::LABEL);
        $this->cekSemester((int) $d['semester_id']);
        $d['tingkat'] = (string) ($d['tingkat'] ?? '');
        $this->cekGanda($d, 0);
        $id = MapelSemester::create($d);
        audit('tambah', 'mapel_semester', $id, null, $d);
        $this->ok('Mapel dibuka pada semester.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(MapelSemester::find($id), 'Mapel semester');
        $this->cekSemester((int) $lama['semester_id']);
        $d = $this->validate($this->rules(), self::LABEL);
        $this->cekSemester((int) $d['semester_id']);
        $d['tingkat'] = (string) ($d['tingkat'] ?? '');
        $this->cekGanda($d, $id);

        $inti = (int) $lama['mapel_id'] !== (int) $d['mapel_id'] || (int) $lama['semester_id'] !== (int) $d['semester_id']
            || (string) $lama['tingkat'] !== $d['tingkat'];
        if ($inti && MapelSemester::dependencies($id)) {
            $this->fail('Mapel, semester, atau tingkat tidak dapat diubah karena sudah dipakai penugasan mengajar. Jam per minggu masih bisa diubah.', [
                'mapel_id' => 'Tidak dapat diubah.',
            ], 409);
        }
        MapelSemester::update($id, $d);
        audit('ubah', 'mapel_semester', $id, $lama, $d);
        $this->ok('Mapel semester diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(MapelSemester::find($id), 'Mapel semester');
        $this->cekSemester((int) $row['semester_id']);
        if (MapelSemester::dependencies($id)) {
            $this->fail('Tidak bisa dihapus, masih dipakai penugasan mengajar. Hapus penugasannya dulu.', [], 409);
        }
        MapelSemester::delete($id);
        audit('hapus', 'mapel_semester', $id, $row, null);
        $this->ok('Mapel dihapus dari semester.');
    }

    /** Salin mapel (dan opsional penugasan) dari satu semester ke semester lain */
    public function salin(): void
    {
        $d = $this->validate([
            'dari_semester_id' => ['required', 'integer', 'exists:semester,id'],
            'ke_semester_id'   => ['required', 'integer', 'exists:semester,id',
                static function ($v, $semua) {
                    return (int) $v === (int) ($semua['dari_semester_id'] ?? 0) ? 'Semester tujuan harus berbeda dari semester asal.' : null;
                },
            ],
            'unit_id'          => ['nullable', 'integer', 'exists:unit_sekolah,id'],
            'salin_penugasan'  => ['boolean'],
        ], ['dari_semester_id' => 'Semester asal', 'ke_semester_id' => 'Semester tujuan', 'unit_id' => 'Unit']);

        $this->cekSemester((int) $d['ke_semester_id']);
        $h = MapelSemester::salin(
            (int) $d['dari_semester_id'], (int) $d['ke_semester_id'],
            $d['unit_id'] ? (int) $d['unit_id'] : null, (bool) $d['salin_penugasan']
        );
        audit('salin', 'mapel_semester', null, null, $d + $h);

        if ($h['mapel'] === 0 && $h['mapel_lewat'] === 0) {
            $this->fail('Tidak ada mapel pada semester asal untuk disalin.', [], 409);
        }
        $pesan = "{$h['mapel']} mapel disalin" . ($h['mapel_lewat'] ? ", {$h['mapel_lewat']} sudah ada" : '');
        if ($d['salin_penugasan']) {
            $pesan .= ". Penugasan: {$h['tugas']} disalin" . ($h['tugas_lewat'] ? ", {$h['tugas_lewat']} dilewati (kelas/guru tidak tersedia atau sudah ada pengajar)" : '');
        }
        $this->ok($pesan . '.');
    }

    // ------------------------------------------------------------------ bantu

    private function rules(): array
    {
        return [
            'semester_id' => ['required', 'integer', 'exists:semester,id'],
            'unit_id'     => ['required', 'integer', 'exists:unit_sekolah,id'],
            'mapel_id'    => ['required', 'integer', 'exists:mapel,id',
                static function ($v, $semua) {
                    $ok = (int) Database::value(
                        'SELECT COUNT(*) FROM mapel WHERE id = ? AND unit_id = ? AND deleted_at IS NULL', [(int) $v, (int) ($semua['unit_id'] ?? 0)]
                    );
                    return $ok > 0 ? null : 'Mapel bukan milik unit yang dipilih.';
                },
            ],
            'tingkat' => ['nullable', 'max:10',
                static function ($v, $semua) {
                    $j = (string) Database::value('SELECT jenjang FROM unit_sekolah WHERE id = ?', [(int) ($semua['unit_id'] ?? 0)]);
                    $boleh = KelasAdminController::TINGKAT[$j] ?? [];
                    return in_array($v, $boleh, true) ? null : 'Tingkat tidak sesuai jenjang ' . $j . '. Kosongkan untuk semua tingkat.';
                },
            ],
            'jam_per_minggu' => ['nullable', 'integer', 'between:1,60'],
        ];
    }

    private function cekSemester(int $semesterId): void
    {
        if (MapelSemester::semesterTerkunci($semesterId)) {
            $this->fail('Semester terkunci. Buka kunci di menu Periode sebelum mengubah.', [], 409);
        }
    }

    private function cekGanda(array $d, int $id): void
    {
        if (MapelSemester::ada((int) $d['mapel_id'], (int) $d['semester_id'], $d['tingkat'], $id)) {
            $this->fail('Mapel dengan tingkat ini sudah dibuka pada semester tersebut.', ['mapel_id' => 'Sudah ada pada semester ini.']);
        }
    }
}
