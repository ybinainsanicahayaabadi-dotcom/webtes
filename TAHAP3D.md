# SISLIA - Tahap 3D (Mapel, Mapel per Semester, Penugasan Mengajar)

Ekstrak di atas proyek (setelah 3C) dan timpa file yang sama.

File lama yang DIGANTI (gabungkan manual bila sudah Anda ubah):
- config/routes_admin.php  (rute mapel, mapel-semester, penugasan)
- config/menu.php          (menu "Mapel & Penugasan" aktif)
- public/assets/js/pages/crud.js  (galat untuk field bernama "x[]")

File baru: app/models/{Mapel,MapelSemester,Penugasan}.php,
app/controllers/{MapelAdminController,MapelSemesterAdminController,PenugasanAdminController}.php,
views/pages/admin/mapel/*, public/assets/js/pages/{admin-mapel,admin-penugasan}.js

## Uji cepat
1. Mapel & Penugasan > Katalog: tambah mapel di SDIT 1 (nama sama dua kali -> ditolak).
2. Tab Mapel per Semester: buka mapel itu pada semester Ganjil (tingkat dikosongkan = semua; coba tingkat 9 untuk SD -> ditolak).
3. Tab Penugasan: pilih semester+unit -> guru/kelas/mapel terisi sesuai unit. Pilih 2 kelas sekaligus -> 2 penugasan.
4. Tugaskan guru lain ke kelas+mapel yang sama -> ditolak (sudah ada pengajar).
5. Kunci semester di menu Periode -> semua tambah/ubah/hapus pada 3 tab ditolak.
6. Salin dari semester Ganjil ke Genap (centang salin penugasan) -> laporan jumlah disalin/dilewati.
