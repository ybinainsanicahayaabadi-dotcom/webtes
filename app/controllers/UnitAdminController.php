<?php
/**
 * Admin > Unit sekolah: profil sekolah + unggah logo (CRUD via modal).
 * Entri berjenjang YAYASAN dipakai untuk logo/profil yayasan (hanya satu).
 */
class UnitAdminController extends CrudController
{
    private const LOGO_DIR_REL = 'assets/img/logo/';

    private const LABEL = [
        'kode' => 'Kode unit', 'nama' => 'Nama unit', 'jenjang' => 'Jenjang', 'npsn' => 'NPSN',
        'alamat' => 'Alamat', 'kelurahan' => 'Kelurahan/Desa', 'kecamatan' => 'Kecamatan',
        'kab_kota' => 'Kabupaten/Kota', 'provinsi' => 'Provinsi', 'telepon' => 'Telepon',
        'email' => 'Email', 'website' => 'Website', 'urutan' => 'Urutan',
    ];

    public function index(): void
    {
        $this->render('admin/unit/index', [
            'pageTitle' => 'Unit Sekolah',
            'pageCss'   => ['crud.css'],
            'pageJs'    => ['crud.js', 'admin-unit.js'],
        ]);
    }

    public function data(): void
    {
        DataTable::respond([
            'select' => 'u.id, u.kode, u.nama, u.jenjang, u.logo, u.telepon, u.email, u.kab_kota, u.urutan, u.is_aktif',
            'from'   => 'unit_sekolah u',
            'where'  => 'u.deleted_at IS NULL',
            'columns' => [
                'nama' => 'u.nama', 'kode' => 'u.kode', 'jenjang' => 'u.jenjang', 'telepon' => 'u.telepon',
                'kab_kota' => 'u.kab_kota', 'urutan' => 'u.urutan', 'is_aktif' => 'u.is_aktif',
            ],
            'searchable' => ['nama', 'kode', 'jenjang', 'kab_kota'],
            'order'      => 'u.urutan ASC, u.id ASC',
        ]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Unit::find($id), 'Unit')]);
    }

    public function store(): void
    {
        $d = $this->validate($this->rules(0), self::LABEL);
        $d['logo'] = $this->prosesLogo($d['kode']);
        $id = Unit::create($d);
        audit('tambah', 'unit_sekolah', $id, null, $d);
        $this->ok('Unit sekolah ditambahkan.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(Unit::find($id), 'Unit');
        $d = $this->validate($this->rules($id), self::LABEL);

        $baru = $this->prosesLogo($d['kode']);
        if ($baru !== null) {
            $d['logo'] = $baru;
        } elseif (!empty($_POST['hapus_logo'])) {
            $d['logo'] = null;
        } else {
            $d['logo'] = $lama['logo'];
        }

        Unit::update($id, $d);
        if ($d['logo'] !== $lama['logo']) {
            $this->hapusFileLogo($lama['logo'], $id);
        }
        audit('ubah', 'unit_sekolah', $id, $lama, $d);
        $this->ok('Profil unit diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Unit::find($id), 'Unit');
        $dep = Unit::dependencies($id);
        if ($dep) {
            $out = [];
            foreach ($dep as $label => $n) {
                $out[] = "$label ($n)";
            }
            $this->fail('Tidak bisa dihapus, masih dipakai: ' . implode(', ', $out) . '. Nonaktifkan unit bila tidak dipakai lagi.', [], 409);
        }
        Unit::delete($id);
        $this->hapusFileLogo($row['logo'], $id);
        audit('hapus', 'unit_sekolah', $id, $row, null);
        $this->ok('Unit sekolah dihapus.');
    }

    // ------------------------------------------------------------------ bantu

    private function rules(int $id): array
    {
        return [
            'kode' => ['required', 'max:20', "unique:unit_sekolah,kode,$id",
                static function ($v) {
                    return preg_match('/^[A-Za-z0-9_-]+$/', $v) ? null : 'Kode hanya boleh huruf, angka, tanda - dan _.';
                },
            ],
            'nama' => ['required', 'max:120'],
            'jenjang' => ['required', 'in:YAYASAN,TK,SD,SMP,SMA',
                static function ($v) use ($id) {
                    if ($v === 'YAYASAN') {
                        $ada = (int) Database::value(
                            "SELECT COUNT(*) FROM unit_sekolah WHERE jenjang = 'YAYASAN' AND deleted_at IS NULL AND id <> ?", [$id]
                        );
                        if ($ada > 0) {
                            return 'Entri Yayasan sudah ada. Hanya boleh satu.';
                        }
                    }
                    if ($id > 0) {
                        $lama = Database::value('SELECT jenjang FROM unit_sekolah WHERE id = ?', [$id]);
                        $kelas = (int) Database::value('SELECT COUNT(*) FROM kelas WHERE unit_id = ?', [$id]);
                        if ($lama && $lama !== $v && $kelas > 0) {
                            return 'Jenjang tidak dapat diubah karena unit sudah memiliki kelas.';
                        }
                    }
                    return null;
                },
            ],
            'npsn'      => ['nullable', 'max:20'],
            'alamat'    => ['nullable', 'max:500'],
            'kelurahan' => ['nullable', 'max:80'],
            'kecamatan' => ['nullable', 'max:80'],
            'kab_kota'  => ['nullable', 'max:80'],
            'provinsi'  => ['nullable', 'max:80'],
            'telepon'   => ['nullable', 'max:40'],
            'email'     => ['nullable', 'email', 'max:120'],
            'website'   => ['nullable', 'max:150', static function ($v) {
                return filter_var($v, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $v) ? null : 'Website harus diawali http:// atau https://.';
            }],
            'urutan'    => ['nullable', 'integer', 'between:0,99'],
            'is_aktif'  => ['boolean'],
        ];
    }

    /** Simpan logo yang diunggah. @return string|null path relatif dari public/, null bila tidak ada unggahan */
    private function prosesLogo(string $kode): ?string
    {
        if (!ImageUpload::hasFile('logo')) {
            return null;
        }
        try {
            $nama = ImageUpload::save('logo', PUBLIC_PATH . '/' . self::LOGO_DIR_REL, 'unit-' . strtolower($kode), [
                'maxBytes' => 2 * 1024 * 1024, 'maxW' => 512, 'maxH' => 512,
            ]);
        } catch (RuntimeException $e) {
            $this->fail('Logo tidak dapat diproses.', ['logo' => $e->getMessage()]);
        }
        return self::LOGO_DIR_REL . $nama;
    }

    /** Hapus file logo jika tidak dipakai unit lain */
    private function hapusFileLogo(?string $path, int $kecualiId): void
    {
        if (!$path || strpos($path, self::LOGO_DIR_REL) !== 0) {
            return;
        }
        $dipakai = (int) Database::value('SELECT COUNT(*) FROM unit_sekolah WHERE logo = ? AND id <> ?', [$path, $kecualiId]);
        if ($dipakai === 0) {
            ImageUpload::remove(PUBLIC_PATH . '/' . self::LOGO_DIR_REL, basename($path));
        }
    }
}
