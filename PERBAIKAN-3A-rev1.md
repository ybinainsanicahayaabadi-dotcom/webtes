# SISLIA - Perbaikan 3A (rev1)

1. Format tahun ajaran: 2026-2027 (bukan 2026/2027).
   - Jalankan database/migrations/2026_11_06_format_tahun_ajaran.sql sekali (ubah data yang sudah ada).
   - Instalasi baru: di database/seed.sql ganti '2026/2027' menjadi '2026-2027' sebelum diimpor.
2. Halaman login: tambah pilihan Tahun ajaran dan Semester (semester mengikuti tahun ajaran).
   Pilihan disimpan sebagai periode aktif sesi dan tetap bisa diganti dari bilah atas.

File yang diganti: app/controllers/{AuthController,PeriodeAdminController}.php,
views/pages/auth/login.php, views/pages/admin/periode/_fields_tahun.php,
public/assets/js/pages/login.js
