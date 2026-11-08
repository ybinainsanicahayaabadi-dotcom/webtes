<?php
/**
 * Penyimpanan tanda tangan digital (PNG) di storage/uploads/ttd (privat, disajikan lewat /berkas/ttd/{id}).
 * Gambar diproses ulang dengan GD: transparansi dipertahankan, lebar maksimal 600 px, metadata dibuang.
 */
final class Signature
{
    private const MAKS_BYTE = 1048576;   // 1 MB

    public static function dir(): string
    {
        return STORAGE_PATH . '/uploads/ttd';
    }

    public static function hasFile(string $field): bool
    {
        return isset($_FILES[$field]) && (int) $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @return string path relatif ke storage/uploads (mis. "ttd/12_ab12cd34.png"), disimpan di users.tanda_tangan
     * @throws InvalidArgumentException pesan aman ditampilkan ke pengguna
     */
    public static function save(string $field, int $userId): string
    {
        $f = $_FILES[$field] ?? null;
        if (!$f || (int) $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            throw new InvalidArgumentException('File tanda tangan tidak terunggah. Periksa ukuran file.');
        }
        if ($f['size'] > self::MAKS_BYTE) {
            throw new InvalidArgumentException('Ukuran file tanda tangan maksimal 1 MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $info = @getimagesize($f['tmp_name']);
        if ($mime !== 'image/png' || $info === false || $info[2] !== IMAGETYPE_PNG) {
            throw new InvalidArgumentException('Tanda tangan harus berformat PNG.');
        }
        [$w, $h] = $info;
        if ($w < 100 || $h < 50 || $w > 3000 || $h > 3000) {
            throw new InvalidArgumentException('Dimensi tanda tangan tidak sesuai (minimal 100x50, maksimal 3000x3000 piksel).');
        }
        if (!function_exists('imagecreatefrompng')) {
            throw new InvalidArgumentException('Ekstensi GD belum aktif di server.');
        }

        $src = @imagecreatefrompng($f['tmp_name']);
        if (!$src) {
            throw new InvalidArgumentException('File PNG tidak dapat diproses.');
        }
        $nw = $w > 600 ? 600 : $w;
        $nh = (int) round($h * ($nw / $w));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $dir = self::dir();
        if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
            throw new InvalidArgumentException('Folder penyimpanan tidak bisa dibuat. Periksa izin folder storage.');
        }
        $nama = $userId . '_' . bin2hex(random_bytes(8)) . '.png';
        $ok = imagepng($dst, $dir . '/' . $nama, 6);
        imagedestroy($src);
        imagedestroy($dst);
        if (!$ok) {
            throw new InvalidArgumentException('Gagal menyimpan tanda tangan.');
        }
        return 'ttd/' . $nama;
    }

    public static function remove(?string $rel): void
    {
        if (!$rel || strpos($rel, 'ttd/') !== 0) {
            return;
        }
        $base = realpath(self::dir());
        $file = realpath(STORAGE_PATH . '/uploads/' . $rel);
        if ($base && $file && strpos($file, $base) === 0 && is_file($file)) {
            @unlink($file);
        }
    }
}
