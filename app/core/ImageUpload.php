<?php
/**
 * Unggah gambar yang aman: cek MIME asli, batas ukuran/dimensi, perbaiki orientasi EXIF,
 * perkecil, lalu simpan ulang dengan GD (membuang metadata/data tersembunyi).
 *
 *   $nama = ImageUpload::save('logo', PUBLIC_PATH . '/assets/img/logo', 'unit-sdit1', ['maxW' => 512, 'maxH' => 512]);
 *   ImageUpload::remove($dir, $namaLama);
 */
final class ImageUpload
{
    /** MIME yang diterima => ekstensi keluaran (WebP disimpan sebagai PNG agar kompatibel luas) */
    private const TIPE = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'png'];

    public static function hasFile(string $field): bool
    {
        return isset($_FILES[$field]) && (int) $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param array $opt maxBytes (bawaan 2 MB), maxW, maxH (bawaan 512)
     * @return string nama file yang tersimpan (tanpa folder)
     * @throws RuntimeException dengan pesan yang aman ditampilkan ke pengguna
     */
    public static function save(string $field, string $dir, string $prefix, array $opt = []): string
    {
        $maxBytes = (int) ($opt['maxBytes'] ?? 2 * 1024 * 1024);
        $maxW = (int) ($opt['maxW'] ?? 512);
        $maxH = (int) ($opt['maxH'] ?? 512);

        $f = $_FILES[$field] ?? null;
        $err = $f['error'] ?? UPLOAD_ERR_NO_FILE;
        if (!$f || $err !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::pesanUpload((int) $err));
        }
        if (!is_uploaded_file($f['tmp_name'])) {
            throw new RuntimeException('Berkas unggahan tidak valid.');
        }
        if ($f['size'] > $maxBytes) {
            throw new RuntimeException('Ukuran file maksimal ' . round($maxBytes / 1048576, 1) . ' MB.');
        }
        if (!extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi GD belum aktif di server.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset(self::TIPE[$mime])) {
            throw new RuntimeException('Format gambar harus JPG, PNG, atau WebP.');
        }
        $info = @getimagesize($f['tmp_name']);
        if ($info === false) {
            throw new RuntimeException('File bukan gambar yang valid.');
        }
        [$w, $h] = $info;
        if ($w < 32 || $h < 32 || $w > 6000 || $h > 6000) {
            throw new RuntimeException('Dimensi gambar tidak sesuai (minimal 32x32, maksimal 6000x6000 piksel).');
        }

        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($f['tmp_name']);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($f['tmp_name']);
        } else {
            if (!function_exists('imagecreatefromwebp')) {
                throw new RuntimeException('Server belum mendukung WebP. Gunakan JPG atau PNG.');
            }
            $src = @imagecreatefromwebp($f['tmp_name']);
        }
        if (!$src) {
            throw new RuntimeException('Gambar tidak dapat diproses.');
        }

        // Putar sesuai orientasi kamera (foto dari HP)
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($f['tmp_name']);
            $o = (int) ($exif['Orientation'] ?? 1);
            $sudut = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($sudut !== 0) {
                $rot = imagerotate($src, $sudut, 0);
                if ($rot) {
                    imagedestroy($src);
                    $src = $rot;
                }
            }
        }
        $w = imagesx($src);
        $h = imagesy($src);

        $rasio = min($maxW / $w, $maxH / $h, 1);
        $nw = max(1, (int) round($w * $rasio));
        $nh = max(1, (int) round($h * $rasio));
        $dst = imagecreatetruecolor($nw, $nh);

        $ext = self::TIPE[$mime];
        if ($ext === 'png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        } else {
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            throw new RuntimeException('Folder penyimpanan tidak bisa dibuat. Periksa izin folder.');
        }
        $nama = preg_replace('/[^a-z0-9_-]/i', '', $prefix) . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $path = rtrim($dir, '/\\') . '/' . $nama;
        $ok = $ext === 'png' ? imagepng($dst, $path, 6) : imagejpeg($dst, $path, 85);
        imagedestroy($src);
        imagedestroy($dst);
        if (!$ok) {
            throw new RuntimeException('Gagal menyimpan gambar. Periksa izin folder.');
        }
        @chmod($path, 0644);
        return $nama;
    }

    /** Hapus file hanya jika benar-benar berada di dalam $dir */
    public static function remove(string $dir, ?string $nama): void
    {
        if (!$nama) {
            return;
        }
        $base = realpath($dir);
        $file = realpath(rtrim($dir, '/\\') . '/' . basename($nama));
        if ($base && $file && strpos($file, $base) === 0 && is_file($file)) {
            @unlink($file);
        }
    }

    private static function pesanUpload(int $kode): string
    {
        switch ($kode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File melebihi batas ukuran yang diizinkan server.';
            case UPLOAD_ERR_PARTIAL:
                return 'File terunggah sebagian. Coba lagi.';
            case UPLOAD_ERR_NO_FILE:
                return 'Pilih file terlebih dahulu.';
            default:
                return 'Unggahan gagal. Coba lagi.';
        }
    }
}
