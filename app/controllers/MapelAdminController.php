<?php
/**
 * Admin > Mapel & Penugasan: halaman dengan tiga tab.
 * Controller ini menangani halaman + katalog mapel per unit. Mapel per semester dan penugasan
 * ada di MapelSemesterAdminController dan PenugasanAdminController.
 */
class MapelAdminController extends CrudController
{
    private const LABEL = ['unit_id' => 'Unit sekolah', 'kode' => 'Kode mapel', 'nama' => 'Nama mapel'];

    public function index(): void
    {
        $periode = Periode::current();
        $semesterList = [];
        foreach (Periode::semesters() as $s) {
            $semesterList[] = [
                'id' => (int) $s['id'],
                'label' => $s['tahun'] . ' - ' . $s['semester'] . ($s['is_terkunci'] ? ' (terkunci)' : ''),
            ];
        }
        $this->render('admin/mapel/index', [
            'pageTitle'    => 'Mapel & Penugasan Mengajar',
            'pageCss'      => ['crud.css'],
            'pageJs'       => ['crud.js', 'admin-mapel.js', 'admin-penugasan.js'],
            'unitList'     => Unit::sekolahAktif(),
            'semesterList' => $semesterList,
            'semesterAktif' => (int) ($periode['id'] ?? 0),
            'mapelList'    => Mapel::opsi(),
            'tingkatMap'   => KelasAdminController::TINGKAT,
        ]);
    }

    public function data(): void
    {
        $where = 'm.deleted_at IS NULL';
        $params = [];
        $unit = (int) ($_GET['unit_id'] ?? 0);
        if ($unit > 0) {
            $where .= ' AND m.unit_id = ?';
            $params[] = $unit;
        }
        DataTable::respond([
            'select' => 'm.id, m.unit_id, u.nama AS unit, m.kode, m.nama,
                         (SELECT COUNT(*) FROM mapel_semester x WHERE x.mapel_id = m.id) AS jml_semester',
            'from'   => 'mapel m JOIN unit_sekolah u ON u.id = m.unit_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'unit' => 'u.nama', 'kode' => 'm.kode', 'nama' => 'm.nama',
                'jml_semester' => '(SELECT COUNT(*) FROM mapel_semester x WHERE x.mapel_id = m.id)',
            ],
            'searchable' => ['nama', 'kode', 'unit'],
            'order'      => 'u.urutan ASC, m.nama ASC',
        ]);
    }

    /** Daftar mapel terbaru untuk pilihan di modal Mapel per Semester */
    public function opsi(): void
    {
        $this->ok('', ['data' => Mapel::opsi()]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Mapel::find($id), 'Mapel')]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rules(0), self::LABEL);
        $id = Mapel::create($d);
        audit('tambah', 'mapel', $id, null, $d);
        $this->ok('Mapel ditambahkan.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(Mapel::find($id), 'Mapel');
        $d = $this->validate($this->rules($id), self::LABEL);
        if ((int) $lama['unit_id'] !== (int) $d['unit_id'] && Mapel::dependencies($id)) {
            $this->fail('Unit tidak dapat diubah karena mapel sudah dibuka pada semester.', ['unit_id' => 'Tidak dapat diubah.'], 409);
        }
        Mapel::update($id, $d);
        audit('ubah', 'mapel', $id, $lama, $d);
        $this->ok('Mapel diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Mapel::find($id), 'Mapel');
        $dep = Mapel::dependencies($id);
        if ($dep) {
            $this->fail('Tidak bisa dihapus, mapel sudah dibuka pada ' . $dep['Mapel semester'] . ' semester. Hapus dari tab Mapel per Semester dulu.', [], 409);
        }
        Mapel::delete($id);
        audit('hapus', 'mapel', $id, $row, null);
        $this->ok('Mapel dihapus.');
    }

    private function rules(int $id): array
    {
        return [
            'unit_id' => ['required', 'integer', 'exists:unit_sekolah,id',
                static function ($v) {
                    $j = Database::value('SELECT jenjang FROM unit_sekolah WHERE id = ? AND deleted_at IS NULL', [(int) $v]);
                    return ($j && $j !== 'YAYASAN') ? null : 'Pilih unit sekolah (bukan yayasan).';
                },
            ],
            'kode' => ['nullable', 'max:20',
                static function ($v, $semua) use ($id) {
                    $ada = (int) Database::value(
                        'SELECT COUNT(*) FROM mapel WHERE unit_id = ? AND kode = ? AND id <> ? AND deleted_at IS NULL',
                        [(int) ($semua['unit_id'] ?? 0), $v, $id]
                    );
                    return $ada > 0 ? 'Kode sudah dipakai mapel lain di unit ini.' : null;
                },
            ],
            'nama' => ['required', 'max:120',
                static function ($v, $semua) use ($id) {
                    $ada = (int) Database::value(
                        'SELECT COUNT(*) FROM mapel WHERE unit_id = ? AND nama = ? AND id <> ? AND deleted_at IS NULL',
                        [(int) ($semua['unit_id'] ?? 0), $v, $id]
                    );
                    return $ada > 0 ? 'Nama mapel sudah ada di unit ini.' : null;
                },
            ],
        ];
    }
}
