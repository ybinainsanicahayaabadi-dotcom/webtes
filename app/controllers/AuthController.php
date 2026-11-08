<?php
class AuthController extends Controller
{
    public function home(): void
    {
        redirect(Auth::check() ? '/dashboard' : '/login');
    }

    public function showLogin(): void
    {
        $error = $_SESSION['login_error'] ?? '';
        $oldUser = $_SESSION['login_user'] ?? '';
        $pilihSem = (int) ($_SESSION['login_sem'] ?? 0);
        unset($_SESSION['login_error'], $_SESSION['login_user'], $_SESSION['login_ta'], $_SESSION['login_sem']);

        // Daftar periode untuk pilihan tahun ajaran + semester
        $semesters = Periode::semesters();   // urut: tahun ajaran terbaru dulu
        $tahunList = [];
        foreach ($semesters as $s) {
            $tahunList[(int) $s['tahun_id']] = $s['tahun'];
        }

        // Pilihan awal: input sebelumnya -> pilihan di sesi -> semester aktif -> yang pertama
        $ids = array_map('intval', array_column($semesters, 'id'));
        if (!in_array($pilihSem, $ids, true)) {
            $pilihSem = (int) ($_SESSION['semester_id'] ?? 0);
        }
        if (!in_array($pilihSem, $ids, true)) {
            $pilihSem = 0;
            foreach ($semesters as $s) {
                if ((int) $s['is_aktif'] === 1) {
                    $pilihSem = (int) $s['id'];
                    break;
                }
            }
        }
        if ($pilihSem === 0 && $semesters) {
            $pilihSem = (int) $semesters[0]['id'];
        }
        $pilihTa = 0;
        foreach ($semesters as $s) {
            if ((int) $s['id'] === $pilihSem) {
                $pilihTa = (int) $s['tahun_id'];
            }
        }

        $this->render('auth/login', [
            'pageTitle' => 'Masuk',
            'error'     => $error,
            'oldUser'   => $oldUser,
            'semesters' => $semesters,
            'tahunList' => $tahunList,
            'pilihTa'   => $pilihTa,
            'pilihSem'  => $pilihSem,
            'pageJs'    => ['login.js'],
        ], 'auth');
    }

    public function login(): void
    {
        $username = input('username');
        $password = (string) ($_POST['password'] ?? '');

        [$ok, $pesan] = Auth::attempt($username, $password);
        if (!$ok) {
            $_SESSION['login_error'] = $pesan;
            $_SESSION['login_user'] = $username;
            $_SESSION['login_ta'] = input('tahun_ajaran_id');
            $_SESSION['login_sem'] = input('semester_id');
            redirect('/login');
        }

        $this->simpanPeriode((int) input('tahun_ajaran_id', '0'), (int) input('semester_id', '0'));

        $intended = $_SESSION['intended'] ?? '/dashboard';
        unset($_SESSION['intended']);
        if (!is_string($intended) || $intended === '' || $intended[0] !== '/' || strpos($intended, '//') === 0) {
            $intended = '/dashboard';
        }
        redirect($intended);
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Anda telah keluar.');
        redirect('/login');
    }

    /** Simpan periode pilihan login ke sesi; jika tidak valid pakai semester aktif */
    private function simpanPeriode(int $tahunId, int $semesterId): void
    {
        try {
            $valid = $semesterId > 0 && $tahunId > 0 && (int) Database::value(
                'SELECT COUNT(*) FROM semester WHERE id = ? AND tahun_ajaran_id = ?',
                [$semesterId, $tahunId]
            ) > 0;
        } catch (Throwable $e) {
            $valid = false;
        }
        if ($valid) {
            $_SESSION['semester_id'] = $semesterId;
        } else {
            unset($_SESSION['semester_id']);
        }
    }
}
