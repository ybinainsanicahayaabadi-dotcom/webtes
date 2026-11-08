<?php
/**
 * Admin > Periode: tahun ajaran dan semester (CRUD via modal).
 * Aturan:
 *  - hanya satu tahun ajaran dan satu semester aktif; mengaktifkan semester ikut mengaktifkan tahun ajarannya
 *  - semester terkunci tidak bisa diubah/dihapus sebelum dibuka kuncinya
 *  - data yang sudah dipakai modul lain tidak bisa dihapus
 *  - format nama tahun ajaran: 2026-2027
 */
class PeriodeAdminController extends CrudController
{
    private const LABEL_TAHUN = [
        'nama' => 'Tahun ajaran', 'tgl_mulai' => 'Tanggal mulai', 'tgl_selesai' => 'Tanggal selesai',
    ];
    private const LABEL_SEMESTER = [
        'tahun_ajaran_id' => 'Tahun ajaran', 'nama' => 'Semester',
        'tgl_mulai' => 'Tanggal mulai', 'tgl_selesai' => 'Tanggal selesai',
    ];

    public function index(): void
    {
        $this->render('admin/periode/index', [
            'pageTitle' => 'Periode: Tahun Ajaran & Semester',
            'pageCss'   => ['crud.css'],
            'pageJs'    => ['crud.js', 'admin-periode.js'],
            'tahunList' => Database::all('SELECT id, nama FROM tahun_ajaran ORDER BY nama DESC'),
        ]);
    }

    // =============================================================== TAHUN AJARAN

    public function tahunData(): void
    {
        DataTable::respond([
            'select' => 't.id, t.nama, t.tgl_mulai, t.tgl_selesai, t.is_aktif,
                         (SELECT COUNT(*) FROM semester x WHERE x.tahun_ajaran_id = t.id) AS jml_semester',
            'from'   => 'tahun_ajaran t',
            'columns' => [
                'nama' => 't.nama', 'tgl_mulai' => 't.tgl_mulai', 'tgl_selesai' => 't.tgl_selesai',
                'is_aktif' => 't.is_aktif',
                'jml_semester' => '(SELECT COUNT(*) FROM semester x WHERE x.tahun_ajaran_id = t.id)',
            ],
            'searchable' => ['nama'],
            'order'      => 't.nama DESC',
        ]);
    }

    public function tahunShow(int $id): void
    {
        $this->ok('', ['data' => $this->found(TahunAjaran::find($id), 'Tahun ajaran')]);
    }

    public function tahunStore(): void
    {
        $d = $this->validate($this->rulesTahun(0), self::LABEL_TAHUN);
        $id = TahunAjaran::create($d);
        audit('tambah', 'tahun_ajaran', $id, null, $d);
        $this->ok('Tahun ajaran ditambahkan.');
    }

    public function tahunUpdate(int $id): void
    {
        $lama = $this->found(TahunAjaran::find($id), 'Tahun ajaran');
        $d = $this->validate($this->rulesTahun($id), self::LABEL_TAHUN);
        TahunAjaran::update($id, $d);
        audit('ubah', 'tahun_ajaran', $id, $lama, $d);
        $this->ok('Tahun ajaran diperbarui.');
    }

    public function tahunDelete(int $id): void
    {
        $row = $this->found(TahunAjaran::find($id), 'Tahun ajaran');
        if ((int) $row['is_aktif'] === 1) {
            $this->fail('Tahun ajaran aktif tidak dapat dihapus.', [], 409);
        }
        $dep = TahunAjaran::dependencies($id);
        if ($dep) {
            $this->fail('Tidak bisa dihapus, masih dipakai: ' . $this->ringkasDependensi($dep) . '.', [], 409);
        }
        TahunAjaran::delete($id);
        audit('hapus', 'tahun_ajaran', $id, $row, null);
        $this->ok('Tahun ajaran dihapus.');
    }

    private function rulesTahun(int $id): array
    {
        return [
            'nama' => ['required', 'max:20', "unique:tahun_ajaran,nama,$id",
                static function ($v) {
                    if (!preg_match('#^(\d{4})-(\d{4})$#', $v, $m) || (int) $m[2] !== (int) $m[1] + 1) {
                        return 'Format harus seperti 2026-2027 (tahun berurutan).';
                    }
                    return null;
                },
            ],
            'tgl_mulai'   => ['nullable', 'date'],
            'tgl_selesai' => ['nullable', 'date', 'after_or_equal:tgl_mulai'],
            'is_aktif'    => ['boolean'],
        ];
    }

    // =============================================================== SEMESTER

