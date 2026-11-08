<?php
class ProfilController extends Controller
{
    private const TTD_MAKS_BYTE = 1048576;   // 1 MB

    public function index(): void
    {
        $this->render('profil/index', [
            'pageTitle' => 'Profil Saya',
            'pageJs'    => ['profil.js'],
            'wajibTtd'  => Auth::hasRole('kepala_sekolah', 'ketua_bidang', 'wakil_kepala'),
        ]);
    }

    public function simpan(): void
    {
        $nama = input('nama_lengkap');
        $email = input('email');

        if ($nama === '' || mb_strlen($nama) > 150) {
            flash('danger', 'Nama lengkap wajib diisi (maksimal 150 karakter).');
            redirect('/profil');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Format email tidak valid.');
            redirect('/profil');
        }
        User::updateProfil(Auth::id(), $nama, $email !== '' ? $email : null);
        audit('ubah', 'users', Auth::id());
        flash('success', 'Profil diperbarui.');
        redirect('/profil');
    }

    public function password(): void
    {
        $uid = Auth::id();
        $row = User::find($uid);
        $lama = (string) ($_POST['password_lama'] ?? '');
        $baru = (string) ($_POST['password_baru'] ?? '');
        $konf = (string) ($_POST['password_konfirmasi'] ?? '');
        $min = max(8, (int) setting('password_min', PASSWORD_MIN));

        $galat = '';
        if (!$row || !password_verify($lama, $row['password_hash'])) {
            $galat = 'Password lama salah.';
        } elseif (mb_strlen($baru) < $min) {
            $galat = "Password baru minimal $min karakter.";
        } elseif (!preg_match('/[A-Za-z]/', $baru) || !preg_match('/\d/', $baru)) {
            $galat = 'Password baru harus mengandung huruf dan angka.';
        } elseif ($baru !== $konf) {
            $galat = 'Konfirmasi password tidak sama.';
        } elseif ($baru === $lama) {
            $galat = 'Password baru tidak boleh sama dengan password lama.';
        }
        if ($galat !== '') {
            flash('danger', $galat);
            redirect('/profil');
        }

        User::updatePassword($uid, Auth::hashPassword($baru));
        session_regenerate_id(true);
        $cabut = Auth::logoutOthers($uid);   // perangkat lain wajib login ulang
        audit('ganti_password', 'users', $uid);
        flash('success', 'Password diganti.' . ($cabut > 0 ? " $cabut perangkat lain dikeluarkan." : ''));
        redirect('/dashboard');
    }

    public function unggahTtd(): void
    {
        $uid = Auth::id();
        $f = $_FILES['ttd'] ?? null;

        if (!$f || $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            flash('danger', 'Pilih file PNG tanda tangan terlebih dahulu.');
            redirect('/profil');
        }
        if ($f['size'] > self::TTD_MAKS_BYTE) {
            flash('danger', 'Ukuran file maksimal 1 MB.');
            redirect('/profil');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $info = @getimagesize($f['tmp_name']);
        if ($mime !== 'image/png' || $info === false || $info[2] !== IMAGETYPE_PNG) {
            flash('danger', 'File harus berformat PNG.');
            redirect('/profil');
        }
        [$w, $h] = $info;
        if ($w < 100 || $h < 50 || $w > 3000 || $h > 3000) {
            flash('danger', 'Dimensi gambar tidak sesuai (minimal 100x50 piksel, maksimal 3000x3000).');
            redirect('/profil');
        }
        if (!function_exists('imagecreatefrompng')) {
            flash('danger', 'Ekstensi GD belum aktif di server.');
            redirect('/profil');
        }

        // Re-encode: membuang data tersembunyi dan menjaga transparansi
        $src = imagecreatefrompng($f['tmp_name']);
        $maxW = 600;
        $nw = $w > $maxW ? $maxW : $w;
        $nh = (int) round($h * ($nw / $w));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $dir = STORAGE_PATH . '/uploads/ttd';
        if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
            flash('danger', 'Folder penyimpanan tidak bisa dibuat. Periksa izin folder storage.');
            redirect('/profil');
        }
        $nama = $uid . '_' . bin2hex(random_bytes(8)) . '.png';
        imagepng($dst, $dir . '/' . $nama, 6);
        imagedestroy($src);
        imagedestroy($dst);

        $lama = User::find($uid)['tanda_tangan'] ?? null;
        User::setTandaTangan($uid, 'ttd/' . $nama);
        if ($lama && is_file(STORAGE_PATH . '/uploads/' . $lama)) {
            @unlink(STORAGE_PATH . '/uploads/' . $lama);
        }
        audit('unggah_ttd', 'users', $uid);
        flash('success', 'Tanda tangan digital disimpan.');
        redirect('/profil');
    }

    public function hapusTtd(): void
    {
        $uid = Auth::id();
        $lama = User::find($uid)['tanda_tangan'] ?? null;
        User::setTandaTangan($uid, null);
        if ($lama && is_file(STORAGE_PATH . '/uploads/' . $lama)) {
            @unlink(STORAGE_PATH . '/uploads/' . $lama);
        }
        audit('hapus_ttd', 'users', $uid);
        flash('success', 'Tanda tangan dihapus.');
        redirect('/profil');
    }
}
