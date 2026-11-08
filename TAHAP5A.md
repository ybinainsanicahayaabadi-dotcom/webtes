# SISLIA — Tahap 5A

Memerlukan seluruh tahap hingga Tahap 4. Backup proyek + database dahulu.
Paket ini hanya master dan mesin hitung. Jadwal/form hasil: 5B; persetujuan/cetak: 5C.

## Pasang
1. Ekstrak isi folder sislia ke proyek. File lama yang diganti hanya public/index.php.
   Bila index.php sudah dimodifikasi, cukup tambahkan require config/routes_supervisi_master.php
   pada array_merge rute Anda, jangan menimpa seluruh file.
2. Di phpMyAdmin pilih database SISLIA lalu impor database/seed_supervisi_5a.sql.
   Jangan impor ulang schema.sql. Seed aman diulang untuk kode instrumen yang sudah lengkap:
   seluruh kode yang sudah ada dilewati, bukan ditimpa. Bila kode sama terlanjur ada tetapi
   tidak lengkap, seed tidak memperbaikinya; periksa master atau pulihkan backup.
3. Login Admin, buka /admin/instrumen-supervisi.
4. Tambahkan ke grup Administrasi di config/menu.php bila ingin tautan sidebar:
   ['label'=>'Master Supervisi','ikon'=>'fa-clipboard-check',
    'url'=>'/admin/instrumen-supervisi','izin'=>['pengaturan','lihat']],
   Jangan aktifkan menu /supervisi dahulu; itu modul pengguna Tahap 5B.
5. Tinjau semua indikator, set opsi, dan kategori. Instrumen seed NONAKTIF.
   Setelah disetujui, gunakan Ubah instrumen dan centang Aktif.

## Isi seed
SUP-ATP-V1: 12 indikator, maksimal 24, perencanaan.
SUP-ADM-V1: 12 indikator, maksimal 24, perencanaan.
SUP-MOD-V1: 24 nomor utama, maksimal 48, perencanaan.
SUP-PEL-V1: 38 indikator, maksimal 76, pelaksanaan.
SUP-ASES-V1: 7 indikator, maksimal 14, asesmen turunan.
Total 93 indikator. Asesmen turunan HARUS dilaporkan terpisah agar tidak dihitung dua kali.
D pada Modul Ajar hanya judul kelompok tanpa indikator bernomor/skor.
Subbutir nomor 13 dan 14 dipadukan ke deskripsi satu nomor utama.

## Penting tentang redaksi
Indikator memakai rumusan operasional ringkas, bukan salinan verbatim PDF.
Struktur, jumlah butir, dan lingkup dipetakan dari dokumen pengguna.
Periksa kembali redaksi dan makna sebelum aktivasi; jangan menganggap paket ini salinan persis PDF.
Skala 0=tidak sesuai/tidak tersedia, 1=sebagian, 2=sesuai/lengkap diterapkan sesuai persetujuan.
Opsi 0 pada administrasi/telaah modul mempertahankan arah skor asli; ATP/pelaksanaan dibalik.

## Mesin hitung
SupervisiScore::calculate($instrumentId, $answers)
$answers = [idButir => idOpsi]; bukan skor angka dari browser.
Mengembalikan total, maksimal, jumlah terisi, lengkap, persen, kategori, dan detail.
Penyebut mencakup SEMUA butir aktif, tidak hanya butir yang dijawab.
Jawaban kosong bukan nol: nilai akhir/kategori null bila belum lengkap.
Persen dibulatkan dua desimal SEBELUM kategori.
Kategori memakai ambang bawah inklusif terbesar (91/81/71/0); tanpa celah pecahan.
Butir dari instrumen lain / opsi dari set lain ditolak.

## Perlindungan riwayat
Instrumen dengan jadwal atau penilaian dikunci dari perubahan master.
Kategori global terkunci setelah ada penilaian supervisi.
Untuk revisi, buat instrumen baru dengan kode versi berbeda (belum ada tombol kloning).
Kunci di sini bukan pengganti snapshot hasil yang akan dipakai Tahap 5B.
Butir/kelompok/opsi dapat dihapus hanya ketika induk belum digunakan.
Instrumen induk tidak dihapus; nonaktifkan sebelum digunakan jika perlu.
Semua rute master hanya Admin, POST melalui middleware CSRF yang sudah ada.
Tabel master memakai DataTables client-side; bukan server-side. CRUD memakai modal,
submit POST lalu redirect; error tampil melalui halaman error 422, bukan AJAX per kolom.
Kelompok dikelola satu tingkat pada UI ini, parent_id belum dikelola.

## Uji penerimaan
- Impor seed dua kali: jumlah instrumen/butir tidak bertambah pada impor kedua.
- Untuk setiap instrumen, klik Uji hitung (JSON): semua 0 -> 0/Kurang;
  semua 1 -> 50/Kurang; semua 2 -> 100/Sangat Baik; kosong -> nilai null.
- ATP/ADM maksimal 24; MOD 48; PEL 76; ASES 14.
- Tambah/ubah/hapus kelompok kosong, opsi, butir pada instrumen uji yang belum digunakan.
- Login guru/kepsek: URL master ditolak 403.
- Uji tanpa CSRF: POST ditolak 419.

## Status
Tidak tersedia runtime PHP/MySQL/browser dalam lingkungan pembuatan.
Belum diuji import atau fungsi end-to-end. Uji dengan php -l dan XAMPP sebelum hosting.
Tidak ada jaminan keamanan produksi tanpa audit lanjutan. SQL DML seed dibungkus transaksi.