    public function semesterData(): void
    {
        DataTable::respond([
            'select' => 's.id, s.tahun_ajaran_id, t.nama AS tahun, s.nama AS semester,
                         s.tgl_mulai, s.tgl_selesai, s.is_aktif, s.is_terkunci',
            'from'   => 'semester s JOIN tahun_ajaran t ON t.id = s.tahun_ajaran_id',
            'columns' => [
                'tahun' => 't.nama', 'semester' => 's.nama', 'tgl_mulai' => 's.tgl_mulai',
                'tgl_selesai' => 's.tgl_selesai', 'is_aktif' => 's.is_aktif', 'is_terkunci' => 's.is_terkunci',
            ],
            'searchable' => ['tahun', 'semester'],
            'order'      => 't.nama DESC, s.nama ASC',
        ]);
    }

    public function semesterShow(int $id): void
    {
        $this->ok('', ['data' => $this->found(Semester::find($id), 'Semester')]);
    }

    public function semesterStore(): void
    {
        $d = $this->validate($this->rulesSemester(0), self::LABEL_SEMESTER);
        $this->cekAktifTerkunci($d);
        $id = Semester::create($d);
        audit('tambah', 'semester', $id, null, $d);
        $this->ok('Semester ditambahkan.');
    }

    public function semesterUpdate(int $id): void
    {
        $lama = $this->found(Semester::find($id), 'Semester');
        if ((int) $lama['is_terkunci'] === 1) {
            $this->fail('Semester terkunci. Buka kunci terlebih dahulu sebelum mengubah.', [], 409);
        }
        $d = $this->validate($this->rulesSemester($id), self::LABEL_SEMESTER);
        $this->cekAktifTerkunci($d);
        Semester::update($id, $d);
        audit('ubah', 'semester', $id, $lama, $d);
        $this->ok('Semester diperbarui.');
    }

    /** Kunci atau buka kunci semester (toggle) */
    public function semesterKunci(int $id): void
    {
        $row = $this->found(Semester::find($id), 'Semester');
        $kunci = (int) $row['is_terkunci'] === 0;
        if ($kunci && (int) $row['is_aktif'] === 1) {
            $this->fail('Semester aktif tidak dapat dikunci. Aktifkan semester lain terlebih dahulu.', [], 409);
        }
        Semester::setKunci($id, $kunci);
        audit($kunci ? 'kunci' : 'buka_kunci', 'semester', $id);
        $this->ok($kunci ? 'Semester dikunci.' : 'Kunci semester dibuka.');
    }

    public function semesterDelete(int $id): void
    {
        $row = $this->found(Semester::find($id), 'Semester');
        if ((int) $row['is_terkunci'] === 1) {
            $this->fail('Semester terkunci. Buka kunci terlebih dahulu.', [], 409);
        }
        if ((int) $row['is_aktif'] === 1) {
            $this->fail('Semester aktif tidak dapat dihapus.', [], 409);
        }
        $dep = Semester::dependencies($id);
        if ($dep) {
            $this->fail('Tidak bisa dihapus, masih dipakai: ' . $this->ringkasDependensi($dep) . '.', [], 409);
        }
        Semester::delete($id);
        audit('hapus', 'semester', $id, $row, null);
        $this->ok('Semester dihapus.');
    }

    private function rulesSemester(int $id): array
    {
        return [
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'nama' => ['required', 'in:Ganjil,Genap',
                static function ($v, $semua) use ($id) {
                    $ada = (int) Database::value(
                        'SELECT COUNT(*) FROM semester WHERE tahun_ajaran_id = ? AND nama = ? AND id <> ?',
                        [(int) ($semua['tahun_ajaran_id'] ?? 0), $v, $id]
                    );
                    return $ada > 0 ? 'Semester ini sudah ada pada tahun ajaran tersebut.' : null;
                },
            ],
            'tgl_mulai'   => ['nullable', 'date'],
            'tgl_selesai' => ['nullable', 'date', 'after_or_equal:tgl_mulai'],
            'is_aktif'    => ['boolean'],
            'is_terkunci' => ['boolean'],
        ];
    }

    private function cekAktifTerkunci(array $d): void
    {
        if (!empty($d['is_aktif']) && !empty($d['is_terkunci'])) {
            $this->fail('Semester aktif tidak boleh dikunci.', ['is_terkunci' => 'Matikan kunci atau status aktif.']);
        }
    }

    private function ringkasDependensi(array $dep): string
    {
        $out = [];
        foreach ($dep as $label => $n) {
            $out[] = "$label ($n)";
        }
        return implode(', ', $out);
    }
}
