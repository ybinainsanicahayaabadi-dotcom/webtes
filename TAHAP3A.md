# SISLIA - Tahap 3A (fondasi CRUD + Periode + Pengaturan)

Ekstrak di atas proyek Tahap 1+2 dan timpa file yang sama.

File lama yang DIGANTI (jika Anda sudah mengeditnya, gabungkan manual):
- public/index.php   -> hanya menggabungkan config/routes.php dengan config/routes_admin.php
- config/menu.php    -> menu Periode dan Pengaturan tidak lagi berlabel "Segera"

File baru: app/core/{Validator,DataTable,CrudController,Transaction,UI}.php,
app/models/{TahunAjaran,Semester}.php, app/controllers/{PeriodeAdminController,PengaturanController}.php,
config/routes_admin.php, views/partials/modal_form.php, views/pages/admin/*,
public/assets/js/pages/{crud,admin-periode}.js, public/assets/css/pages/crud.css

## Uji cepat
1. Login admin -> Administrasi > Periode.
2. Tambah tahun ajaran "2027/2028" (coba format salah: "2027-2028" harus ditolak).
3. Tambah semester Ganjil untuk 2027/2028, centang aktif -> tahun ajaran ikut aktif, semester lama nonaktif.
4. Kunci semester lama (ikon gembok); coba ubah/hapus -> ditolak. Coba hapus 2026/2027 -> ditolak (masih dipakai).
5. Layar HP: hanya kolom bertanda "all" + Aksi yang tampil; tekan + untuk detail.
6. Administrasi > Pengaturan: ubah nama aplikasi, simpan, cek judul tab dan sidebar.
