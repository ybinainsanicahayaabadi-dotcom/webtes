# SISLIA - Sistem Satuan Layanan Informasi Akademik
Yayasan Bina Insani Cahaya Abadi. PHP murni + MySQL.

## Instalasi lokal (XAMPP)
1. Salin folder ini ke `htdocs/sislia`.
2. Buat database `sislia` (phpMyAdmin) atau biarkan install.php membuatnya.
3. Edit `config/config.php` (DB_*, BASE_URL = http://localhost/sislia/public).
4. Buka `http://localhost/sislia/public/install.php`, centang opsi, klik Pasang.
5. Simpan password admin yang tampil SEKALI, lalu HAPUS `public/install.php`.

## Instalasi hosting (cPanel)
1. Idealnya arahkan document root domain ke folder `public/`. Jika tidak bisa, `.htaccess` di root meneruskan ke `public/`.
2. Buat database + user di cPanel, isi `config/config.php` (APP_ENV=production, APP_DEBUG=false, BASE_URL https).
3. Jalankan install.php sekali, matikan opsi data contoh, hapus install.php.
4. Pastikan folder `storage/` dapat ditulis (chmod 755/775) dan TIDAK dapat diakses web.

## File database
- `database/schema.sql` : 30 tabel (v2).
- `database/seed.sql`   : 7 unit, 9 standar, pengaturan, kategori capaian, 1 tahun ajaran + 2 semester.
- `database/dummy.sql`  : data contoh (30 guru, 22 kelas, 30 mapel, penugasan, butir standar contoh). Hanya untuk uji coba.

## Akun contoh (jika opsi data contoh dicentang)
Password: `Sislia#2026` | ketuabidang, kepsek.sdit1, wakasek.sdit1, guru{n}.sdit1 dst.
Admin: username `admin`, password acak dari install.php.
