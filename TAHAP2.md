# SISLIA - Tahap 2 (paket tambahan di atas Tahap 1)

Ekstrak ke folder proyek `sislia/` dan timpa file yang sama (public/index.php diganti).

## Langkah
1. `php tools/unduh_vendor.php` (di komputer lokal) -> library tersimpan di public/assets/vendor.
   Tanpa langkah ini halaman tetap jalan memakai CDN jsdelivr sebagai cadangan.
2. Pastikan Tahap 1 sudah di-install (storage/installed.lock ada) dan install.php sudah dihapus.
3. Buka BASE_URL, login sebagai admin, ganti password.

## Rute
/login /logout /dashboard /profil /perangkat /berkas/ttd/{id} /periode/pilih
Menu lain di sidebar bertanda "Segera" (Tahap 3 dst).
