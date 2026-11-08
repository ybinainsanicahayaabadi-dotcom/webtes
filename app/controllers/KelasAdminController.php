<?php
/**
 * Admin > Kelas: kelas per tahun ajaran (CRUD via modal) + salin kelas antar tahun ajaran.
 * Wali kelas dipilih dari guru yang ditempatkan di unit tersebut pada tahun ajaran yang sama.
 */
class KelasAdminController extends CrudController
{
    /** Tingkat yang diizinkan per jenjang */
    public const TINGKAT = [
        'TK'  => ['PG', 'A', 'B'],
        'SD'  => ['1', '2', '3', '4', '5', '6'],
        'SMP' => ['7', '8', '9'],
        'SMA' => ['10', '11', '12'],
    ];

    private const LABEL = [
        'unit_id' => 'Unit sekolah', 'tahun_ajaran_id' => 'Tahun ajaran', 'tingkat' => 'Tingkat',
        'nama' => 'Nama kelas', 'wali_guru_id' => 'Wali kelas', 'jumlah_siswa' => 'Jumlah siswa',
    ];

    public function index(): void
    {
        $periode = Periode::current();
        $this->render('admin/kelas/index', [
            'pageTitle'   => 'Kelas',
            'pageCss'     => ['crud.css'],
            'pageJs'      => ['crud.js', 'admin-kelas.js'],
            'unitList'    => Unit::sekolahAktif(),
            'tahunList'   => Database::all('SELECT id, nama FROM tahun_ajaran ORDER BY nama DESC'),
            'tahunAktif'  => (int) ($periode['tahun_id'] ?? 0),
            'tingkatMap'  => self::TINGKAT,
        ]);
    }

