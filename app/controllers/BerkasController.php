<?php
/**
 * Penyajian berkas privat dari storage/ dengan pengecekan hak akses.
 */
class BerkasController extends Controller
{
    /** Tanda tangan digital milik user {id} */
    public function ttd(int $id): void
    {
        $boleh = Auth::id() === $id || Auth::hasRole('admin', 'ketua_bidang');
        if (!$boleh) {
            Router::abort(403);
        }
        $row = User::find($id);
        if (!$row || empty($row['tanda_tangan'])) {
            Router::abort(404);
        }
        $this->kirim(STORAGE_PATH . '/uploads', $row['tanda_tangan']);
    }

    /**
     * Foto guru {id}. Boleh: admin, ketua bidang, guru itu sendiri, serta kepala sekolah/wakil
     * pada unit tempat guru itu pernah/sedang ditempatkan.
     */
    public function guru(int $id): void
    {
        $u = Auth::user();
        $boleh = Auth::hasRole('admin', 'ketua_bidang') || (int) ($u['guru_id'] ?? 0) === $id;
        if (!$boleh && Auth::hasRole('kepala_sekolah', 'wakil_kepala') && !empty($u['unit_id'])) {
            $boleh = (int) Database::value(
                'SELECT COUNT(*) FROM guru_penempatan WHERE guru_id = ? AND unit_id = ?', [$id, (int) $u['unit_id']]
            ) > 0;
        }
        if (!$boleh) {
            Router::abort(403);
        }
        $g = Guru::find($id);
        if (!$g || empty($g['foto'])) {
            Router::abort(404);
        }
        $this->kirim(Guru::dirFoto(), $g['foto']);
    }

    /** Kirim file gambar bila benar-benar berada di dalam folder dasar */
    private function kirim(string $baseDir, string $relatif): void
    {
        $base = realpath($baseDir);
        $file = realpath($baseDir . '/' . $relatif);
        if (!$base || !$file || strpos($file, $base) !== 0 || !is_file($file)) {
            Router::abort(404);
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file);
        if (strpos((string) $mime, 'image/') !== 0) {
            Router::abort(404);
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=300');
        readfile($file);
        exit;
    }
}
