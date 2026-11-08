<?php
/**
 * Admin > Pengaturan aplikasi (tabel `pengaturan`, pasangan kunci-nilai).
 */
class PengaturanController extends Controller
{
    private const EKSTENSI_BOLEH = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

    /** kunci => grup penyimpanan */
    private const GRUP = [
        'app_nama' => 'umum', 'app_kepanjangan' => 'umum', 'yayasan_nama' => 'umum', 'app_footer' => 'tampilan',
        'tema_default' => 'tampilan', 'password_min' => 'keamanan',
        'upload_maks_mb' => 'storage', 'upload_ekstensi' => 'storage', 'storage_driver' => 'storage',
    ];

    private const LABEL = [
        'app_nama' => 'Nama aplikasi', 'app_kepanjangan' => 'Kepanjangan', 'yayasan_nama' => 'Nama yayasan',
        'app_footer' => 'Teks footer', 'tema_default' => 'Tema bawaan', 'password_min' => 'Panjang minimal password',
        'upload_maks_mb' => 'Ukuran maksimal unggahan', 'upload_ekstensi' => 'Ekstensi file yang diizinkan',
        'storage_driver' => 'Penyimpanan bukti fisik',
    ];

    public function index(): void
    {
        $this->tampil([], []);
    }

    public function simpan(): void
    {
        $v = Validator::make($_POST, $this->rules(), self::LABEL);
        if ($v->fails()) {
            http_response_code(422);
            $this->tampil($_POST, $v->errors());
            return;
        }
        $d = $v->data();

        $lama = [];
        foreach (self::GRUP as $kunci => $grup) {
            $lama[$kunci] = setting($kunci, '');
            Setting::set($kunci, (string) ($d[$kunci] ?? ''), $grup);
        }
        audit('ubah', 'pengaturan', null, $lama, $d);

        $batasServer = $this->batasServerMb();
        if ($batasServer > 0 && (int) $d['upload_maks_mb'] > $batasServer) {
            flash('warning', "Pengaturan tersimpan, tetapi batas PHP server hanya {$batasServer} MB. Naikkan upload_max_filesize dan post_max_size di hosting agar berlaku.");
        } else {
            flash('success', 'Pengaturan disimpan.');
        }
        redirect('/admin/pengaturan');
    }

    private function tampil(array $old, array $errors): void
    {
        $this->render('admin/pengaturan/index', [
            'pageTitle' => 'Pengaturan Aplikasi',
            'pageCss'   => ['crud.css'],
            'old'       => $old,
            'errors'    => $errors,
            'server'    => $this->infoServer(),
        ]);
    }

    private function rules(): array
    {
        return [
            'app_nama'        => ['required', 'max:40'],
            'app_kepanjangan' => ['nullable', 'max:120'],
            'yayasan_nama'    => ['required', 'max:120'],
            'app_footer'      => ['nullable', 'max:150'],
            'tema_default'    => ['required', 'in:terang,gelap'],
            'password_min'    => ['required', 'integer', 'between:8,32'],
            'upload_maks_mb'  => ['required', 'integer', 'between:1,50'],
            'upload_ekstensi' => ['required', static function ($v) {
                $daftar = array_filter(array_map('trim', explode(',', strtolower($v))));
                if (!$daftar) {
                    return 'Isi minimal satu ekstensi.';
                }
                $ditolak = array_diff($daftar, self::EKSTENSI_BOLEH);
                return $ditolak ? 'Ekstensi tidak diizinkan: ' . implode(', ', $ditolak) . '. Boleh: ' . implode(', ', self::EKSTENSI_BOLEH) . '.' : null;
            }],
            'storage_driver'  => ['required', 'in:lokal'],
        ];
    }

    private function batasServerMb(): int
    {
        $toMb = static function (string $v): int {
            $n = (int) $v;
            $u = strtolower(substr(trim($v), -1));
            return $u === 'g' ? $n * 1024 : ($u === 'k' ? (int) ceil($n / 1024) : ($u === 'm' ? $n : (int) ceil($n / 1048576)));
        };
        return min($toMb((string) ini_get('upload_max_filesize')), $toMb((string) ini_get('post_max_size')));
    }

    private function infoServer(): array
    {
        $tulis = static function (string $d): bool {
            return is_dir($d) && is_writable($d);
        };
        return [
            'PHP'                  => PHP_VERSION,
            'upload_max_filesize'  => (string) ini_get('upload_max_filesize'),
            'post_max_size'        => (string) ini_get('post_max_size'),
            'Ekstensi GD'          => extension_loaded('gd') ? 'Aktif' : 'Tidak aktif',
            'Ekstensi cURL'        => extension_loaded('curl') ? 'Aktif' : 'Tidak aktif',
            'storage/ bisa ditulis' => $tulis(STORAGE_PATH) ? 'Ya' : 'Tidak',
            'HTTPS'                => Security::isHttps() ? 'Aktif' : 'Tidak aktif',
            'Mode'                 => APP_ENV . (APP_DEBUG ? ' (debug aktif)' : ''),
        ];
    }
}
