<?php
/**
 * Admin > Akun: lima peran, aktif/nonaktif, reset password, tanda tangan digital PNG.
 * Kepala sekolah, wakil kepala, dan guru wajib terhubung ke data guru; unit diambil dari penempatan tahun aktif.
 * Pengaman: admin tidak bisa menonaktifkan/menghapus/menurunkan dirinya, dan harus selalu ada minimal satu admin aktif.
 */
class AkunAdminController extends CrudController
{
    private const LABEL = [
        'username' => 'Username', 'nama_lengkap' => 'Nama lengkap', 'email' => 'Email',
        'role' => 'Peran', 'guru_id' => 'Data guru', 'password' => 'Password',
    ];

    public function index(): void
    {
        $this->render('admin/akun/index', [
            'pageTitle' => 'Akun Pengguna',
            'pageCss'   => ['crud.css'],
            'pageJs'    => ['crud.js', 'admin-akun.js'],
            'guruList'  => Guru::opsi(),
            'roleGuru'  => Akun::ROLE_GURU,
            'passwordMin' => max(8, (int) setting('password_min', PASSWORD_MIN)),
        ]);
    }

    public function data(): void
    {
        $where = 'u.deleted_at IS NULL';
        $params = [];
        $role = (string) ($_GET['role'] ?? '');
        if (in_array($role, Akun::ROLE, true)) {
            $where .= ' AND u.role = ?';
            $params[] = $role;
        }
        $status = (string) ($_GET['status'] ?? '');
        if ($status === 'aktif' || $status === 'nonaktif') {
            $where .= ' AND u.is_aktif = ?';
            $params[] = $status === 'aktif' ? 1 : 0;
        }

        DataTable::respond([
            'select' => 'u.id, u.username, u.nama_lengkap, u.email, u.role, u.is_aktif, u.last_login_at,
                         (u.tanda_tangan IS NOT NULL) AS punya_ttd, un.nama AS unit, g.nama AS guru',
            'from'   => 'users u LEFT JOIN unit_sekolah un ON un.id = u.unit_id LEFT JOIN guru g ON g.id = u.guru_id',
            'where'  => $where,
            'params' => $params,
            'columns' => [
                'nama_lengkap' => 'u.nama_lengkap', 'username' => 'u.username', 'role' => 'u.role',
                'unit' => 'un.nama', 'is_aktif' => 'u.is_aktif', 'last_login_at' => 'u.last_login_at',
            ],
            'searchable' => ['nama_lengkap', 'username', 'unit'],
            'order'      => 'u.nama_lengkap ASC',
        ]);
    }

    public function show(int $id): void
    {
        $this->ok('', ['data' => $this->found(Akun::find($id), 'Akun')]);
    }

    public function store(): void
    {
        $this->normalisasi();
        $d = $this->validate($this->rules(0), self::LABEL);
        $this->cekPeranGuru($d);
        $password = (string) ($_POST['password'] ?? '');
        if ($password === '') {
            $this->fail('Periksa kembali isian Anda.', ['password' => 'Password wajib diisi untuk akun baru.']);
        }
        $d['unit_id'] = $this->unitUntuk($d);

        $berkas = null;
        try {
            $id = Transaction::run(function () use ($d, $password, &$berkas) {
                $id = Akun::create($d, Auth::hashPassword($password));
                if (Signature::hasFile('ttd')) {
                    $berkas = Signature::save('ttd', $id);
                    Akun::setTtd($id, $berkas);
                }
                return $id;
            });
        } catch (InvalidArgumentException $e) {
            Signature::remove($berkas);
            $this->fail('Tanda tangan tidak dapat diproses.', ['ttd' => $e->getMessage()]);
        }
        audit('tambah', 'users', $id, null, $this->tanpaRahasia($d));
        $this->ok('Akun ditambahkan.');
    }

    public function update(int $id): void
    {
        $lama = $this->found(Akun::find($id), 'Akun');
        $this->normalisasi();
        $d = $this->validate($this->rules($id), self::LABEL);
        $this->cekPeranGuru($d);
        $this->cekPengaman($id, $lama, $d);
        $d['unit_id'] = $this->unitUntuk($d);

        $password = (string) ($_POST['password'] ?? '');
        $hash = $password !== '' ? Auth::hashPassword($password) : null;

        $berkasBaru = null;
        try {
            Transaction::run(function () use ($id, $d, $hash, $lama, &$berkasBaru) {
                Akun::update($id, $d, $hash);
                if (Signature::hasFile('ttd')) {
                    $berkasBaru = Signature::save('ttd', $id);
                    Akun::setTtd($id, $berkasBaru);
                } elseif (!empty($_POST['hapus_ttd'])) {
                    Akun::setTtd($id, null);
                }
            });
        } catch (InvalidArgumentException $e) {
            Signature::remove($berkasBaru);
            $this->fail('Tanda tangan tidak dapat diproses.', ['ttd' => $e->getMessage()]);
        }
        if (($berkasBaru !== null || !empty($_POST['hapus_ttd'])) && !empty($lama['tanda_tangan'])) {
            Signature::remove($lama['tanda_tangan']);
        }
        if ($hash !== null || (int) $d['is_aktif'] === 0) {
            Akun::cabutSesi($id);   // sandi diganti / akun dinonaktifkan: paksa login ulang
        }
        audit('ubah', 'users', $id, $this->tanpaRahasia($lama), $this->tanpaRahasia($d));
        $this->ok('Akun diperbarui.');
    }

