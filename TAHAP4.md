# SISLIA — Tahap 4

Paket pembaruan, bukan aplikasi mandiri. Memerlukan Tahap 1,2,3A-rev1,3B,3C,3D.
Tidak ada migrasi dan tidak ada impor ulang seed. Backup proyek/database dahulu.

## Pemasangan
1. Ekstrak isi folder sislia ke proyek. public/index.php diganti untuk menggabungkan routes_standar.php.
   Bila index.php sudah dimodifikasi, cukup tambahkan require routes_standar.php ke array_merge rute Anda.
2. Di config/menu.php: pada menu Standar Mutu hapus pasangan 'segera' => true.
   URL-nya tetap /standar-mutu. Alternatif: sisipkan require VIEW_PATH . '/partials/standar_nav.php' di sidebar.
3. Buka /standar-mutu. Pilih unit. Periode mengikuti topbar.
4. Jangan impor ulang schema/seed/dummy. Ini akan berisiko merusak data Anda.

## Implementasi dan batas
- Master standar dan butir dapat ditambah/diubah/dinonaktifkan Admin lewat modal.
- Tidak ada hapus fisik master: riwayat dipertahankan melalui status nonaktif.
- Isian pemenuhan per unit+semester. Skor internal opsional 0–100, belum rumus lisensi.
- Progres = jumlah indikator aktif terpenuhi / jumlah indikator aktif. Bukan nilai sertifikasi.
- Kepala sekolah/wakil/Admin mengisi sesuai izin; ketua bidang/Admin/kepsek memverifikasi.
- Perubahan isian atau penambahan/pengarsipan bukti membatalkan verifikasi terkait.
- Bukti PDF/JPG/PNG saja. Office belum didukung. Penyimpanan privat lokal; Nextcloud Tahap 7.
- Bukti diarsipkan (soft delete), berkas fisik tetap disimpan untuk audit.
- Download selalu attachment, lewat pemeriksaan unit, dengan nama aman.
- Daftar indikator menggunakan DataTables client-side; bukan server-side. Cocok untuk jumlah indikator terbatas.
- Master belum versioning: mengubah redaksi berdampak pada tampilan riwayat. Untuk revisi substansial,
  nonaktifkan butir lama lalu buat butir baru. Snapshot instrumen perlu tahap lanjutan sebelum produksi.
- Verifikasi adalah pencatatan akun pemeriksa, bukan tanda tangan elektronik tersertifikasi.
- Metadata verifikasi berkas (bukti_file.status_verifikasi) belum dikelola, hanya verifikasi isian.
- Semua perubahan menulis melalui POST dengan CSRF middleware Guard dari Tahap 2.

## Uji penerimaan
1. Admin membuat standar/indikator uji. Nonaktifkan, pastikan tidak muncul untuk kepsek.
2. Kepsek unit A mengisi unit A. Mengganti unit di URL/POST ke B harus ditolak.
3. Simpan status terpenuhi/skor/catatan. Ganti semester: isian harus berbeda.
4. Unggah PDF valid dan JPG. File PHP atau ekstensi palsu harus ditolak.
5. Verifikasi isian, ubah isian atau bukti: tanda verifikasi harus kosong lagi.
6. Kunci semester di Periode: isi/unggah/verifikasi/arsip bukti ditolak termasuk Admin;
   buka kunci secara eksplisit dahulu.
7. URL download bukti unit lain harus ditolak untuk akun kepsek/wakil.
8. Uji tabel di HP: indikator dan aksi tetap tampil, detail melalui tombol child row.

## Status pengujian
Kode dibuat berdasarkan kontrak kelas pada percakapan sebelumnya, bukan hasil membaca ZIP server.
Belum diuji dengan runtime PHP/MySQL/Apache atau browser. Periksa php -l pada file PHP,
kemudian uji di XAMPP sebelum dipakai di hosting produksi.
