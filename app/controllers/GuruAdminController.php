<?php
/**
 * Admin > Guru: data diri + foto (CRUD via modal). Penempatan unit diatur terpisah (PenempatanAdminController).
 */
class GuruAdminController extends CrudController
{
    private const LABEL = [
        'niy' => 'NIY', 'nuptk' => 'NUPTK', 'nama' => 'Nama lengkap', 'jenis_kelamin' => 'Jenis kelamin',
        'tempat_lahir' => 'Tempat lahir', 'tanggal_lahir' => 'Tanggal lahir', 'pendidikan_terakhir' => 'Pendidikan terakhir',
        'tmt' => 'TMT', 'email' => 'Email', 'telepon' => 'Telepon', 'alamat' => 'Alamat', 'status' => 'Status',
    ];

    public function index(): void
    {
        $periode = Periode::current();
        $this->render('admin/guru/index', [
            'pageTitle'  => 'Guru & Tenaga Kependidikan',
            'pageCss'    => ['crud.css'],
            'pageJs'     => ['crud.js', 'admin-guru.js'],
            'unitList'   => Unit::sekolahAktif(),
            'tahunList'  => Database::all('SELECT id, nama FROM tahun_ajaran ORDER BY nama DESC'),
            'tahunAktif' => (int) ($periode['tahun_id'] ?? 0),
            'guruList'   => Guru::opsi(),
            'jabatanList' => Penempatan::JABATAN,
        ]);
    }

    public function data(): void
    {
        $tahun = (int) ($_GET['tahun_id'] ?? 0);
        if ($tahun <= 0) {
            $tahun = (int) (Periode::current()['tahun_id'] ?? 0);
        }
        $where = 'g.deleted_at IS NULL';
        $params = [$tahun];   // untuk "?" pada JOIN penempatan

        $unit = (string) ($_GET['unit_id'] ?? '');
        if ($unit === 'none') {
            $where .= ' AND p.id IS NULL';
        } elseif ((int) $unit > 0) {
            $where .= ' AND p.unit_id = ?';
            $params[] = (int) $unit;
        }
        $status = (string) ($_GET['status'] ?? '');
        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $where .= ' AND g.status = ?';
            $params[] = $status;
        }

        DataTable::respond([
            'select' => 'g.id, g.niy, g.nama, g.jenis_kelamin, g.email, g.telepon, g.foto, g.status,
                         p.jabatan, u.nama AS unit',
            'from'   => 'guru g
                         LEFT JOIN guru_penempatan p ON p.guru_id = g.id AND p.tahun_ajaran_id = ?
                         LEFT JOIN unit_sekolah u ON u.id = p.unit_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'nama' => 'g.nama', 'niy' => 'g.niy', 'unit' => 'u.nama', 'jabatan' => 'p.jabatan',
                'telepon' => 'g.telepon', 'email' => 'g.email', 'status' => 'g.status',
            ],
            'searchable' => ['nama', 'niy', 'unit', 'jabatan', 'telepon', 'email'],
            'order'      => 'g.nama ASC',
        ]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Guru::find($id), 'Guru')]);
    }

    /** Riwayat penempatan seorang guru dari tahun ke tahun */
    public function riwayat(int $id): void
    {
        $guru = $this->found(Guru::find($id), 'Guru');
        $rows = Database::all(
            'SELECT t.nama AS tahun, u.nama AS unit, p.jabatan, p.pangkat_golongan, p.status
             FROM guru_penempatan p
             JOIN tahun_ajaran t ON t.id = p.tahun_ajaran_id
             JOIN unit_sekolah u ON u.id = p.unit_id
             WHERE p.guru_id = ? ORDER BY t.nama DESC',
            [$id]
        );
        $this->ok('', ['guru' => $guru['nama'], 'data' => $rows]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rules(0), self::LABEL);
        $d['foto'] = $this->prosesFoto($d['nama']);
        try {
            $id = Guru::create($d);
        } catch (Throwable $e) {
            ImageUpload::remove(Guru::dirFoto(), $d['foto']);
            throw $e;
        }
        audit('tambah', 'guru', $id, null, array_diff_key($d, ['foto' => 1]));
        $this->ok('Data guru ditambahkan. Atur penempatannya pada tab Penempatan.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(Guru::find($id), 'Guru');
        $d = $this->validate($this->rules($id), self::LABEL);

        $baru = $this->prosesFoto($d['nama']);
        if ($baru !== null) {
            $d['foto'] = $baru;
        } elseif (!empty($_POST['hapus_foto'])) {
            $d['foto'] = null;
        } else {
            $d['foto'] = $lama['foto'];
        }

        try {
            Guru::update($id, $d);
        } catch (Throwable $e) {
            if ($baru !== null) {
                ImageUpload::remove(Guru::dirFoto(), $baru);
            }
            throw $e;
        }
        if ($d['foto'] !== $lama['foto']) {
            ImageUpload::remove(Guru::dirFoto(), $lama['foto']);
        }
        audit('ubah', 'guru', $id, $lama, array_diff_key($d, ['foto' => 1]));
        $this->ok('Data guru diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Guru::find($id), 'Guru');
        $dep = Guru::dependencies($id);
        if ($dep) {
            $out = [];
            foreach ($dep as $label => $n) {
                $out[] = "$label ($n)";
            }
            $this->fail('Tidak bisa dihapus, masih terkait: ' . implode(', ', $out) . '. Ubah status menjadi nonaktif bila guru sudah tidak bertugas.', [], 409);
        }
        Guru::delete($id);
        ImageUpload::remove(Guru::dirFoto(), $row['foto']);
        audit('hapus', 'guru', $id, $row, null);
        $this->ok('Data guru dihapus.');
    }

    // ------------------------------------------------------------------ bantu

    private function rules(int $id): array
    {
        return [
            'nama' => ['required', 'max:150'],
            'niy'  => ['nullable', 'max:30', "unique:guru,niy,$id,soft"],
            'nuptk' => ['nullable', 'max:30', "unique:guru,nuptk,$id,soft"],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir'  => ['nullable', 'max:80'],
            'tanggal_lahir' => ['nullable', 'date', static function ($v) {
                return $v >= date('Y-m-d') ? 'Tanggal lahir harus sebelum hari ini.' : null;
            }],
            'pendidikan_terakhir' => ['nullable', 'max:100'],
            'tmt'     => ['nullable', 'date'],
            'email'   => ['nullable', 'email', 'max:120'],
            'telepon' => ['nullable', 'max:40'],
            'alamat'  => ['nullable', 'max:500'],
            'status'  => ['required', 'in:aktif,nonaktif'],
        ];
    }

    /** Simpan foto yang diunggah ke storage privat. @return string|null nama file, null bila tidak ada unggahan */
    private function prosesFoto(string $nama): ?string
    {
        if (!ImageUpload::hasFile('foto')) {
            return null;
        }
        try {
            return ImageUpload::save('foto', Guru::dirFoto(), 'g', ['maxBytes' => 3 * 1024 * 1024, 'maxW' => 600, 'maxH' => 600]);
        } catch (RuntimeException $e) {
            $this->fail('Foto tidak dapat diproses.', ['foto' => $e->getMessage()]);
        }
        return null;
    }
}
