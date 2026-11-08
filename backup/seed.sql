-- SISLIA - database/seed.sql (data wajib/awal). Jalankan SETELAH schema.sql
SET NAMES utf8mb4;

INSERT INTO tahun_ajaran (id,nama,tgl_mulai,tgl_selesai,is_aktif) VALUES (1,'2026/2027','2026-07-13','2027-06-26',1);
INSERT INTO semester (id,tahun_ajaran_id,nama,tgl_mulai,tgl_selesai,is_aktif,is_terkunci) VALUES
(1,1,'Ganjil','2026-07-13','2026-12-19',1,0),
(2,1,'Genap','2027-01-04','2027-06-26',0,0);

INSERT INTO unit_sekolah (id,kode,nama,jenjang,logo,urutan,is_aktif,provinsi) VALUES
(1,'YBICA','Yayasan Bina Insani Cahaya Abadi','YAYASAN','assets/img/logo/yayasan.png',1,1,'Banten'),
(2,'TKIT1','TKIT Bina Insani','TK','assets/img/logo/tkit.png',2,1,'Banten'),
(3,'TKIT2','TKIT Bina Insani 2','TK','assets/img/logo/tkit2.png',3,1,'Banten'),
(4,'SDIT1','SDIT Bina Insani','SD','assets/img/logo/sdit.png',4,1,'Banten'),
(5,'SDIT2','SDIT Bina Insani 2','SD','assets/img/logo/sdit2.png',5,1,'Banten'),
(6,'SMPIT','SMPIT Al Masykar Bina Insani','SMP','assets/img/logo/smpit.png',6,1,'Banten'),
(7,'SMAIT','SMAIT Bina Insani','SMA','assets/img/logo/smait.png',7,1,'Banten');

INSERT INTO standar_mutu (id,kode,nama,urutan,is_aktif) VALUES
(1,'SKL','Standar Kompetensi Lulusan',1,1),
(2,'SI','Standar Isi',2,1),
(3,'SPS','Standar Pembinaan Siswa',3,1),
(4,'SP','Standar Proses',4,1),
(5,'SPN','Standar Penilaian',5,1),
(6,'SPTK','Standar Pendidik dan Tenaga Kependidikan',6,1),
(7,'SSP','Standar Sarana dan Prasarana',7,1),
(8,'SPG','Standar Pengelolaan',8,1),
(9,'SPB','Standar Pembiayaan',9,1);

INSERT INTO pengaturan (kunci,nilai,grup) VALUES
('app_nama','SISLIA','umum'),
('app_kepanjangan','Sistem Satuan Layanan Informasi Akademik','umum'),
('yayasan_nama','Yayasan Bina Insani Cahaya Abadi','umum'),
('app_footer','© Yayasan Bina Insani Cahaya Abadi','tampilan'),
('tema_default','terang','tampilan'),
('warna_utama','#4f46e5','tampilan'),
('timezone','Asia/Jakarta','umum'),
('sesi_hari','30','keamanan'),
('login_maks_gagal','5','keamanan'),
('login_kunci_menit','15','keamanan'),
('password_min','8','keamanan'),
('upload_maks_mb','5','storage'),
('upload_ekstensi','pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx','storage'),
('storage_driver','lokal','storage'),
('nextcloud_url','','storage'),
('nextcloud_user','','storage'),
('nextcloud_password','','storage'),
('nextcloud_folder','/SISLIA','storage'),
('kuesioner_kode_panjang','8','keamanan');

INSERT INTO kategori_capaian (modul,label,min_persen,max_persen,warna) VALUES
('pkks','Sangat Baik',91,100,'success'),
('pkks','Baik',81,90.99,'primary'),
('pkks','Cukup',71,80.99,'warning'),
('pkks','Kurang',0,70.99,'danger'),
('supervisi','Sangat Baik',91,100,'success'),
('supervisi','Baik',81,90.99,'primary'),
('supervisi','Cukup',71,80.99,'warning'),
('supervisi','Kurang',0,70.99,'danger'),
('standar','Sangat Baik',91,100,'success'),
('standar','Baik',81,90.99,'primary'),
('standar','Cukup',71,80.99,'warning'),
('standar','Kurang',0,70.99,'danger');

-- Catatan: akun Admin dibuat oleh public/install.php (password acak, ditampilkan sekali).
-- Instrumen PKKS/Supervisi/Kuesioner di-seed pada Tahap 5 dan 6.
