<?php
/**
 * Admin > Guru > Penempatan: satu guru ditempatkan di satu unit per tahun ajaran.
 * Aturan: kepala sekolah hanya satu per unit per tahun; penempatan yang sudah dipakai
 * (wali kelas / penugasan mengajar) tidak bisa dipindah atau dihapus.
 */
class PenempatanAdminController extends CrudController
{
    private const LABEL = [
        'guru_id' => 'Guru', 'tahun_ajaran_id' => 'Tahun ajaran', 'unit_id' => 'Unit',
        'jabatan' => 'Jabatan', 'pangkat_golongan' => 'Pangkat/golongan', 'status' => 'Status',
    ];

    public function data(): void
    {
        $where = 'g.deleted_at IS NULL';
        $params = [];
        $tahun = (int) ($_GET['tahun_id'] ?? 0);
        $unit = (int) ($_GET['unit_id'] ?? 0);
        if ($tahun > 0) {
            $where .= ' AND p.tahun_ajaran_id = ?';
            $params[] = $tahun;
        }
        if ($unit > 0) {
            $where .= ' AND p.unit_id = ?';
            $params[] = $unit;
        }

        DataTable::respond([
            'select' => 'p.id, p.guru_id, g.nama AS guru, g.niy, t.nama AS tahun, u.nama AS unit,
                         p.jabatan, p.pangkat_golongan, p.status',
            'from'   => 'guru_penempatan p
                         JOIN guru g ON g.id = p.guru_id
                         JOIN tahun_ajaran t ON t.id = p.tahun_ajaran_id
                         JOIN unit_sekolah u ON u.id = p.unit_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'guru' => 'g.nama', 'tahun' => 't.nama', 'unit' => 'u.nama', 'jabatan' => 'p.jabatan',
                'pangkat_golongan' => 'p.pangkat_golongan', 'status' => 'p.status',
            ],
            'searchable' => ['guru', 'unit', 'jabatan', 'tahun'],
            'order'      => 't.nama DESC, u.urutan ASC, g.nama ASC',
        ]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Penempatan::find($id), 'Penempatan')]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rules(0), self::LABEL);
        $id = Penempatan::create($d);
        audit('tambah', 'guru_penempatan', $id, null, $d);
        $this->ok('Penempatan ditambahkan.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(Penempatan::find($id), 'Penempatan');
        $d = $this->validate($this->rules($id), self::LABEL);

        $berpindah = (int) $lama['guru_id'] !== (int) $d['guru_id']
            || (int) $lama['tahun_ajaran_id'] !== (int) $d['tahun_ajaran_id']
            || (int) $lama['unit_id'] !== (int) $d['unit_id'];
        if ($berpindah && ($dep = Penempatan::dependencies((int) $lama['guru_id'], (int) $lama['tahun_ajaran_id']))) {
            $this->fail('Guru, tahun ajaran, atau unit tidak dapat diubah karena masih terkait: ' . $this->ringkas($dep) . '.', [
                'unit_id' => 'Tidak dapat diubah.',
            ], 409);
        }
        Penempatan::update($id, $d);
        audit('ubah', 'guru_penempatan', $id, $lama, $d);
        $this->ok('Penempatan diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Penempatan::find($id), 'Penempatan');
        $dep = Penempatan::dependencies((int) $row['guru_id'], (int) $row['tahun_ajaran_id']);
        if ($dep) {
            $this->fail('Tidak bisa dihapus, masih terkait: ' . $this->ringkas($dep) . '.', [], 409);
        }
        Penempatan::delete($id);
        audit('hapus', 'guru_penempatan', $id, $row, null);
        $this->ok('Penempatan dihapus.');
    }

    /** Salin penempatan guru aktif dari satu tahun ajaran ke tahun lain */
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

        [$disalin, $dilewati] = Penempatan::salin((int) $d['dari_tahun_id'], (int) $d['ke_tahun_id'], $d['unit_id'] ? (int) $d['unit_id'] : null);
        audit('salin', 'guru_penempatan', null, null, $d + ['disalin' => $disalin, 'dilewati' => $dilewati]);

        if ($disalin === 0 && $dilewati === 0) {
            $this->fail('Tidak ada penempatan aktif pada tahun asal untuk disalin.', [], 409);
        }
        $this->ok("$disalin penempatan disalin" . ($dilewati ? ", $dilewati dilewati (sudah ada / kepala sekolah terisi)" : '')
            . '. Tinjau guru yang pindah unit lalu ubah manual.');
    }

    private function rules(int $id): array
    {
        return [
            'guru_id' => ['required', 'integer', 'exists:guru,id'],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id',
                static function ($v, $semua) use ($id) {
                    $ada = (int) Database::value(
                        'SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ? AND tahun_ajaran_id = ? AND id <> ?',
                        [(int) ($semua['guru_id'] ?? 0), (int) $v, $id]
                    );
                    return $ada > 0 ? 'Guru ini sudah ditempatkan pada tahun ajaran tersebut. Ubah data yang ada.' : null;
                },
            ],
            'unit_id' => ['required', 'integer', 'exists:unit_sekolah,id',
                static function ($v) {
                    $j = Database::value('SELECT jenjang FROM unit_sekolah WHERE id = ? AND deleted_at IS NULL', [(int) $v]);
                    return ($j && $j !== 'YAYASAN') ? null : 'Pilih unit sekolah (bukan yayasan).';
                },
            ],
            'jabatan' => ['required', 'in:' . implode(',', Penempatan::JABATAN),
                static function ($v, $semua) use ($id) {
                    if ($v !== 'Kepala Sekolah' || ($semua['status'] ?? 'aktif') !== 'aktif') {
                        return null;
                    }
                    $ada = (int) Database::value(
                        "SELECT COUNT(*) FROM guru_penempatan WHERE unit_id = ? AND tahun_ajaran_id = ? AND jabatan = 'Kepala Sekolah' AND status = 'aktif' AND id <> ?",
                        [(int) ($semua['unit_id'] ?? 0), (int) ($semua['tahun_ajaran_id'] ?? 0), $id]
                    );
                    return $ada > 0 ? 'Unit ini sudah memiliki Kepala Sekolah pada tahun ajaran tersebut.' : null;
                },
            ],
            'pangkat_golongan' => ['nullable', 'max:80'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ];
    }

    private function ringkas(array $dep): string
    {
        $out = [];
        foreach ($dep as $label => $n) {
            $out[] = "$label ($n)";
        }
        return implode(', ', $out);
    }
}
