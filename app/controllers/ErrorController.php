<?php
class ErrorController extends Controller
{
    private const PESAN = [
        401 => ['Sesi berakhir', 'Silakan login kembali.'],
        403 => ['Akses ditolak', 'Anda tidak memiliki izin untuk membuka halaman ini.'],
        404 => ['Halaman tidak ditemukan', 'Alamat yang Anda tuju tidak tersedia.'],
        419 => ['Sesi formulir kedaluwarsa', 'Muat ulang halaman lalu coba lagi.'],
        500 => ['Terjadi kesalahan', 'Maaf, ada gangguan pada server. Coba lagi beberapa saat.'],
    ];

    public function show(int $code, string $message = ''): void
    {
        [$judul, $isi] = self::PESAN[$code] ?? ['Kesalahan', 'Permintaan tidak dapat diproses.'];
        $isi = $message !== '' ? $message : $isi;
        http_response_code($code);

        if ($this->isAjax()) {
            $this->json(['ok' => false, 'message' => $isi], $code);
        }
        $logged = false;
        try {
            $logged = Auth::check();
        } catch (Throwable $e) {
            $logged = false;
        }
        $this->render('errors/error', [
            'code' => $code, 'judul' => $judul, 'isi' => $isi, 'pageTitle' => $judul,
        ], $logged ? 'main' : 'auth');
    }
}