    public function data(): void
    {
        $where = 'k.deleted_at IS NULL';
        $params = [];
        $unit = (int) ($_GET['unit_id'] ?? 0);
        $tahun = (int) ($_GET['tahun_id'] ?? 0);
        if ($unit > 0) {
            $where .= ' AND k.unit_id = ?';
            $params[] = $unit;
        }
        if ($tahun > 0) {
            $where .= ' AND k.tahun_ajaran_id = ?';
            $params[] = $tahun;
        }

        DataTable::respond([
            'select' => 'k.id, k.unit_id, k.tahun_ajaran_id, u.nama AS unit, t.nama AS tahun, k.tingkat, k.nama,
                         g.nama AS wali, k.jumlah_siswa',
            'from'   => 'kelas k JOIN unit_sekolah u ON u.id = k.unit_id
                         JOIN tahun_ajaran t ON t.id = k.tahun_ajaran_id
                         LEFT JOIN guru g ON g.id = k.wali_guru_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'unit' => 'u.nama', 'tahun' => 't.nama', 'tingkat' => 'CAST(k.tingkat AS UNSIGNED)',
                'nama' => 'k.nama', 'wali' => 'g.nama', 'jumlah_siswa' => 'k.jumlah_siswa',
            ],
            'searchable' => ['unit', 'nama', 'wali', 'tahun'],
            'order'      => 't.nama DESC, u.urutan ASC, CAST(k.tingkat AS UNSIGNED) ASC, k.nama ASC',
        ]);
    }

    /** Pilihan wali kelas untuk kombinasi unit + tahun ajaran */
    public function guruOpsi(): void
    {
        $unit = (int) ($_GET['unit_id'] ?? 0);
        $tahun = (int) ($_GET['tahun_id'] ?? 0);
        $data = ($unit > 0 && $tahun > 0) ? Kelas::guruTersedia($unit, $tahun) : [];
        $this->ok('', ['data' => $data]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Kelas::find($id), 'Kelas')]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rules(0), self::LABEL);
        $id = Kelas::create($d);
        audit('tambah', 'kelas', $id, null, $d);
        $this->ok('Kelas ditambahkan.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(Kelas::find($id), 'Kelas');
        $d = $this->validate($this->rules($id), self::LABEL);
        if (((int) $lama['unit_id'] !== (int) $d['unit_id'] || (int) $lama['tahun_ajaran_id'] !== (int) $d['tahun_ajaran_id'])
            && Kelas::dependencies($id)) {
            $this->fail('Unit atau tahun ajaran tidak dapat diubah karena kelas sudah dipakai penugasan/penilaian.', [
                'unit_id' => 'Tidak dapat diubah.',
            ], 409);
        }
        Kelas::update($id, $d);
        audit('ubah', 'kelas', $id, $lama, $d);
        $this->ok('Kelas diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Kelas::find($id), 'Kelas');
        $dep = Kelas::dependencies($id);
        if ($dep) {
            $out = [];
            foreach ($dep as $label => $n) {
                $out[] = "$label ($n)";
            }
            $this->fail('Tidak bisa dihapus, masih dipakai: ' . implode(', ', $out) . '.', [], 409);
        }
        Kelas::delete($id);
        audit('hapus', 'kelas', $id, $row, null);
        $this->ok('Kelas dihapus.');
    }

    /** Salin kelas dari satu tahun ajaran ke tahun ajaran lain (nama yang sudah ada dilewati) */
    public function salin(): void
    {
        $d = $this->validate([
            'dari_tahun_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'ke_tahun_id'   => ['required', 'integer', 'exists:tahun_ajaran,id',
                static function ($v, $semua) {
                    return (int) $v === (int) ($semua['dari_tahun_id'] ?? 0) ? 'Tahun tujuan harus berbeda dari tahun asal.' : null;
                },
            ],
            'unit_id' => ['nullable', 'integer', 'exists:unit_sekolah,id'],
        ], ['dari_tahun_id' => 'Tahun asal', 'ke_tahun_id' => 'Tahun tujuan', 'unit_id' => 'Unit']);

        [$disalin, $dilewati] = Kelas::salin((int) $d['dari_tahun_id'], (int) $d['ke_tahun_id'], $d['unit_id'] ? (int) $d['unit_id'] : null);
        audit('salin', 'kelas', null, null, $d + ['disalin' => $disalin, 'dilewati' => $dilewati]);

        if ($disalin === 0 && $dilewati === 0) {
            $this->fail('Tidak ada kelas pada tahun asal untuk disalin.', [], 409);
        }
        $this->ok("$disalin kelas disalin" . ($dilewati ? ", $dilewati dilewati karena sudah ada" : '') . '.');
    }

    // ------------------------------------------------------------------ bantu

    private function rules(int $id): array
    {
        return [
            'unit_id' => ['required', 'integer', 'exists:unit_sekolah,id',
                static function ($v) {
                    $j = Database::value('SELECT jenjang FROM unit_sekolah WHERE id = ? AND deleted_at IS NULL', [(int) $v]);
                    return ($j && $j !== 'YAYASAN') ? null : 'Pilih unit sekolah (bukan yayasan).';
                },
            ],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'tingkat' => ['required', 'max:10',
                static function ($v, $semua) {
                    $j = (string) Database::value('SELECT jenjang FROM unit_sekolah WHERE id = ?', [(int) ($semua['unit_id'] ?? 0)]);
                    $boleh = KelasAdminController::TINGKAT[$j] ?? null;
                    if ($boleh !== null && !in_array($v, $boleh, true)) {
                        return 'Tingkat untuk jenjang ' . $j . ' harus salah satu dari: ' . implode(', ', $boleh) . '.';
                    }
                    return null;
                },
            ],
            'nama' => ['required', 'max:50',
                static function ($v, $semua) use ($id) {
                    $ada = (int) Database::value(
                        'SELECT COUNT(*) FROM kelas WHERE unit_id = ? AND tahun_ajaran_id = ? AND nama = ? AND id <> ? AND deleted_at IS NULL',
                        [(int) ($semua['unit_id'] ?? 0), (int) ($semua['tahun_ajaran_id'] ?? 0), $v, $id]
                    );
                    return $ada > 0 ? 'Nama kelas sudah ada pada unit dan tahun ajaran ini.' : null;
                },
            ],
            'wali_guru_id' => ['nullable', 'integer',
                static function ($v, $semua) {
                    $ada = (int) Database::value(
                        "SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ? AND unit_id = ? AND tahun_ajaran_id = ? AND status = 'aktif'",
                        [(int) $v, (int) ($semua['unit_id'] ?? 0), (int) ($semua['tahun_ajaran_id'] ?? 0)]
                    );
                    return $ada > 0 ? null : 'Wali kelas harus guru yang ditempatkan di unit ini pada tahun ajaran tersebut.';
                },
            ],
            'jumlah_siswa' => ['nullable', 'integer', 'between:0,200'],
        ];
    }
}
