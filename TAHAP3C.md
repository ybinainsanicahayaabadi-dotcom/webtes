# SISLIA - Tahap 3C (Guru + Penempatan + Akun)

Ekstrak di atas proyek (setelah 3B) dan timpa file yang sama.

File lama yang DIGANTI (gabungkan manual bila sudah Anda ubah):
- config/routes_admin.php    (rute guru, penempatan, akun, berkas/guru)
- config/menu.php            (Guru & Akun aktif)
- app/controllers/BerkasController.php  (ditambah penyajian foto guru)
- app/models/{TahunAjaran,Semester}.php (akun otomatis disinkronkan saat tahun/semester aktif berganti)
- public/assets/css/pages/crud.css

File baru: app/core/Signature.php, app/models/{Guru,Penempatan,Akun}.php,
app/controllers/{GuruAdminController,PenempatanAdminController,AkunAdminController}.php,
views/pages/admin/{guru,akun}/*, public/assets/js/pages/{admin-guru,admin-akun}.js

## Uji cepat
1. Guru > Tambah guru (unggah foto JPG dari HP): foto tampil di tabel. Coba NIY yang sama dua kali -> ditolak.
2. Tab Penempatan > tambah penempatan tahun aktif, unit SDIT 1, jabatan Kepala Sekolah. Coba tambah Kepala Sekolah kedua di unit yang sama -> ditolak.
3. Salin penempatan 2026-2027 -> 2027-2028; ulangi -> semua dilewati.
4. Akun > Tambah: peran Guru wajib pilih data guru; peran Admin tidak. Tombol "Acak" membuat password.
5. Login sebagai akun baru -> diminta ganti password. Admin klik ikon kunci (reset) -> password baru tampil sekali.
6. Coba nonaktifkan/hapus akun admin sendiri -> ditolak. Coba hapus admin aktif terakhir -> ditolak.
7. Unggah tanda tangan PNG di akun kepala sekolah, cek di Profil akun itu.