    public function delete(int $id): void
    {
        $row = $this->found(Akun::find($id), 'Akun');
        if ($id === Auth::id()) {
            $this->fail('Anda tidak dapat menghapus akun yang sedang dipakai.', [], 409);
        }
        if ($row['role'] === 'admin' && (int) $row['is_aktif'] === 1 && Akun::jumlahAdminAktif($id) === 0) {
            $this->fail('Tidak dapat menghapus admin aktif terakhir.', [], 409);
        }
        Akun::hapusLunak($id);
        Signature::remove($row['tanda_tangan']);
        audit('hapus', 'users', $id, $this->tanpaRahasia($row), null);
        $this->ok('Akun dihapus.');
    }

    /** Buat password acak baru; ditampilkan sekali kepada admin, pengguna wajib menggantinya saat login */
    public function reset(int $id): void
    {
        $row = $this->found(Akun::find($id), 'Akun');
        $baru = Akun::passwordAcak(12);
        Akun::setPassword($id, Auth::hashPassword($baru), true);
        Akun::cabutSesi($id);
        audit('reset_password', 'users', $id);   // password tidak dicatat
        $this->ok('Password direset.', ['password' => $baru, 'username' => $row['username']]);
    }

    // ------------------------------------------------------------------ bantu

    private function normalisasi(): void
    {
        $_POST['username'] = strtolower(trim((string) ($_POST['username'] ?? '')));
    }

    private function rules(int $id): array
    {
        $min = max(8, (int) setting('password_min', PASSWORD_MIN));
        return [
            'username' => ['required', 'min:3', 'max:40', "unique:users,username,$id",
                static function ($v) {
                    return preg_match('/^[a-z0-9._-]+$/', $v) ? null : 'Username hanya huruf kecil, angka, titik, - dan _.';
                },
            ],
            'nama_lengkap' => ['required', 'max:150'],
            'email' => ['nullable', 'email', 'max:120'],
            'role' => ['required', 'in:' . implode(',', Akun::ROLE)],
            'guru_id' => ['nullable', 'integer', 'exists:guru,id',
                static function ($v) use ($id) {
                    $ada = (int) Database::value(
                        'SELECT COUNT(*) FROM users WHERE guru_id = ? AND id <> ? AND deleted_at IS NULL', [(int) $v, $id]
                    );
                    return $ada > 0 ? 'Data guru ini sudah memiliki akun.' : null;
                },
            ],
            'password' => ['nullable', static function ($v) use ($min) {
                if (mb_strlen($v) < $min) {
                    return "Password minimal $min karakter.";
                }
                return (preg_match('/[A-Za-z]/', $v) && preg_match('/\d/', $v)) ? null : 'Password harus mengandung huruf dan angka.';
            }],
            'must_change_pw' => ['boolean'],
            'is_aktif'       => ['boolean'],
        ];
    }

    /** Peran guru/kepsek/wakil wajib punya data guru; peran lain tidak boleh */
    private function cekPeranGuru(array &$d): void
    {
        if (in_array($d['role'], Akun::ROLE_GURU, true)) {
            if (empty($d['guru_id'])) {
                $this->fail('Periksa kembali isian Anda.', ['guru_id' => 'Pilih data guru untuk peran ini.']);
            }
        } else {
            $d['guru_id'] = null;
        }
    }

    private function unitUntuk(array $d): ?int
    {
        return !empty($d['guru_id']) ? Penempatan::unitAktif((int) $d['guru_id']) : null;
    }

    private function cekPengaman(int $id, array $lama, array $d): void
    {
        $diri = $id === Auth::id();
        if ($diri && ($d['role'] !== $lama['role'] || (int) $d['is_aktif'] === 0)) {
            $this->fail('Anda tidak dapat mengubah peran atau menonaktifkan akun Anda sendiri.', [
                'role' => 'Tidak dapat diubah untuk akun sendiri.',
            ], 409);
        }
        $adminAktif = $lama['role'] === 'admin' && (int) $lama['is_aktif'] === 1;
        if ($adminAktif && ($d['role'] !== 'admin' || (int) $d['is_aktif'] === 0) && Akun::jumlahAdminAktif($id) === 0) {
            $this->fail('Harus ada minimal satu admin aktif.', ['role' => 'Admin aktif terakhir tidak dapat diubah.'], 409);
        }
    }

    private function tanpaRahasia(array $d): array
    {
        unset($d['password'], $d['password_hash']);
        return $d;
    }
}
