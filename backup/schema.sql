-- =====================================================
-- SISLIA - database/schema.sql (v2)
-- MySQL 5.7+ / MariaDB 10.3+, InnoDB, utf8mb4
-- =====================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE pengaturan (
  kunci VARCHAR(80) PRIMARY KEY,
  nilai TEXT NULL,
  grup VARCHAR(40) NOT NULL DEFAULT 'umum',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tahun_ajaran (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(20) NOT NULL,
  tgl_mulai DATE NULL,
  tgl_selesai DATE NULL,
  is_aktif TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ta_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE semester (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tahun_ajaran_id INT UNSIGNED NOT NULL,
  nama ENUM('Ganjil','Genap') NOT NULL,
  tgl_mulai DATE NULL,
  tgl_selesai DATE NULL,
  is_aktif TINYINT(1) NOT NULL DEFAULT 0,
  is_terkunci TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_sem (tahun_ajaran_id, nama),
  FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE unit_sekolah (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL,
  nama VARCHAR(120) NOT NULL,
  jenjang ENUM('YAYASAN','TK','SD','SMP','SMA') NOT NULL,
  npsn VARCHAR(20) NULL,
  alamat TEXT NULL,
  kelurahan VARCHAR(80) NULL,
  kecamatan VARCHAR(80) NULL,
  kab_kota VARCHAR(80) NULL,
  provinsi VARCHAR(80) NULL,
  telepon VARCHAR(40) NULL,
  email VARCHAR(120) NULL,
  website VARCHAR(150) NULL,
  logo VARCHAR(255) NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_unit_kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE guru (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  niy VARCHAR(30) NULL,
  nuptk VARCHAR(30) NULL,
  nama VARCHAR(150) NOT NULL,
  jenis_kelamin ENUM('L','P') NULL,
  tempat_lahir VARCHAR(80) NULL,
  tanggal_lahir DATE NULL,
  pendidikan_terakhir VARCHAR(100) NULL,
  tmt DATE NULL,
  email VARCHAR(120) NULL,
  telepon VARCHAR(40) NULL,
  alamat TEXT NULL,
  foto VARCHAR(255) NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  KEY idx_guru_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE guru_penempatan (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guru_id INT UNSIGNED NOT NULL,
  tahun_ajaran_id INT UNSIGNED NOT NULL,
  unit_id INT UNSIGNED NOT NULL,
  jabatan VARCHAR(100) NULL,
  pangkat_golongan VARCHAR(80) NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  UNIQUE KEY uq_gp (guru_id, tahun_ajaran_id),
  KEY idx_gp_unit (unit_id, tahun_ajaran_id),
  FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
  FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kelas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  unit_id INT UNSIGNED NOT NULL,
  tahun_ajaran_id INT UNSIGNED NOT NULL,
  tingkat VARCHAR(10) NOT NULL,
  nama VARCHAR(50) NOT NULL,
  wali_guru_id INT UNSIGNED NULL,
  jumlah_siswa SMALLINT NULL,
  deleted_at DATETIME NULL,
  KEY idx_kelas (unit_id, tahun_ajaran_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id),
  FOREIGN KEY (wali_guru_id) REFERENCES guru(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mapel (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  unit_id INT UNSIGNED NOT NULL,
  kode VARCHAR(20) NULL,
  nama VARCHAR(120) NOT NULL,
  deleted_at DATETIME NULL,
  KEY idx_mapel_unit (unit_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mapel_semester (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mapel_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  tingkat VARCHAR(10) NOT NULL DEFAULT '',
  jam_per_minggu TINYINT NULL,
  UNIQUE KEY uq_ms (mapel_id, semester_id, tingkat),
  FOREIGN KEY (mapel_id) REFERENCES mapel(id) ON DELETE CASCADE,
  FOREIGN KEY (semester_id) REFERENCES semester(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE penugasan_guru (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guru_id INT UNSIGNED NOT NULL,
  kelas_id INT UNSIGNED NOT NULL,
  mapel_semester_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_pg (guru_id, kelas_id, mapel_semester_id),
  FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  FOREIGN KEY (mapel_semester_id) REFERENCES mapel_semester(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL,
  email VARCHAR(120) NULL,
  password_hash VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(150) NOT NULL,
  role ENUM('admin','ketua_bidang','kepala_sekolah','wakil_kepala','guru') NOT NULL,
  unit_id INT UNSIGNED NULL,
  guru_id INT UNSIGNED NULL,
  tanda_tangan VARCHAR(255) NULL,
  must_change_pw TINYINT(1) NOT NULL DEFAULT 1,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_user (username),
  KEY idx_user_role (role, unit_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id) ON DELETE SET NULL,
  FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_token (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  selector CHAR(24) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  user_agent VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL,
  UNIQUE KEY uq_selector (selector),
  KEY idx_token_exp (expires_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE login_attempt (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NULL,
  ip_address VARCHAR(45) NOT NULL,
  berhasil TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_la (ip_address, created_at),
  KEY idx_la_user (username, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE standar_mutu (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL,
  nama VARCHAR(150) NOT NULL,
  deskripsi TEXT NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_sm_kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE standar_butir (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  standar_id INT UNSIGNED NOT NULL,
  nomor VARCHAR(20) NULL,
  indikator TEXT NOT NULL,
  bukti_dibutuhkan TEXT NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_sb (standar_id),
  FOREIGN KEY (standar_id) REFERENCES standar_mutu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE standar_penilaian (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  unit_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  butir_id INT UNSIGNED NOT NULL,
  status ENUM('belum','sebagian','terpenuhi','tidak_terpenuhi') NOT NULL DEFAULT 'belum',
  skor DECIMAL(5,2) NULL,
  catatan TEXT NULL,
  diisi_oleh INT UNSIGNED NULL,
  diverifikasi_oleh INT UNSIGNED NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sp (unit_id, semester_id, butir_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (semester_id) REFERENCES semester(id),
  FOREIGN KEY (butir_id) REFERENCES standar_butir(id) ON DELETE CASCADE,
  FOREIGN KEY (diisi_oleh) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instrumen (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  modul ENUM('pkks','supervisi','kuesioner_ortu','kuesioner_guru') NOT NULL,
  tahap ENUM('perencanaan','pelaksanaan','asesmen') NULL,
  kode VARCHAR(40) NOT NULL,
  nama VARCHAR(200) NOT NULL,
  skor_maks_butir DECIMAL(5,2) NULL,
  rumus ENUM('persen_maks','rerata_skor') NOT NULL DEFAULT 'persen_maks',
  catatan_form TEXT NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_ins_kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instrumen_kelompok (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrumen_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NULL,
  kode VARCHAR(20) NULL,
  nama VARCHAR(255) NOT NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  KEY idx_ik (instrumen_id),
  FOREIGN KEY (instrumen_id) REFERENCES instrumen(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES instrumen_kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instrumen_butir (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrumen_id INT UNSIGNED NOT NULL,
  kelompok_id INT UNSIGNED NULL,
  nomor VARCHAR(20) NULL,
  pernyataan TEXT NOT NULL,
  butuh_bukti TINYINT(1) NOT NULL DEFAULT 0,
  opsi_set VARCHAR(40) NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_ib (instrumen_id, kelompok_id),
  FOREIGN KEY (instrumen_id) REFERENCES instrumen(id) ON DELETE CASCADE,
  FOREIGN KEY (kelompok_id) REFERENCES instrumen_kelompok(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instrumen_opsi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrumen_id INT UNSIGNED NOT NULL,
  opsi_set VARCHAR(40) NOT NULL DEFAULT 'default',
  label VARCHAR(60) NOT NULL,
  bobot DECIMAL(5,2) NULL,
  skor DECIMAL(5,2) NOT NULL,
  urutan SMALLINT NOT NULL DEFAULT 0,
  FOREIGN KEY (instrumen_id) REFERENCES instrumen(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kategori_capaian (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  modul ENUM('pkks','supervisi','standar') NOT NULL,
  label VARCHAR(40) NOT NULL,
  min_persen DECIMAL(5,2) NOT NULL,
  max_persen DECIMAL(5,2) NOT NULL,
  warna VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE jadwal_supervisi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  unit_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  guru_id INT UNSIGNED NOT NULL,
  supervisor_id INT UNSIGNED NOT NULL,
  instrumen_id INT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('terjadwal','selesai','batal') NOT NULL DEFAULT 'terjadwal',
  catatan VARCHAR(255) NULL,
  KEY idx_js (unit_id, semester_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (semester_id) REFERENCES semester(id),
  FOREIGN KEY (guru_id) REFERENCES guru(id),
  FOREIGN KEY (supervisor_id) REFERENCES users(id),
  FOREIGN KEY (instrumen_id) REFERENCES instrumen(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE penilaian (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  modul ENUM('pkks','supervisi') NOT NULL,
  sesi_kode VARCHAR(40) NULL,
  instrumen_id INT UNSIGNED NOT NULL,
  unit_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  jenis_penilai ENUM('penilai','diri') NOT NULL DEFAULT 'penilai',
  guru_id INT UNSIGNED NULL,
  kepsek_user_id INT UNSIGNED NULL,
  penilai_id INT UNSIGNED NOT NULL,
  jadwal_id INT UNSIGNED NULL,
  mapel_semester_id INT UNSIGNED NULL,
  kelas_id INT UNSIGNED NULL,
  materi_topik VARCHAR(255) NULL,
  jam_tatap_muka SMALLINT NULL,
  tanggal DATE NOT NULL,
  tempat VARCHAR(100) NULL,
  snapshot_identitas JSON NULL,
  skor_total DECIMAL(8,2) NULL,
  skor_maks DECIMAL(8,2) NULL,
  nilai_akhir DECIMAL(5,2) NULL,
  kategori VARCHAR(30) NULL,
  catatan TEXT NULL,
  tindak_lanjut TEXT NULL,
  status ENUM('draft','diajukan','disetujui','ditolak') NOT NULL DEFAULT 'draft',
  ttd_penilai_at DATETIME NULL,
  ttd_dinilai_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_pkks (modul, instrumen_id, semester_id, kepsek_user_id, jenis_penilai),
  KEY idx_pn (modul, unit_id, semester_id),
  KEY idx_pn_guru (guru_id, semester_id),
  KEY idx_pn_sesi (sesi_kode),
  FOREIGN KEY (instrumen_id) REFERENCES instrumen(id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (semester_id) REFERENCES semester(id),
  FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL,
  FOREIGN KEY (kepsek_user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (penilai_id) REFERENCES users(id),
  FOREIGN KEY (jadwal_id) REFERENCES jadwal_supervisi(id) ON DELETE SET NULL,
  FOREIGN KEY (mapel_semester_id) REFERENCES mapel_semester(id) ON DELETE SET NULL,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE penilaian_detail (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  penilaian_id BIGINT UNSIGNED NOT NULL,
  butir_id INT UNSIGNED NOT NULL,
  opsi_id INT UNSIGNED NULL,
  skor DECIMAL(5,2) NULL,
  catatan TEXT NULL,
  UNIQUE KEY uq_pd (penilaian_id, butir_id),
  FOREIGN KEY (penilaian_id) REFERENCES penilaian(id) ON DELETE CASCADE,
  FOREIGN KEY (butir_id) REFERENCES instrumen_butir(id),
  FOREIGN KEY (opsi_id) REFERENCES instrumen_opsi(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kuesioner_kode (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  unit_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  slug VARCHAR(40) NOT NULL,
  kode_hash VARCHAR(255) NOT NULL,
  kode_label VARCHAR(40) NULL,
  berlaku_dari DATETIME NULL,
  berlaku_sampai DATETIME NULL,
  maks_pakai INT UNSIGNED NULL,
  jumlah_pakai INT UNSIGNED NOT NULL DEFAULT 0,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kk_slug (slug),
  KEY idx_kk (unit_id, semester_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (semester_id) REFERENCES semester(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kuesioner_respon (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrumen_id INT UNSIGNED NOT NULL,
  unit_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  kode_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  nama_responden VARCHAR(120) NULL,
  hubungan VARCHAR(60) NULL,
  ip_hash CHAR(64) NULL,
  cookie_hash CHAR(64) NULL,
  rerata DECIMAL(5,2) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_kr (unit_id, semester_id, instrumen_id),
  FOREIGN KEY (instrumen_id) REFERENCES instrumen(id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (semester_id) REFERENCES semester(id),
  FOREIGN KEY (kode_id) REFERENCES kuesioner_kode(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kuesioner_jawaban (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  respon_id BIGINT UNSIGNED NOT NULL,
  butir_id INT UNSIGNED NOT NULL,
  skor DECIMAL(5,2) NOT NULL,
  UNIQUE KEY uq_kj (respon_id, butir_id),
  FOREIGN KEY (respon_id) REFERENCES kuesioner_respon(id) ON DELETE CASCADE,
  FOREIGN KEY (butir_id) REFERENCES instrumen_butir(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bukti_file (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  unit_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NULL,
  ref_tipe ENUM('standar_butir','standar_mutu','penilaian_detail','penilaian','guru') NOT NULL,
  ref_id BIGINT UNSIGNED NOT NULL,
  driver ENUM('lokal','nextcloud') NOT NULL DEFAULT 'lokal',
  path VARCHAR(500) NOT NULL,
  nama_asli VARCHAR(255) NOT NULL,
  mime VARCHAR(100) NOT NULL,
  ukuran BIGINT UNSIGNED NOT NULL,
  checksum CHAR(64) NULL,
  keterangan VARCHAR(255) NULL,
  status_verifikasi ENUM('menunggu','valid','ditolak') NOT NULL DEFAULT 'menunggu',
  diverifikasi_oleh INT UNSIGNED NULL,
  diunggah_oleh INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  KEY idx_bf_ref (ref_tipe, ref_id),
  KEY idx_bf_unit (unit_id, semester_id),
  FOREIGN KEY (unit_id) REFERENCES unit_sekolah(id),
  FOREIGN KEY (semester_id) REFERENCES semester(id) ON DELETE SET NULL,
  FOREIGN KEY (diunggah_oleh) REFERENCES users(id),
  FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  aksi VARCHAR(40) NOT NULL,
  tabel VARCHAR(60) NULL,
  record_id BIGINT UNSIGNED NULL,
  data_lama JSON NULL,
  data_baru JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_al_user (user_id, created_at),
  KEY idx_al_tabel (tabel, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE backup_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_file VARCHAR(255) NOT NULL,
  ukuran BIGINT UNSIGNED NULL,
  dibuat_oleh INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
