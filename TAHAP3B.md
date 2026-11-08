# SISLIA - Tahap 3B (Unit Sekolah + Kelas)

Ekstrak di atas proyek (setelah 3A + rev1) dan timpa file yang sama.

File lama yang DIGANTI (gabungkan manual bila sudah Anda ubah):
- config/routes_admin.php  (menambah rute unit & kelas)
- config/menu.php          (Unit Sekolah dan Kelas aktif)
- public/assets/js/pages/crud.js   (tambahan opsi ajaxData, onFill, onReset)
- public/assets/css/pages/crud.css (gaya logo & filter)

File baru: app/core/ImageUpload.php, app/models/{Unit,Kelas}.php,
app/controllers/{UnitAdminController,KelasAdminController}.php, views/pages/admin/{unit,kelas}/*,
public/assets/js/pages/{admin-unit,admin-kelas}.js

## Uji cepat
1. Administrasi > Unit Sekolah: ubah SDIT Bina Insani, unggah logo baru (JPG/PNG), simpan; logo di tabel & sidebar/login ikut berubah (logo yayasan).
2. Coba unggah file .pdf atau gambar > 2 MB -> ditolak. Coba website tanpa https:// -> ditolak.
3. Coba hapus unit yang punya kelas -> ditolak dengan rincian. Coba jadikan unit kedua berjenjang YAYASAN -> ditolak.
4. Administrasi > Kelas: tambah kelas; pilih unit SD -> tingkat hanya 1-6; wali kelas terisi guru unit tsb (butuh data contoh/penempatan).
5. Tombol "Salin dari tahun lain": salin kelas 2026-2027 ke 2027-2028; ulangi -> semua dilewati.
