<?php
/**
 * Penjaga rute: akses (guest/auth/role/can), paksa ganti password, dan verifikasi CSRF.
 *
 * Format akses di config/routes.php:
 *   'any'                       siapa saja
 *   'guest'                     hanya yang belum login
 *   'auth'                      harus login
 *   'role:admin,ketua_bidang'   harus punya salah satu peran
 *   'can:standar_mutu.isi'      harus punya izin modul.aksi (lihat config/permissions.php)
 */
final class Guard
{
    /** Rute yang tetap boleh diakses saat wajib ganti password */
    private const BOLEH_SAAT_GANTI_PW = ['/profil', '/profil/password', '/logout'];

    public static function handle(string $access, string $path, string $method): void
    {
        if ($access !== 'any') {
            self::cekAkses($access, $path);
        }
        if (!in_array($method, ['GET', 'HEAD'], true) && !Csrf::verify()) {
            Router::abort(419, 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        }
    }

    private static function cekAkses(string $access, string $path): void
    {
        $user = Auth::user();

        if ($access === 'guest') {
            if ($user) {
                redirect('/dashboard');
            }
            return;
        }

        if (!$user) {
            if (Router::isAjax()) {
                Router::abort(401, 'Sesi berakhir. Silakan login kembali.');
            }
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                $_SESSION['intended'] = $path;
            }
            redirect('/login');
        }

        if ((int) $user['must_change_pw'] === 1 && !in_array($path, self::BOLEH_SAAT_GANTI_PW, true)) {
            flash('warning', 'Demi keamanan, ganti password Anda terlebih dahulu.');
            redirect('/profil');
        }

        if (strpos($access, 'role:') === 0) {
            $roles = explode(',', substr($access, 5));
            if (!in_array($user['role'], $roles, true)) {
                Router::abort(403);
            }
        } elseif (strpos($access, 'can:') === 0) {
            [$modul, $aksi] = array_pad(explode('.', substr($access, 4), 2), 2, 'lihat');
            if (!Rbac::can($modul, $aksi)) {
                Router::abort(403);
            }
        }
    }
}
