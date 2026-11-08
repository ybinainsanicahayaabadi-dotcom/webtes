-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Nov 2026 pada 12.57
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sislia`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `aksi` varchar(40) NOT NULL,
  `tabel` varchar(60) DEFAULT NULL,
  `record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `data_lama` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_lama`)),
  `data_baru` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_baru`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `tabel`, `record_id`, `data_lama`, `data_baru`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 00:37:50'),
(2, 1, 'ganti_password', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 01:15:37'),
(3, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 03:21:01'),
(4, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 03:35:23'),
(5, 1, 'ubah', 'unit_sekolah', 3, '{\"id\":3,\"kode\":\"TKIT2\",\"nama\":\"TKIT Bina Insani 2\",\"jenjang\":\"TK\",\"npsn\":null,\"alamat\":null,\"kelurahan\":null,\"kecamatan\":null,\"kab_kota\":null,\"provinsi\":\"Banten\",\"telepon\":null,\"email\":null,\"website\":null,\"logo\":\"assets\\/img\\/logo\\/tkit2.png\",\"urutan\":3,\"is_aktif\":1,\"created_at\":\"2026-11-06 07:13:44\",\"deleted_at\":null}', '{\"kode\":\"TKIT2\",\"nama\":\"TKIT Bina Insani 2\",\"jenjang\":\"TK\",\"npsn\":null,\"alamat\":null,\"kelurahan\":null,\"kecamatan\":null,\"kab_kota\":null,\"provinsi\":\"Banten\",\"telepon\":null,\"email\":null,\"website\":null,\"urutan\":3,\"is_aktif\":1,\"logo\":\"assets\\/img\\/logo\\/unit-tkit2_98e888fdbf1c.png\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 03:59:12'),
(6, 1, 'ubah', 'users', 1, '{\"id\":1,\"username\":\"admin\",\"email\":null,\"nama_lengkap\":\"Administrator\",\"role\":\"admin\",\"unit_id\":null,\"guru_id\":null,\"tanda_tangan\":null,\"must_change_pw\":0,\"is_aktif\":1,\"last_login_at\":\"2026-11-06 10:35:23\"}', '{\"username\":\"admin\",\"nama_lengkap\":\"Administrator\",\"email\":null,\"role\":\"admin\",\"guru_id\":null,\"must_change_pw\":0,\"is_aktif\":1,\"unit_id\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 07:05:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `backup_log`
--

CREATE TABLE `backup_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `ukuran` bigint(20) UNSIGNED DEFAULT NULL,
  `dibuat_oleh` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bukti_file`
--

CREATE TABLE `bukti_file` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED DEFAULT NULL,
  `ref_tipe` enum('standar_butir','standar_mutu','penilaian_detail','penilaian','guru') NOT NULL,
  `ref_id` bigint(20) UNSIGNED NOT NULL,
  `driver` enum('lokal','nextcloud') NOT NULL DEFAULT 'lokal',
  `path` varchar(500) NOT NULL,
  `nama_asli` varchar(255) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `ukuran` bigint(20) UNSIGNED NOT NULL,
  `checksum` char(64) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `status_verifikasi` enum('menunggu','valid','ditolak') NOT NULL DEFAULT 'menunggu',
  `diverifikasi_oleh` int(10) UNSIGNED DEFAULT NULL,
  `diunggah_oleh` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id` int(10) UNSIGNED NOT NULL,
  `niy` varchar(30) DEFAULT NULL,
  `nuptk` varchar(30) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(80) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `tmt` date DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `telepon` varchar(40) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id`, `niy`, `nuptk`, `nama`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `pendidikan_terakhir`, `tmt`, `email`, `telepon`, `alamat`, `foto`, `status`, `created_at`, `deleted_at`) VALUES
(1, '439563', NULL, 'Eko Nugroho', 'L', 'Tangerang', '1983-09-10', 'S1', '2013-07-15', 'guru1@contoh.test', '081200000001', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(2, '175954', NULL, 'Siti Pratama', 'P', 'Tangerang', '1994-06-12', 'S1', '2021-07-15', 'guru2@contoh.test', '081200000002', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(3, '711097', NULL, 'Budi Saputra', 'L', 'Tangerang', '1995-01-18', 'S1', '2014-07-15', 'guru3@contoh.test', '081200000003', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(4, '139317', NULL, 'Citra Nugroho', 'P', 'Tangerang', '1998-02-14', 'S1', '2018-07-15', 'guru4@contoh.test', '081200000004', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(5, '173248', NULL, 'Hana Pratama', 'P', 'Tangerang', '1989-03-15', 'S1', '2022-07-15', 'guru5@contoh.test', '081200000005', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(6, '161981', NULL, 'Taufik Pratama', 'L', 'Tangerang', '1985-09-18', 'S1', '2022-07-15', 'guru6@contoh.test', '081200000006', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(7, '331821', NULL, 'Eko Rahmawati', 'P', 'Tangerang', '1994-06-13', 'S1', '2019-07-15', 'guru7@contoh.test', '081200000007', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(8, '251262', NULL, 'Taufik Rahmawati', 'L', 'Tangerang', '1984-04-16', 'S1', '2021-07-15', 'guru8@contoh.test', '081200000008', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(9, '208061', NULL, 'Taufik Utami', 'L', 'Tangerang', '1985-04-18', 'S1', '2017-07-15', 'guru9@contoh.test', '081200000009', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(10, '490487', NULL, 'Dewi Saputra', 'L', 'Tangerang', '1989-01-10', 'S1', '2022-07-15', 'guru10@contoh.test', '081200000010', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(11, '691783', NULL, 'Budi Utami', 'L', 'Tangerang', '1986-08-14', 'S1', '2013-07-15', 'guru11@contoh.test', '081200000011', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(12, '620528', NULL, 'Wahyu Saputra', 'P', 'Tangerang', '1997-06-17', 'S1', '2022-07-15', 'guru12@contoh.test', '081200000012', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(13, '932967', NULL, 'Fitri Permata', 'L', 'Tangerang', '1989-06-11', 'S1', '2013-07-15', 'guru13@contoh.test', '081200000013', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(14, '185831', NULL, 'Rizky Kusuma', 'P', 'Tangerang', '1981-04-17', 'S1', '2013-07-15', 'guru14@contoh.test', '081200000014', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(15, '864878', NULL, 'Oki Rahmawati', 'L', 'Tangerang', '1988-04-17', 'S1', '2019-07-15', 'guru15@contoh.test', '081200000015', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(16, '223800', NULL, 'Rizky Nugroho', 'L', 'Tangerang', '1997-01-17', 'S1', '2024-07-15', 'guru16@contoh.test', '081200000016', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(17, '893919', NULL, 'Khoirul Lestari', 'P', 'Tangerang', '1998-06-11', 'S1', '2023-07-15', 'guru17@contoh.test', '081200000017', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(18, '542182', NULL, 'Budi Ramadhan', 'L', 'Tangerang', '1981-07-13', 'S1', '2017-07-15', 'guru18@contoh.test', '081200000018', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(19, '168157', NULL, 'Budi Permata', 'P', 'Tangerang', '1983-07-15', 'S1', '2011-07-15', 'guru19@contoh.test', '081200000019', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(20, '778563', NULL, 'Taufik Ramadhan', 'P', 'Tangerang', '1990-08-16', 'S1', '2021-07-15', 'guru20@contoh.test', '081200000020', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(21, '398420', NULL, 'Yusuf Nugroho', 'P', 'Tangerang', '1980-03-12', 'S1', '2012-07-15', 'guru21@contoh.test', '081200000021', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(22, '123658', NULL, 'Oki Hidayat', 'L', 'Tangerang', '1978-03-19', 'S1', '2024-07-15', 'guru22@contoh.test', '081200000022', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(23, '740595', NULL, 'Dewi Kusuma', 'L', 'Tangerang', '1992-03-19', 'S1', '2023-07-15', 'guru23@contoh.test', '081200000023', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(24, '328807', NULL, 'Jihan Lestari', 'L', 'Tangerang', '1997-08-15', 'S1', '2012-07-15', 'guru24@contoh.test', '081200000024', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(25, '341960', NULL, 'Eko Pratama', 'L', 'Tangerang', '1995-09-12', 'S1', '2010-07-15', 'guru25@contoh.test', '081200000025', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(26, '258647', NULL, 'Hana Ramadhan', 'L', 'Tangerang', '1978-02-18', 'S1', '2021-07-15', 'guru26@contoh.test', '081200000026', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(27, '112649', NULL, 'Putri Utami', 'L', 'Tangerang', '1982-07-13', 'S1', '2023-07-15', 'guru27@contoh.test', '081200000027', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(28, '375509', NULL, 'Jihan Santoso', 'L', 'Tangerang', '1984-01-14', 'S1', '2013-07-15', 'guru28@contoh.test', '081200000028', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(29, '539297', NULL, 'Siti Hidayat', 'P', 'Tangerang', '1987-09-13', 'S1', '2022-07-15', 'guru29@contoh.test', '081200000029', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(30, '231587', NULL, 'Yusuf Saputra', 'L', 'Tangerang', '1996-06-14', 'S1', '2018-07-15', 'guru30@contoh.test', '081200000030', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(31, '318904', NULL, 'Oki Lestari', 'L', 'Tangerang', '1991-03-10', 'S1', '2024-07-15', 'guru31@contoh.test', '081200000031', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(32, '456572', NULL, 'Umi Santoso', 'L', 'Tangerang', '1989-08-19', 'S1', '2023-07-15', 'guru32@contoh.test', '081200000032', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(33, '100244', NULL, 'Taufik Lestari', 'L', 'Tangerang', '1994-07-18', 'S1', '2012-07-15', 'guru33@contoh.test', '081200000033', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(34, '481272', NULL, 'Citra Wijaya', 'P', 'Tangerang', '1995-03-18', 'S1', '2018-07-15', 'guru34@contoh.test', '081200000034', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(35, '255766', NULL, 'Vina Rahmawati', 'P', 'Tangerang', '1978-08-12', 'S1', '2019-07-15', 'guru35@contoh.test', '081200000035', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL),
(36, '731535', NULL, 'Lina Kusuma', 'L', 'Tangerang', '1978-03-12', 'S1', '2012-07-15', 'guru36@contoh.test', '081200000036', NULL, NULL, 'aktif', '2026-11-06 00:13:47', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru_penempatan`
--

CREATE TABLE `guru_penempatan` (
  `id` int(10) UNSIGNED NOT NULL,
  `guru_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `pangkat_golongan` varchar(80) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru_penempatan`
--

INSERT INTO `guru_penempatan` (`id`, `guru_id`, `tahun_ajaran_id`, `unit_id`, `jabatan`, `pangkat_golongan`, `status`) VALUES
(1, 1, 1, 2, 'Kepala Sekolah', 'III/c', 'aktif'),
(2, 2, 1, 2, 'Wakil Kepala Sekolah', 'III/b', 'aktif'),
(3, 3, 1, 2, 'Guru', 'III/a', 'aktif'),
(4, 4, 1, 2, 'Guru', 'III/a', 'aktif'),
(5, 5, 1, 2, 'Guru', 'III/a', 'aktif'),
(6, 6, 1, 2, 'Guru', 'III/a', 'aktif'),
(7, 7, 1, 3, 'Kepala Sekolah', 'III/c', 'aktif'),
(8, 8, 1, 3, 'Wakil Kepala Sekolah', 'III/b', 'aktif'),
(9, 9, 1, 3, 'Guru', 'III/a', 'aktif'),
(10, 10, 1, 3, 'Guru', 'III/a', 'aktif'),
(11, 11, 1, 3, 'Guru', 'III/a', 'aktif'),
(12, 12, 1, 3, 'Guru', 'III/a', 'aktif'),
(13, 13, 1, 4, 'Kepala Sekolah', 'III/c', 'aktif'),
(14, 14, 1, 4, 'Wakil Kepala Sekolah', 'III/b', 'aktif'),
(15, 15, 1, 4, 'Guru', 'III/a', 'aktif'),
(16, 16, 1, 4, 'Guru', 'III/a', 'aktif'),
(17, 17, 1, 4, 'Guru', 'III/a', 'aktif'),
(18, 18, 1, 4, 'Guru', 'III/a', 'aktif'),
(19, 19, 1, 5, 'Kepala Sekolah', 'III/c', 'aktif'),
(20, 20, 1, 5, 'Wakil Kepala Sekolah', 'III/b', 'aktif'),
(21, 21, 1, 5, 'Guru', 'III/a', 'aktif'),
(22, 22, 1, 5, 'Guru', 'III/a', 'aktif'),
(23, 23, 1, 5, 'Guru', 'III/a', 'aktif'),
(24, 24, 1, 5, 'Guru', 'III/a', 'aktif'),
(25, 25, 1, 6, 'Kepala Sekolah', 'III/c', 'aktif'),
(26, 26, 1, 6, 'Wakil Kepala Sekolah', 'III/b', 'aktif'),
(27, 27, 1, 6, 'Guru', 'III/a', 'aktif'),
(28, 28, 1, 6, 'Guru', 'III/a', 'aktif'),
(29, 29, 1, 6, 'Guru', 'III/a', 'aktif'),
(30, 30, 1, 6, 'Guru', 'III/a', 'aktif'),
(31, 31, 1, 7, 'Kepala Sekolah', 'III/c', 'aktif'),
(32, 32, 1, 7, 'Wakil Kepala Sekolah', 'III/b', 'aktif'),
(33, 33, 1, 7, 'Guru', 'III/a', 'aktif'),
(34, 34, 1, 7, 'Guru', 'III/a', 'aktif'),
(35, 35, 1, 7, 'Guru', 'III/a', 'aktif'),
(36, 36, 1, 7, 'Guru', 'III/a', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `instrumen`
--

CREATE TABLE `instrumen` (
  `id` int(10) UNSIGNED NOT NULL,
  `modul` enum('pkks','supervisi','kuesioner_ortu','kuesioner_guru') NOT NULL,
  `tahap` enum('perencanaan','pelaksanaan','asesmen') DEFAULT NULL,
  `kode` varchar(40) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `skor_maks_butir` decimal(5,2) DEFAULT NULL,
  `rumus` enum('persen_maks','rerata_skor') NOT NULL DEFAULT 'persen_maks',
  `catatan_form` text DEFAULT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `instrumen`
--

INSERT INTO `instrumen` (`id`, `modul`, `tahap`, `kode`, `nama`, `skor_maks_butir`, `rumus`, `catatan_form`, `urutan`, `is_aktif`) VALUES
(1, 'supervisi', 'perencanaan', 'SUP-ATP-V1', 'Penelaahan ATP', 2.00, 'persen_maks', 'Redaksi operasional ringkas berdasarkan struktur PDF; skala baik=2, sebagian=1, tidak=0. Periksa sebelum publikasi.', 1, 0),
(2, 'supervisi', 'perencanaan', 'SUP-ADM-V1', 'Administrasi Pembelajaran', 2.00, 'persen_maks', 'Redaksi operasional ringkas berdasarkan struktur PDF; skala baik=2, sebagian=1, tidak=0. Periksa sebelum publikasi.', 2, 0),
(3, 'supervisi', 'perencanaan', 'SUP-MOD-V1', 'Telaah Modul Ajar', 2.00, 'persen_maks', 'Redaksi operasional ringkas berdasarkan struktur PDF; skala baik=2, sebagian=1, tidak=0. Periksa sebelum publikasi.', 3, 0),
(4, 'supervisi', 'pelaksanaan', 'SUP-PEL-V1', 'Supervisi Pelaksanaan Pembelajaran', 2.00, 'persen_maks', 'Redaksi operasional ringkas berdasarkan struktur PDF; skala baik=2, sebagian=1, tidak=0. Periksa sebelum publikasi.', 4, 0),
(5, 'supervisi', 'asesmen', 'SUP-ASES-V1', 'Asesmen Pembelajaran (turunan)', 2.00, 'persen_maks', 'Redaksi operasional ringkas berdasarkan struktur PDF; skala baik=2, sebagian=1, tidak=0. Periksa sebelum publikasi.', 5, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `instrumen_butir`
--

CREATE TABLE `instrumen_butir` (
  `id` int(10) UNSIGNED NOT NULL,
  `instrumen_id` int(10) UNSIGNED NOT NULL,
  `kelompok_id` int(10) UNSIGNED DEFAULT NULL,
  `nomor` varchar(20) DEFAULT NULL,
  `pernyataan` text NOT NULL,
  `butuh_bukti` tinyint(1) NOT NULL DEFAULT 0,
  `opsi_set` varchar(40) DEFAULT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `instrumen_butir`
--

INSERT INTO `instrumen_butir` (`id`, `instrumen_id`, `kelompok_id`, `nomor`, `pernyataan`, `butuh_bukti`, `opsi_set`, `urutan`, `is_aktif`) VALUES
(1, 1, 1, '1', 'Identitas sekolah, mapel, kelas, semester, dan CP tersedia.', 0, 'default', 1, 1),
(2, 1, 2, '2', 'Pemetaan kompetensi sesuai fase belajar.', 0, 'default', 2, 1),
(3, 1, 2, '3', 'CP dicantumkan.', 0, 'default', 3, 1),
(4, 1, 2, '4', 'Tujuan belajar dicantumkan.', 0, 'default', 4, 1),
(5, 1, 3, '5', 'ATP memuat kompetensi.', 0, 'default', 5, 1),
(6, 1, 3, '6', 'ATP memuat lingkup konten.', 0, 'default', 6, 1),
(7, 1, 3, '7', 'ATP memuat variasi pembelajaran.', 0, 'default', 7, 1),
(8, 1, 4, '8', 'Urutan kompetensi tersusun jelas.', 0, 'default', 8, 1),
(9, 1, 4, '9', 'Tahapan belajar dalam fase tersusun berkesinambungan.', 0, 'default', 9, 1),
(10, 1, 4, '10', 'Perkembangan kompetensi antarfase tergambar.', 0, 'default', 10, 1),
(11, 1, 4, '11', 'Elemen profil pelajar terkait tujuan diidentifikasi.', 0, 'default', 11, 1),
(12, 1, 4, '12', 'Dokumen ATP tersedia untuk ditelaah.', 0, 'default', 12, 1),
(13, 2, 5, '1', 'Kalender pendidikan', 0, 'default', 1, 1),
(14, 2, 5, '2', 'Program tahunan', 0, 'default', 2, 1),
(15, 2, 5, '3', 'Program semester', 0, 'default', 3, 1),
(16, 2, 5, '4', 'ATP', 0, 'default', 4, 1),
(17, 2, 5, '5', 'Modul ajar', 0, 'default', 5, 1),
(18, 2, 5, '6', 'Jadwal tatap muka', 0, 'default', 6, 1),
(19, 2, 5, '7', 'Agenda mengajar', 0, 'default', 7, 1),
(20, 2, 5, '8', 'Rekap nilai', 0, 'default', 8, 1),
(21, 2, 5, '9', 'KKTP', 0, 'default', 9, 1),
(22, 2, 5, '10', 'Presensi siswa', 0, 'default', 10, 1),
(23, 2, 5, '11', 'Buku pegangan pendidik', 0, 'default', 11, 1),
(24, 2, 5, '12', 'Buku teks peserta didik', 0, 'default', 12, 1),
(25, 3, 6, '1', 'Identitas penyusun, institusi, tahun, kelas, dan durasi tercantum.', 0, 'default', 1, 1),
(26, 3, 7, '2', 'Kompetensi awal diidentifikasi.', 0, 'default', 2, 1),
(27, 3, 7, '3', 'Profil Pelajar Pancasila dicantumkan.', 0, 'default', 3, 1),
(28, 3, 8, '4', 'Fasilitas mendukung rancangan kegiatan.', 0, 'default', 4, 1),
(29, 3, 8, '5', 'Sumber materi mendukung pembelajaran.', 0, 'default', 5, 1),
(30, 3, 10, '6', 'Rancangan tatap muka tersedia.', 0, 'default', 6, 1),
(31, 3, 10, '7', 'Rancangan blended tersedia.', 0, 'default', 7, 1),
(32, 3, 11, '8', 'Tujuan belajar dirumuskan tepat.', 0, 'default', 8, 1),
(33, 3, 11, '9', 'Pemahaman bermakna dirumuskan.', 0, 'default', 9, 1),
(34, 3, 11, '10', 'Pertanyaan pemantik disiapkan.', 0, 'default', 10, 1),
(35, 3, 11, '11', 'Persiapan kegiatan dijabarkan.', 0, 'default', 11, 1),
(36, 3, 12, '12', 'Pembukaan memuat motivasi dan apersepsi.', 0, 'default', 12, 1),
(37, 3, 12, '13', 'Kegiatan inti mengarahkan pengamatan, pertanyaan, eksplorasi, simpulan, dan komunikasi.', 0, 'default', 13, 1),
(38, 3, 12, '14', 'Penutupan memuat rangkuman, refleksi, dan umpan balik.', 0, 'default', 14, 1),
(39, 3, 13, '15', 'Instrumen dan teknik selaras dengan tujuan.', 0, 'default', 15, 1),
(40, 3, 13, '16', 'Rancangan asesmen sikap sesuai.', 0, 'default', 16, 1),
(41, 3, 13, '17', 'Rancangan asesmen pengetahuan sesuai.', 0, 'default', 17, 1),
(42, 3, 13, '18', 'Rancangan asesmen keterampilan sesuai.', 0, 'default', 18, 1),
(43, 3, 14, '19', 'Remedial mempertimbangkan kebutuhan siswa dan sumber daya.', 0, 'default', 19, 1),
(44, 3, 15, '20', 'Pengayaan mempertimbangkan kebutuhan siswa dan sumber daya.', 0, 'default', 20, 1),
(45, 3, 16, '21', 'LKPD disertakan.', 0, 'default', 21, 1),
(46, 3, 16, '22', 'Bacaan pendidik dan peserta didik disertakan.', 0, 'default', 22, 1),
(47, 3, 16, '23', 'Glosarium disertakan.', 0, 'default', 23, 1),
(48, 3, 16, '24', 'Daftar pustaka disertakan.', 0, 'default', 24, 1),
(49, 4, 17, '1', 'Kesiapan fisik dan psikis siswa dibangun melalui sapaan.', 0, 'default', 1, 1),
(50, 4, 17, '2', 'Rencana aktivitas individu, kelompok, dan observasi dijelaskan.', 0, 'default', 2, 1),
(51, 4, 18, '3', 'Pertanyaan menantang digunakan untuk memotivasi.', 0, 'default', 3, 1),
(52, 4, 18, '4', 'Manfaat materi dijelaskan.', 0, 'default', 4, 1),
(53, 4, 19, '5', 'Kompetensi sasaran disampaikan.', 0, 'default', 5, 1),
(54, 4, 19, '6', 'Materi dihubungkan dengan pembelajaran terdahulu.', 0, 'default', 6, 1),
(55, 4, 19, '7', 'Demonstrasi awal terkait materi diberikan.', 0, 'default', 7, 1),
(56, 4, 20, '8', 'Materi selaras dengan tujuan.', 0, 'default', 8, 1),
(57, 4, 20, '9', 'Materi terhubung dengan pengetahuan lain dan kehidupan.', 0, 'default', 9, 1),
(58, 4, 20, '10', 'Penjelasan materi akurat.', 0, 'default', 10, 1),
(59, 4, 20, '11', 'Materi disajikan bertahap dari sederhana ke kompleks.', 0, 'default', 11, 1),
(60, 4, 21, '12', 'Kegiatan selaras dengan kompetensi sasaran.', 0, 'default', 12, 1),
(61, 4, 21, '13', 'Siswa mendapat ruang untuk bertanya.', 0, 'default', 13, 1),
(62, 4, 21, '14', 'Siswa mendapat ruang untuk menyatakan pendapat.', 0, 'default', 14, 1),
(63, 4, 21, '15', 'Kegiatan mengembangkan keterampilan siswa.', 0, 'default', 15, 1),
(64, 4, 21, '16', 'Konteks nyata digunakan dalam pembelajaran.', 0, 'default', 16, 1),
(65, 4, 21, '17', 'Durasi pelaksanaan sesuai rencana.', 0, 'default', 17, 1),
(66, 4, 22, '18', 'Aktivitas mendorong kreativitas.', 0, 'default', 18, 1),
(67, 4, 22, '19', 'Aktivitas mendorong berpikir kritis.', 0, 'default', 19, 1),
(68, 4, 22, '20', 'Aktivitas mendorong komunikasi.', 0, 'default', 20, 1),
(69, 4, 22, '21', 'Aktivitas mendorong kolaborasi.', 0, 'default', 21, 1),
(70, 4, 23, '22', 'Suasana kelas mendukung konsentrasi belajar.', 0, 'default', 22, 1),
(71, 4, 23, '23', 'Kesepakatan kelas ditegakkan dengan disiplin positif.', 0, 'default', 23, 1),
(72, 4, 24, '24', 'Sumber belajar digunakan secara bervariasi.', 0, 'default', 24, 1),
(73, 4, 24, '25', 'Media belajar digunakan dengan terampil.', 0, 'default', 25, 1),
(74, 4, 24, '26', 'Siswa terlibat dalam penggunaan sumber.', 0, 'default', 26, 1),
(75, 4, 24, '27', 'Siswa terlibat dalam penggunaan media.', 0, 'default', 27, 1),
(76, 4, 24, '28', 'Penyajian menarik perhatian siswa.', 0, 'default', 28, 1),
(77, 4, 25, '29', 'Bahasa lisan jelas dan lancar.', 0, 'default', 29, 1),
(78, 4, 25, '30', 'Bahasa tulis tepat dan mudah dipahami.', 0, 'default', 30, 1),
(79, 4, 26, '31', 'Siswa dibimbing menyusun rangkuman.', 0, 'default', 31, 1),
(80, 4, 26, '32', 'Guru menunjukkan upaya meningkatkan pengetahuan dan keterampilan mengajar.', 0, 'default', 32, 1),
(81, 4, 26, '33', 'Praktik mengajar direfleksikan berdasarkan dampaknya pada siswa.', 0, 'default', 33, 1),
(82, 4, 26, '34', 'Pendekatan baru diterapkan dari perencanaan hingga evaluasi.', 0, 'default', 34, 1),
(83, 4, 26, '35', 'Arahan lanjutan, perbaikan, dan pengayaan diberikan.', 0, 'default', 35, 1),
(84, 4, 27, '36', 'Asesmen sikap dilakukan melalui observasi.', 0, 'default', 36, 1),
(85, 4, 27, '37', 'Asesmen pengetahuan dilakukan melalui tes.', 0, 'default', 37, 1),
(86, 4, 27, '38', 'Asesmen keterampilan menggunakan kinerja atau karya siswa.', 0, 'default', 38, 1),
(87, 5, 28, '1', 'Instrumen dan teknik selaras dengan tujuan.', 0, 'default', 1, 1),
(88, 5, 28, '2', 'Rancangan asesmen sikap sesuai.', 0, 'default', 2, 1),
(89, 5, 28, '3', 'Rancangan asesmen pengetahuan sesuai.', 0, 'default', 3, 1),
(90, 5, 28, '4', 'Rancangan asesmen keterampilan sesuai.', 0, 'default', 4, 1),
(91, 5, 29, '5', 'Asesmen sikap dilakukan melalui observasi.', 0, 'default', 5, 1),
(92, 5, 29, '6', 'Asesmen pengetahuan dilakukan melalui tes.', 0, 'default', 6, 1),
(93, 5, 29, '7', 'Asesmen keterampilan menggunakan kinerja atau karya siswa.', 0, 'default', 7, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `instrumen_kelompok`
--

CREATE TABLE `instrumen_kelompok` (
  `id` int(10) UNSIGNED NOT NULL,
  `instrumen_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `kode` varchar(20) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `instrumen_kelompok`
--

INSERT INTO `instrumen_kelompok` (`id`, `instrumen_id`, `parent_id`, `kode`, `nama`, `urutan`) VALUES
(1, 1, NULL, 'A', 'Identitas ATP', 1),
(2, 1, NULL, 'B', 'Kompetensi dan tujuan', 2),
(3, 1, NULL, 'C', 'Komponen ATP', 3),
(4, 1, NULL, 'D', 'Kriteria ATP', 4),
(5, 2, NULL, 'A', 'Administrasi', 1),
(6, 3, NULL, 'A', 'Identitas', 1),
(7, 3, NULL, 'B', 'Kesiapan awal dan profil', 2),
(8, 3, NULL, 'C', 'Sarana belajar', 3),
(9, 3, NULL, 'D', 'Target peserta didik', 4),
(10, 3, NULL, 'E', 'Model belajar', 5),
(11, 3, NULL, 'F', 'Komponen pembelajaran', 6),
(12, 3, NULL, 'G', 'Skenario', 7),
(13, 3, NULL, 'H', 'Rancangan asesmen', 8),
(14, 3, NULL, 'I', 'Remedial', 9),
(15, 3, NULL, 'J', 'Pengayaan', 10),
(16, 3, NULL, 'K', 'Lampiran', 11),
(17, 4, NULL, 'A1', 'Pendahuluan · orientasi', 1),
(18, 4, NULL, 'A2', 'Pendahuluan · motivasi', 2),
(19, 4, NULL, 'A3', 'Pendahuluan · apersepsi', 3),
(20, 4, NULL, 'B1', 'Inti · penguasaan materi', 4),
(21, 4, NULL, 'B2', 'Inti · strategi', 5),
(22, 4, NULL, 'B3', 'Inti · HOTS dan 4C', 6),
(23, 4, NULL, 'B4', 'Inti · manajemen kelas', 7),
(24, 4, NULL, 'B5', 'Inti · sumber dan media', 8),
(25, 4, NULL, 'B6', 'Inti · bahasa', 9),
(26, 4, NULL, 'C1', 'Penutup · refleksi dan tindak lanjut', 10),
(27, 4, NULL, 'C2', 'Penutup · asesmen hasil', 11),
(28, 5, NULL, 'H', 'Rancangan asesmen', 1),
(29, 5, NULL, 'C2', 'Pelaksanaan asesmen', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `instrumen_opsi`
--

CREATE TABLE `instrumen_opsi` (
  `id` int(10) UNSIGNED NOT NULL,
  `instrumen_id` int(10) UNSIGNED NOT NULL,
  `opsi_set` varchar(40) NOT NULL DEFAULT 'default',
  `label` varchar(60) NOT NULL,
  `bobot` decimal(5,2) DEFAULT NULL,
  `skor` decimal(5,2) NOT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `instrumen_opsi`
--

INSERT INTO `instrumen_opsi` (`id`, `instrumen_id`, `opsi_set`, `label`, `bobot`, `skor`, `urutan`) VALUES
(1, 1, 'default', 'Tidak tersedia / tidak sesuai', NULL, 0.00, 0),
(2, 1, 'default', 'Sebagian / kurang sesuai', NULL, 1.00, 1),
(3, 1, 'default', 'Tersedia lengkap / sesuai', NULL, 2.00, 2),
(4, 2, 'default', 'Tidak tersedia / tidak sesuai', NULL, 0.00, 0),
(5, 2, 'default', 'Sebagian / kurang sesuai', NULL, 1.00, 1),
(6, 2, 'default', 'Tersedia lengkap / sesuai', NULL, 2.00, 2),
(7, 3, 'default', 'Tidak tersedia / tidak sesuai', NULL, 0.00, 0),
(8, 3, 'default', 'Sebagian / kurang sesuai', NULL, 1.00, 1),
(9, 3, 'default', 'Tersedia lengkap / sesuai', NULL, 2.00, 2),
(10, 4, 'default', 'Tidak tersedia / tidak sesuai', NULL, 0.00, 0),
(11, 4, 'default', 'Sebagian / kurang sesuai', NULL, 1.00, 1),
(12, 4, 'default', 'Tersedia lengkap / sesuai', NULL, 2.00, 2),
(13, 5, 'default', 'Tidak tersedia / tidak sesuai', NULL, 0.00, 0),
(14, 5, 'default', 'Sebagian / kurang sesuai', NULL, 1.00, 1),
(15, 5, 'default', 'Tersedia lengkap / sesuai', NULL, 2.00, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_supervisi`
--

CREATE TABLE `jadwal_supervisi` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `guru_id` int(10) UNSIGNED NOT NULL,
  `supervisor_id` int(10) UNSIGNED NOT NULL,
  `instrumen_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('terjadwal','selesai','batal') NOT NULL DEFAULT 'terjadwal',
  `catatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_capaian`
--

CREATE TABLE `kategori_capaian` (
  `id` int(10) UNSIGNED NOT NULL,
  `modul` enum('pkks','supervisi','standar') NOT NULL,
  `label` varchar(40) NOT NULL,
  `min_persen` decimal(5,2) NOT NULL,
  `max_persen` decimal(5,2) NOT NULL,
  `warna` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_capaian`
--

INSERT INTO `kategori_capaian` (`id`, `modul`, `label`, `min_persen`, `max_persen`, `warna`) VALUES
(1, 'pkks', 'Sangat Baik', 91.00, 100.00, 'success'),
(2, 'pkks', 'Baik', 81.00, 90.99, 'primary'),
(3, 'pkks', 'Cukup', 71.00, 80.99, 'warning'),
(4, 'pkks', 'Kurang', 0.00, 70.99, 'danger'),
(5, 'supervisi', 'Sangat Baik', 91.00, 100.00, 'success'),
(6, 'supervisi', 'Baik', 81.00, 90.99, 'primary'),
(7, 'supervisi', 'Cukup', 71.00, 80.99, 'warning'),
(8, 'supervisi', 'Kurang', 0.00, 70.99, 'danger'),
(9, 'standar', 'Sangat Baik', 91.00, 100.00, 'success'),
(10, 'standar', 'Baik', 81.00, 90.99, 'primary'),
(11, 'standar', 'Cukup', 71.00, 80.99, 'warning'),
(12, 'standar', 'Kurang', 0.00, 70.99, 'danger');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `tingkat` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `wali_guru_id` int(10) UNSIGNED DEFAULT NULL,
  `jumlah_siswa` smallint(6) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `unit_id`, `tahun_ajaran_id`, `tingkat`, `nama`, `wali_guru_id`, `jumlah_siswa`, `deleted_at`) VALUES
(1, 2, 1, 'A', 'Kelompok A1', 3, 28, NULL),
(2, 2, 1, 'B', 'Kelompok B1', 4, 28, NULL),
(3, 3, 1, 'A', 'Kelompok A1', 9, 30, NULL),
(4, 3, 1, 'B', 'Kelompok B1', 10, 23, NULL),
(5, 4, 1, '1', 'Kelas 1A', 15, 30, NULL),
(6, 4, 1, '2', 'Kelas 2A', 16, 26, NULL),
(7, 4, 1, '3', 'Kelas 3A', 17, 27, NULL),
(8, 4, 1, '4', 'Kelas 4A', 18, 30, NULL),
(9, 4, 1, '5', 'Kelas 5A', 15, 23, NULL),
(10, 4, 1, '6', 'Kelas 6A', 16, 23, NULL),
(11, 5, 1, '1', 'Kelas 1A', 21, 24, NULL),
(12, 5, 1, '2', 'Kelas 2A', 22, 24, NULL),
(13, 5, 1, '3', 'Kelas 3A', 23, 25, NULL),
(14, 5, 1, '4', 'Kelas 4A', 24, 19, NULL),
(15, 5, 1, '5', 'Kelas 5A', 21, 20, NULL),
(16, 5, 1, '6', 'Kelas 6A', 22, 25, NULL),
(17, 6, 1, '7', 'Kelas 7A', 27, 25, NULL),
(18, 6, 1, '8', 'Kelas 8A', 28, 30, NULL),
(19, 6, 1, '9', 'Kelas 9A', 29, 28, NULL),
(20, 7, 1, '10', 'Kelas X-1', 33, 19, NULL),
(21, 7, 1, '11', 'Kelas XI-1', 34, 25, NULL),
(22, 7, 1, '12', 'Kelas XII-1', 35, 25, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_jawaban`
--

CREATE TABLE `kuesioner_jawaban` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `respon_id` bigint(20) UNSIGNED NOT NULL,
  `butir_id` int(10) UNSIGNED NOT NULL,
  `skor` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_kode`
--

CREATE TABLE `kuesioner_kode` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(40) NOT NULL,
  `kode_hash` varchar(255) NOT NULL,
  `kode_label` varchar(40) DEFAULT NULL,
  `berlaku_dari` datetime DEFAULT NULL,
  `berlaku_sampai` datetime DEFAULT NULL,
  `maks_pakai` int(10) UNSIGNED DEFAULT NULL,
  `jumlah_pakai` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_respon`
--

CREATE TABLE `kuesioner_respon` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `instrumen_id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `kode_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `nama_responden` varchar(120) DEFAULT NULL,
  `hubungan` varchar(60) DEFAULT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `cookie_hash` char(64) DEFAULT NULL,
  `rerata` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempt`
--

CREATE TABLE `login_attempt` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `berhasil` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `login_attempt`
--

INSERT INTO `login_attempt` (`id`, `username`, `ip_address`, `berhasil`, `created_at`) VALUES
(1, 'admin', '::1', 1, '2026-11-06 00:37:50'),
(2, 'admin', '::1', 1, '2026-11-06 03:35:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mapel`
--

CREATE TABLE `mapel` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(20) DEFAULT NULL,
  `nama` varchar(120) NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mapel`
--

INSERT INTO `mapel` (`id`, `unit_id`, `kode`, `nama`, `deleted_at`) VALUES
(1, 2, 'TKIT1-01', 'Nilai Agama dan Budi Pekerti', NULL),
(2, 2, 'TKIT1-02', 'Jati Diri', NULL),
(3, 2, 'TKIT1-03', 'Dasar Literasi dan STEAM', NULL),
(4, 3, 'TKIT2-04', 'Nilai Agama dan Budi Pekerti', NULL),
(5, 3, 'TKIT2-05', 'Jati Diri', NULL),
(6, 3, 'TKIT2-06', 'Dasar Literasi dan STEAM', NULL),
(7, 4, 'SDIT1-07', 'Pendidikan Agama Islam', NULL),
(8, 4, 'SDIT1-08', 'Bahasa Indonesia', NULL),
(9, 4, 'SDIT1-09', 'Matematika', NULL),
(10, 4, 'SDIT1-10', 'IPAS', NULL),
(11, 4, 'SDIT1-11', 'Bahasa Inggris', NULL),
(12, 4, 'SDIT1-12', 'Tahfidz', NULL),
(13, 5, 'SDIT2-13', 'Pendidikan Agama Islam', NULL),
(14, 5, 'SDIT2-14', 'Bahasa Indonesia', NULL),
(15, 5, 'SDIT2-15', 'Matematika', NULL),
(16, 5, 'SDIT2-16', 'IPAS', NULL),
(17, 5, 'SDIT2-17', 'Bahasa Inggris', NULL),
(18, 5, 'SDIT2-18', 'Tahfidz', NULL),
(19, 6, 'SMPIT-19', 'Pendidikan Agama Islam', NULL),
(20, 6, 'SMPIT-20', 'Bahasa Indonesia', NULL),
(21, 6, 'SMPIT-21', 'Matematika', NULL),
(22, 6, 'SMPIT-22', 'IPA', NULL),
(23, 6, 'SMPIT-23', 'IPS', NULL),
(24, 6, 'SMPIT-24', 'Bahasa Inggris', NULL),
(25, 7, 'SMAIT-25', 'Pendidikan Agama Islam', NULL),
(26, 7, 'SMAIT-26', 'Bahasa Indonesia', NULL),
(27, 7, 'SMAIT-27', 'Matematika', NULL),
(28, 7, 'SMAIT-28', 'Fisika', NULL),
(29, 7, 'SMAIT-29', 'Biologi', NULL),
(30, 7, 'SMAIT-30', 'Bahasa Inggris', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mapel_semester`
--

CREATE TABLE `mapel_semester` (
  `id` int(10) UNSIGNED NOT NULL,
  `mapel_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `tingkat` varchar(10) NOT NULL DEFAULT '',
  `jam_per_minggu` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mapel_semester`
--

INSERT INTO `mapel_semester` (`id`, `mapel_id`, `semester_id`, `tingkat`, `jam_per_minggu`) VALUES
(1, 1, 1, '', 4),
(2, 1, 2, '', 2),
(3, 2, 1, '', 4),
(4, 2, 2, '', 4),
(5, 3, 1, '', 3),
(6, 3, 2, '', 2),
(7, 4, 1, '', 3),
(8, 4, 2, '', 4),
(9, 5, 1, '', 3),
(10, 5, 2, '', 3),
(11, 6, 1, '', 3),
(12, 6, 2, '', 2),
(13, 7, 1, '', 4),
(14, 7, 2, '', 3),
(15, 8, 1, '', 4),
(16, 8, 2, '', 3),
(17, 9, 1, '', 4),
(18, 9, 2, '', 3),
(19, 10, 1, '', 2),
(20, 10, 2, '', 2),
(21, 11, 1, '', 3),
(22, 11, 2, '', 3),
(23, 12, 1, '', 4),
(24, 12, 2, '', 4),
(25, 13, 1, '', 3),
(26, 13, 2, '', 4),
(27, 14, 1, '', 3),
(28, 14, 2, '', 2),
(29, 15, 1, '', 3),
(30, 15, 2, '', 4),
(31, 16, 1, '', 3),
(32, 16, 2, '', 4),
(33, 17, 1, '', 3),
(34, 17, 2, '', 3),
(35, 18, 1, '', 4),
(36, 18, 2, '', 3),
(37, 19, 1, '', 4),
(38, 19, 2, '', 3),
(39, 20, 1, '', 3),
(40, 20, 2, '', 3),
(41, 21, 1, '', 3),
(42, 21, 2, '', 2),
(43, 22, 1, '', 3),
(44, 22, 2, '', 4),
(45, 23, 1, '', 3),
(46, 23, 2, '', 2),
(47, 24, 1, '', 2),
(48, 24, 2, '', 2),
(49, 25, 1, '', 3),
(50, 25, 2, '', 3),
(51, 26, 1, '', 3),
(52, 26, 2, '', 2),
(53, 27, 1, '', 2),
(54, 27, 2, '', 2),
(55, 28, 1, '', 4),
(56, 28, 2, '', 3),
(57, 29, 1, '', 4),
(58, 29, 2, '', 3),
(59, 30, 1, '', 3),
(60, 30, 2, '', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `kunci` varchar(80) NOT NULL,
  `nilai` text DEFAULT NULL,
  `grup` varchar(40) NOT NULL DEFAULT 'umum',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`kunci`, `nilai`, `grup`, `updated_at`) VALUES
('app_footer', '© Yayasan Bina Insani Cahaya Abadi', 'tampilan', '2026-11-06 00:13:45'),
('app_kepanjangan', 'Sistem Satuan Layanan Informasi Akademik', 'umum', '2026-11-06 00:13:45'),
('app_nama', 'SISLIA', 'umum', '2026-11-06 00:13:45'),
('kuesioner_kode_panjang', '8', 'keamanan', '2026-11-06 00:13:45'),
('login_kunci_menit', '15', 'keamanan', '2026-11-06 00:13:45'),
('login_maks_gagal', '5', 'keamanan', '2026-11-06 00:13:45'),
('nextcloud_folder', '/SISLIA', 'storage', '2026-11-06 00:13:45'),
('nextcloud_password', '', 'storage', '2026-11-06 00:13:45'),
('nextcloud_url', '', 'storage', '2026-11-06 00:13:45'),
('nextcloud_user', '', 'storage', '2026-11-06 00:13:45'),
('password_min', '8', 'keamanan', '2026-11-06 00:13:45'),
('sesi_hari', '30', 'keamanan', '2026-11-06 00:13:45'),
('storage_driver', 'lokal', 'storage', '2026-11-06 00:13:45'),
('tema_default', 'terang', 'tampilan', '2026-11-06 00:13:45'),
('timezone', 'Asia/Jakarta', 'umum', '2026-11-06 00:13:45'),
('upload_ekstensi', 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx', 'storage', '2026-11-06 00:13:45'),
('upload_maks_mb', '5', 'storage', '2026-11-06 00:13:45'),
('warna_utama', '#4f46e5', 'tampilan', '2026-11-06 00:13:45'),
('yayasan_nama', 'Yayasan Bina Insani Cahaya Abadi', 'umum', '2026-11-06 00:13:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian`
--

CREATE TABLE `penilaian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `modul` enum('pkks','supervisi') NOT NULL,
  `sesi_kode` varchar(40) DEFAULT NULL,
  `instrumen_id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `jenis_penilai` enum('penilai','diri') NOT NULL DEFAULT 'penilai',
  `guru_id` int(10) UNSIGNED DEFAULT NULL,
  `kepsek_user_id` int(10) UNSIGNED DEFAULT NULL,
  `penilai_id` int(10) UNSIGNED NOT NULL,
  `jadwal_id` int(10) UNSIGNED DEFAULT NULL,
  `mapel_semester_id` int(10) UNSIGNED DEFAULT NULL,
  `kelas_id` int(10) UNSIGNED DEFAULT NULL,
  `materi_topik` varchar(255) DEFAULT NULL,
  `jam_tatap_muka` smallint(6) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `tempat` varchar(100) DEFAULT NULL,
  `snapshot_identitas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot_identitas`)),
  `skor_total` decimal(8,2) DEFAULT NULL,
  `skor_maks` decimal(8,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `kategori` varchar(30) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `tindak_lanjut` text DEFAULT NULL,
  `status` enum('draft','diajukan','disetujui','ditolak') NOT NULL DEFAULT 'draft',
  `ttd_penilai_at` datetime DEFAULT NULL,
  `ttd_dinilai_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian_detail`
--

CREATE TABLE `penilaian_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penilaian_id` bigint(20) UNSIGNED NOT NULL,
  `butir_id` int(10) UNSIGNED NOT NULL,
  `opsi_id` int(10) UNSIGNED DEFAULT NULL,
  `skor` decimal(5,2) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penugasan_guru`
--

CREATE TABLE `penugasan_guru` (
  `id` int(10) UNSIGNED NOT NULL,
  `guru_id` int(10) UNSIGNED NOT NULL,
  `kelas_id` int(10) UNSIGNED NOT NULL,
  `mapel_semester_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penugasan_guru`
--

INSERT INTO `penugasan_guru` (`id`, `guru_id`, `kelas_id`, `mapel_semester_id`) VALUES
(1, 3, 1, 1),
(2, 3, 2, 3),
(4, 4, 1, 1),
(3, 4, 2, 5),
(5, 5, 1, 3),
(6, 5, 2, 5),
(8, 6, 1, 3),
(7, 6, 2, 1),
(9, 9, 3, 7),
(10, 9, 4, 9),
(12, 10, 3, 7),
(11, 10, 4, 11),
(13, 11, 3, 9),
(14, 11, 4, 11),
(16, 12, 3, 9),
(15, 12, 4, 7),
(17, 15, 5, 13),
(18, 15, 6, 15),
(19, 16, 6, 17),
(20, 16, 7, 19),
(21, 17, 7, 21),
(22, 17, 8, 23),
(23, 18, 8, 13),
(24, 18, 9, 15),
(25, 21, 11, 25),
(26, 21, 12, 27),
(27, 22, 12, 29),
(28, 22, 13, 31),
(29, 23, 13, 33),
(30, 23, 14, 35),
(31, 24, 14, 25),
(32, 24, 15, 27),
(33, 27, 17, 37),
(34, 27, 18, 39),
(35, 28, 18, 41),
(36, 28, 19, 43),
(38, 29, 17, 47),
(37, 29, 19, 45),
(39, 30, 17, 37),
(40, 30, 18, 39),
(41, 33, 20, 49),
(42, 33, 21, 51),
(43, 34, 21, 53),
(44, 34, 22, 55),
(46, 35, 20, 59),
(45, 35, 22, 57),
(47, 36, 20, 49),
(48, 36, 21, 51);

-- --------------------------------------------------------

--
-- Struktur dari tabel `semester`
--

CREATE TABLE `semester` (
  `id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `nama` enum('Ganjil','Genap') NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 0,
  `is_terkunci` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `semester`
--

INSERT INTO `semester` (`id`, `tahun_ajaran_id`, `nama`, `tgl_mulai`, `tgl_selesai`, `is_aktif`, `is_terkunci`) VALUES
(1, 1, 'Ganjil', '2026-07-13', '2026-12-19', 1, 0),
(2, 1, 'Genap', '2027-01-04', '2027-06-26', 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `standar_butir`
--

CREATE TABLE `standar_butir` (
  `id` int(10) UNSIGNED NOT NULL,
  `standar_id` int(10) UNSIGNED NOT NULL,
  `nomor` varchar(20) DEFAULT NULL,
  `indikator` text NOT NULL,
  `bukti_dibutuhkan` text DEFAULT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `standar_butir`
--

INSERT INTO `standar_butir` (`id`, `standar_id`, `nomor`, `indikator`, `bukti_dibutuhkan`, `urutan`, `is_aktif`) VALUES
(1, 1, 'SKL.1', '[CONTOH] Lulusan memiliki akhlak mulia dan karakter islami.', 'Dokumen pendukung terkait', 1, 1),
(2, 1, 'SKL.2', '[CONTOH] Lulusan menguasai kompetensi akademik sesuai jenjang.', 'Dokumen pendukung terkait', 2, 1),
(3, 1, 'SKL.3', '[CONTOH] Lulusan memiliki keterampilan abad 21.', 'Dokumen pendukung terkait', 3, 1),
(4, 2, 'SI.1', '[CONTOH] Kurikulum satuan pendidikan tersusun dan disahkan.', 'Dokumen pendukung terkait', 1, 1),
(5, 2, 'SI.2', '[CONTOH] Muatan lokal dan keislaman terintegrasi dalam kurikulum.', 'Dokumen pendukung terkait', 2, 1),
(6, 2, 'SI.3', '[CONTOH] Kalender pendidikan tersedia dan disosialisasikan.', 'Dokumen pendukung terkait', 3, 1),
(7, 3, 'SPS.1', '[CONTOH] Program pembinaan karakter dan ibadah siswa terjadwal.', 'Dokumen pendukung terkait', 1, 1),
(8, 3, 'SPS.2', '[CONTOH] Kegiatan ekstrakurikuler tersedia dan terdokumentasi.', 'Dokumen pendukung terkait', 2, 1),
(9, 3, 'SPS.3', '[CONTOH] Layanan bimbingan dan konseling berjalan.', 'Dokumen pendukung terkait', 3, 1),
(10, 4, 'SP.1', '[CONTOH] Perangkat pembelajaran disusun setiap semester.', 'Dokumen pendukung terkait', 1, 1),
(11, 4, 'SP.2', '[CONTOH] Pembelajaran dilaksanakan sesuai modul ajar.', 'Dokumen pendukung terkait', 2, 1),
(12, 4, 'SP.3', '[CONTOH] Supervisi pembelajaran dilaksanakan terjadwal.', 'Dokumen pendukung terkait', 3, 1),
(13, 5, 'SPN.1', '[CONTOH] Rencana penilaian sesuai tujuan pembelajaran.', 'Dokumen pendukung terkait', 1, 1),
(14, 5, 'SPN.2', '[CONTOH] Hasil penilaian dilaporkan kepada orang tua.', 'Dokumen pendukung terkait', 2, 1),
(15, 5, 'SPN.3', '[CONTOH] Analisis hasil penilaian ditindaklanjuti.', 'Dokumen pendukung terkait', 3, 1),
(16, 6, 'SPTK.1', '[CONTOH] Kualifikasi pendidik sesuai bidang tugas.', 'Dokumen pendukung terkait', 1, 1),
(17, 6, 'SPTK.2', '[CONTOH] Program pengembangan profesional tersedia.', 'Dokumen pendukung terkait', 2, 1),
(18, 6, 'SPTK.3', '[CONTOH] Evaluasi kinerja pendidik dilakukan berkala.', 'Dokumen pendukung terkait', 3, 1),
(19, 7, 'SSP.1', '[CONTOH] Ruang kelas memadai dan layak.', 'Dokumen pendukung terkait', 1, 1),
(20, 7, 'SSP.2', '[CONTOH] Fasilitas ibadah dan sanitasi terpelihara.', 'Dokumen pendukung terkait', 2, 1),
(21, 7, 'SSP.3', '[CONTOH] Sarana TIK mendukung pembelajaran.', 'Dokumen pendukung terkait', 3, 1),
(22, 8, 'SPG.1', '[CONTOH] Rencana kerja sekolah tersusun dan dievaluasi.', 'Dokumen pendukung terkait', 1, 1),
(23, 8, 'SPG.2', '[CONTOH] Struktur organisasi dan pembagian tugas jelas.', 'Dokumen pendukung terkait', 2, 1),
(24, 8, 'SPG.3', '[CONTOH] Sistem informasi sekolah dimanfaatkan.', 'Dokumen pendukung terkait', 3, 1),
(25, 9, 'SPB.1', '[CONTOH] Rencana anggaran sekolah disusun dan disetujui.', 'Dokumen pendukung terkait', 1, 1),
(26, 9, 'SPB.2', '[CONTOH] Laporan keuangan transparan dan akuntabel.', 'Dokumen pendukung terkait', 2, 1),
(27, 9, 'SPB.3', '[CONTOH] Pembiayaan mendukung program mutu.', 'Dokumen pendukung terkait', 3, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `standar_mutu`
--

CREATE TABLE `standar_mutu` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `standar_mutu`
--

INSERT INTO `standar_mutu` (`id`, `kode`, `nama`, `deskripsi`, `urutan`, `is_aktif`) VALUES
(1, 'SKL', 'Standar Kompetensi Lulusan', NULL, 1, 1),
(2, 'SI', 'Standar Isi', NULL, 2, 1),
(3, 'SPS', 'Standar Pembinaan Siswa', NULL, 3, 1),
(4, 'SP', 'Standar Proses', NULL, 4, 1),
(5, 'SPN', 'Standar Penilaian', NULL, 5, 1),
(6, 'SPTK', 'Standar Pendidik dan Tenaga Kependidikan', NULL, 6, 1),
(7, 'SSP', 'Standar Sarana dan Prasarana', NULL, 7, 1),
(8, 'SPG', 'Standar Pengelolaan', NULL, 8, 1),
(9, 'SPB', 'Standar Pembiayaan', NULL, 9, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `standar_penilaian`
--

CREATE TABLE `standar_penilaian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `butir_id` int(10) UNSIGNED NOT NULL,
  `status` enum('belum','sebagian','terpenuhi','tidak_terpenuhi') NOT NULL DEFAULT 'belum',
  `skor` decimal(5,2) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `diisi_oleh` int(10) UNSIGNED DEFAULT NULL,
  `diverifikasi_oleh` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(20) NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `nama`, `tgl_mulai`, `tgl_selesai`, `is_aktif`, `created_at`) VALUES
(1, '2026-2027', '2026-07-13', '2027-06-26', 1, '2026-11-06 00:13:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit_sekolah`
--

CREATE TABLE `unit_sekolah` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `jenjang` enum('YAYASAN','TK','SD','SMP','SMA') NOT NULL,
  `npsn` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kelurahan` varchar(80) DEFAULT NULL,
  `kecamatan` varchar(80) DEFAULT NULL,
  `kab_kota` varchar(80) DEFAULT NULL,
  `provinsi` varchar(80) DEFAULT NULL,
  `telepon` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unit_sekolah`
--

INSERT INTO `unit_sekolah` (`id`, `kode`, `nama`, `jenjang`, `npsn`, `alamat`, `kelurahan`, `kecamatan`, `kab_kota`, `provinsi`, `telepon`, `email`, `website`, `logo`, `urutan`, `is_aktif`, `created_at`, `deleted_at`) VALUES
(1, 'YBICA', 'Yayasan Bina Insani Cahaya Abadi', 'YAYASAN', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/yayasan.png', 1, 1, '2026-11-06 00:13:44', NULL),
(2, 'TKIT1', 'TKIT Bina Insani', 'TK', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/tkit.png', 2, 1, '2026-11-06 00:13:44', NULL),
(3, 'TKIT2', 'TKIT Bina Insani 2', 'TK', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/unit-tkit2_98e888fdbf1c.png', 3, 1, '2026-11-06 00:13:44', NULL),
(4, 'SDIT1', 'SDIT Bina Insani', 'SD', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/sdit.png', 4, 1, '2026-11-06 00:13:44', NULL),
(5, 'SDIT2', 'SDIT Bina Insani 2', 'SD', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/sdit2.png', 5, 1, '2026-11-06 00:13:44', NULL),
(6, 'SMPIT', 'SMPIT Al Masykar Bina Insani', 'SMP', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/smpit.png', 6, 1, '2026-11-06 00:13:44', NULL),
(7, 'SMAIT', 'SMAIT Bina Insani', 'SMA', NULL, NULL, NULL, NULL, NULL, 'Banten', NULL, NULL, NULL, 'assets/img/logo/smait.png', 7, 1, '2026-11-06 00:13:44', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `role` enum('admin','ketua_bidang','kepala_sekolah','wakil_kepala','guru') NOT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `guru_id` int(10) UNSIGNED DEFAULT NULL,
  `tanda_tangan` varchar(255) DEFAULT NULL,
  `must_change_pw` tinyint(1) NOT NULL DEFAULT 1,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `nama_lengkap`, `role`, `unit_id`, `guru_id`, `tanda_tangan`, `must_change_pw`, `is_aktif`, `last_login_at`, `created_at`, `deleted_at`) VALUES
(1, 'admin', NULL, '$argon2id$v=19$m=65536,t=4,p=1$UnR2cEM4RmVGNjh3akJxRw$YixPbAy5hXuN9RA37TNpOh0PW0+1HvWU1JX0UrrKRY8', 'Administrator', 'admin', NULL, NULL, 'ttd/1_47ad4aa88bf6d4b6.png', 0, 1, '2026-11-06 10:35:23', '2026-11-06 00:13:46', NULL),
(2, 'ketuabidang', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Ketua Bidang Akademik (Contoh)', 'ketua_bidang', NULL, NULL, NULL, 0, 1, NULL, '2026-11-06 00:13:49', NULL),
(3, 'kepsek.tkit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Eko Nugroho', 'kepala_sekolah', 2, 1, NULL, 0, 1, NULL, '2026-11-06 00:13:50', NULL),
(4, 'wakasek.tkit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Siti Pratama', 'wakil_kepala', 2, 2, NULL, 0, 1, NULL, '2026-11-06 00:13:50', NULL),
(5, 'guru3.tkit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Budi Saputra', 'guru', 2, 3, NULL, 0, 1, NULL, '2026-11-06 00:13:50', NULL),
(6, 'guru4.tkit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Citra Nugroho', 'guru', 2, 4, NULL, 0, 1, NULL, '2026-11-06 00:13:50', NULL),
(7, 'guru5.tkit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Hana Pratama', 'guru', 2, 5, NULL, 0, 1, NULL, '2026-11-06 00:13:50', NULL),
(8, 'guru6.tkit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Taufik Pratama', 'guru', 2, 6, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(9, 'kepsek.tkit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Eko Rahmawati', 'kepala_sekolah', 3, 7, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(10, 'wakasek.tkit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Taufik Rahmawati', 'wakil_kepala', 3, 8, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(11, 'guru9.tkit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Taufik Utami', 'guru', 3, 9, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(12, 'guru10.tkit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Dewi Saputra', 'guru', 3, 10, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(13, 'guru11.tkit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Budi Utami', 'guru', 3, 11, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(14, 'guru12.tkit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Wahyu Saputra', 'guru', 3, 12, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(15, 'kepsek.sdit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Fitri Permata', 'kepala_sekolah', 4, 13, NULL, 0, 1, NULL, '2026-11-06 00:13:51', NULL),
(16, 'wakasek.sdit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Rizky Kusuma', 'wakil_kepala', 4, 14, NULL, 0, 1, NULL, '2026-11-06 00:13:52', NULL),
(17, 'guru15.sdit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Oki Rahmawati', 'guru', 4, 15, NULL, 0, 1, NULL, '2026-11-06 00:13:52', NULL),
(18, 'guru16.sdit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Rizky Nugroho', 'guru', 4, 16, NULL, 0, 1, NULL, '2026-11-06 00:13:52', NULL),
(19, 'guru17.sdit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Khoirul Lestari', 'guru', 4, 17, NULL, 0, 1, NULL, '2026-11-06 00:13:52', NULL),
(20, 'guru18.sdit1', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Budi Ramadhan', 'guru', 4, 18, NULL, 0, 1, NULL, '2026-11-06 00:13:52', NULL),
(21, 'kepsek.sdit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Budi Permata', 'kepala_sekolah', 5, 19, NULL, 0, 1, NULL, '2026-11-06 00:13:52', NULL),
(22, 'wakasek.sdit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Taufik Ramadhan', 'wakil_kepala', 5, 20, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(23, 'guru21.sdit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Yusuf Nugroho', 'guru', 5, 21, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(24, 'guru22.sdit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Oki Hidayat', 'guru', 5, 22, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(25, 'guru23.sdit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Dewi Kusuma', 'guru', 5, 23, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(26, 'guru24.sdit2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Jihan Lestari', 'guru', 5, 24, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(27, 'kepsek.smpit', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Eko Pratama', 'kepala_sekolah', 6, 25, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(28, 'wakasek.smpit', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Hana Ramadhan', 'wakil_kepala', 6, 26, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(29, 'guru27.smpit', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Putri Utami', 'guru', 6, 27, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(30, 'guru28.smpit', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Jihan Santoso', 'guru', 6, 28, NULL, 0, 1, NULL, '2026-11-06 00:13:53', NULL),
(31, 'guru29.smpit', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Siti Hidayat', 'guru', 6, 29, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(32, 'guru30.smpit', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Yusuf Saputra', 'guru', 6, 30, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(33, 'kepsek.smait', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Oki Lestari', 'kepala_sekolah', 7, 31, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(34, 'wakasek.smait', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Umi Santoso', 'wakil_kepala', 7, 32, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(35, 'guru33.smait', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Taufik Lestari', 'guru', 7, 33, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(36, 'guru34.smait', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Citra Wijaya', 'guru', 7, 34, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(37, 'guru35.smait', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Vina Rahmawati', 'guru', 7, 35, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL),
(38, 'guru36.smait', NULL, '$argon2id$v=19$m=65536,t=4,p=1$SXAwamluWlZsb2kzNlQ3Lg$idglNEMnU+KQi0+H2doJ/GtnIztKWhLr0Jm61MHwDrc', 'Lina Kusuma', 'guru', 7, 36, NULL, 0, 1, NULL, '2026-11-06 00:13:54', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_token`
--

CREATE TABLE `user_token` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `selector` char(24) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_token`
--

INSERT INTO `user_token` (`id`, `user_id`, `selector`, `token_hash`, `user_agent`, `ip_address`, `expires_at`, `created_at`, `last_used_at`) VALUES
(14, 1, '1d8ccbe2abc41e741a3c53d8', '1bb054d90bb62e1164ca554297cbae0a91290ba8c8df3e83960b412f9dfeb3f7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '::1', '2026-12-06 10:35:23', '2026-11-06 03:35:23', '2026-11-06 10:35:23');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_al_user` (`user_id`,`created_at`),
  ADD KEY `idx_al_tabel` (`tabel`,`record_id`);

--
-- Indeks untuk tabel `backup_log`
--
ALTER TABLE `backup_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `bukti_file`
--
ALTER TABLE `bukti_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bf_ref` (`ref_tipe`,`ref_id`),
  ADD KEY `idx_bf_unit` (`unit_id`,`semester_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `diunggah_oleh` (`diunggah_oleh`),
  ADD KEY `diverifikasi_oleh` (`diverifikasi_oleh`);

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_guru_nama` (`nama`);

--
-- Indeks untuk tabel `guru_penempatan`
--
ALTER TABLE `guru_penempatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_gp` (`guru_id`,`tahun_ajaran_id`),
  ADD KEY `idx_gp_unit` (`unit_id`,`tahun_ajaran_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `instrumen`
--
ALTER TABLE `instrumen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ins_kode` (`kode`);

--
-- Indeks untuk tabel `instrumen_butir`
--
ALTER TABLE `instrumen_butir`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ib` (`instrumen_id`,`kelompok_id`),
  ADD KEY `kelompok_id` (`kelompok_id`);

--
-- Indeks untuk tabel `instrumen_kelompok`
--
ALTER TABLE `instrumen_kelompok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ik` (`instrumen_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indeks untuk tabel `instrumen_opsi`
--
ALTER TABLE `instrumen_opsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instrumen_id` (`instrumen_id`);

--
-- Indeks untuk tabel `jadwal_supervisi`
--
ALTER TABLE `jadwal_supervisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_js` (`unit_id`,`semester_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `instrumen_id` (`instrumen_id`);

--
-- Indeks untuk tabel `kategori_capaian`
--
ALTER TABLE `kategori_capaian`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kelas` (`unit_id`,`tahun_ajaran_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `wali_guru_id` (`wali_guru_id`);

--
-- Indeks untuk tabel `kuesioner_jawaban`
--
ALTER TABLE `kuesioner_jawaban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kj` (`respon_id`,`butir_id`),
  ADD KEY `butir_id` (`butir_id`);

--
-- Indeks untuk tabel `kuesioner_kode`
--
ALTER TABLE `kuesioner_kode`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kk_slug` (`slug`),
  ADD KEY `idx_kk` (`unit_id`,`semester_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `kuesioner_respon`
--
ALTER TABLE `kuesioner_respon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kr` (`unit_id`,`semester_id`,`instrumen_id`),
  ADD KEY `instrumen_id` (`instrumen_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `kode_id` (`kode_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `login_attempt`
--
ALTER TABLE `login_attempt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_la` (`ip_address`,`created_at`),
  ADD KEY `idx_la_user` (`username`,`created_at`);

--
-- Indeks untuk tabel `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mapel_unit` (`unit_id`);

--
-- Indeks untuk tabel `mapel_semester`
--
ALTER TABLE `mapel_semester`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ms` (`mapel_id`,`semester_id`,`tingkat`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`kunci`);

--
-- Indeks untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pkks` (`modul`,`instrumen_id`,`semester_id`,`kepsek_user_id`,`jenis_penilai`),
  ADD KEY `idx_pn` (`modul`,`unit_id`,`semester_id`),
  ADD KEY `idx_pn_guru` (`guru_id`,`semester_id`),
  ADD KEY `idx_pn_sesi` (`sesi_kode`),
  ADD KEY `instrumen_id` (`instrumen_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `kepsek_user_id` (`kepsek_user_id`),
  ADD KEY `penilai_id` (`penilai_id`),
  ADD KEY `jadwal_id` (`jadwal_id`),
  ADD KEY `mapel_semester_id` (`mapel_semester_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `penilaian_detail`
--
ALTER TABLE `penilaian_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pd` (`penilaian_id`,`butir_id`),
  ADD KEY `butir_id` (`butir_id`),
  ADD KEY `opsi_id` (`opsi_id`);

--
-- Indeks untuk tabel `penugasan_guru`
--
ALTER TABLE `penugasan_guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pg` (`guru_id`,`kelas_id`,`mapel_semester_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `mapel_semester_id` (`mapel_semester_id`);

--
-- Indeks untuk tabel `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sem` (`tahun_ajaran_id`,`nama`);

--
-- Indeks untuk tabel `standar_butir`
--
ALTER TABLE `standar_butir`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sb` (`standar_id`);

--
-- Indeks untuk tabel `standar_mutu`
--
ALTER TABLE `standar_mutu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sm_kode` (`kode`);

--
-- Indeks untuk tabel `standar_penilaian`
--
ALTER TABLE `standar_penilaian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sp` (`unit_id`,`semester_id`,`butir_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `butir_id` (`butir_id`),
  ADD KEY `diisi_oleh` (`diisi_oleh`),
  ADD KEY `diverifikasi_oleh` (`diverifikasi_oleh`);

--
-- Indeks untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ta_nama` (`nama`);

--
-- Indeks untuk tabel `unit_sekolah`
--
ALTER TABLE `unit_sekolah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_unit_kode` (`kode`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user` (`username`),
  ADD KEY `idx_user_role` (`role`,`unit_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indeks untuk tabel `user_token`
--
ALTER TABLE `user_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_selector` (`selector`),
  ADD KEY `idx_token_exp` (`expires_at`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `backup_log`
--
ALTER TABLE `backup_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `bukti_file`
--
ALTER TABLE `bukti_file`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `guru_penempatan`
--
ALTER TABLE `guru_penempatan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `instrumen`
--
ALTER TABLE `instrumen`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `instrumen_butir`
--
ALTER TABLE `instrumen_butir`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT untuk tabel `instrumen_kelompok`
--
ALTER TABLE `instrumen_kelompok`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `instrumen_opsi`
--
ALTER TABLE `instrumen_opsi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `jadwal_supervisi`
--
ALTER TABLE `jadwal_supervisi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori_capaian`
--
ALTER TABLE `kategori_capaian`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_jawaban`
--
ALTER TABLE `kuesioner_jawaban`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_kode`
--
ALTER TABLE `kuesioner_kode`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_respon`
--
ALTER TABLE `kuesioner_respon`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `login_attempt`
--
ALTER TABLE `login_attempt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `mapel_semester`
--
ALTER TABLE `mapel_semester`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penilaian_detail`
--
ALTER TABLE `penilaian_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penugasan_guru`
--
ALTER TABLE `penugasan_guru`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT untuk tabel `semester`
--
ALTER TABLE `semester`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `standar_butir`
--
ALTER TABLE `standar_butir`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `standar_mutu`
--
ALTER TABLE `standar_mutu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `standar_penilaian`
--
ALTER TABLE `standar_penilaian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `unit_sekolah`
--
ALTER TABLE `unit_sekolah`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `user_token`
--
ALTER TABLE `user_token`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bukti_file`
--
ALTER TABLE `bukti_file`
  ADD CONSTRAINT `bukti_file_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `bukti_file_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bukti_file_ibfk_3` FOREIGN KEY (`diunggah_oleh`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bukti_file_ibfk_4` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `guru_penempatan`
--
ALTER TABLE `guru_penempatan`
  ADD CONSTRAINT `guru_penempatan_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guru_penempatan_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`),
  ADD CONSTRAINT `guru_penempatan_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`);

--
-- Ketidakleluasaan untuk tabel `instrumen_butir`
--
ALTER TABLE `instrumen_butir`
  ADD CONSTRAINT `instrumen_butir_ibfk_1` FOREIGN KEY (`instrumen_id`) REFERENCES `instrumen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instrumen_butir_ibfk_2` FOREIGN KEY (`kelompok_id`) REFERENCES `instrumen_kelompok` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `instrumen_kelompok`
--
ALTER TABLE `instrumen_kelompok`
  ADD CONSTRAINT `instrumen_kelompok_ibfk_1` FOREIGN KEY (`instrumen_id`) REFERENCES `instrumen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instrumen_kelompok_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `instrumen_kelompok` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `instrumen_opsi`
--
ALTER TABLE `instrumen_opsi`
  ADD CONSTRAINT `instrumen_opsi_ibfk_1` FOREIGN KEY (`instrumen_id`) REFERENCES `instrumen` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_supervisi`
--
ALTER TABLE `jadwal_supervisi`
  ADD CONSTRAINT `jadwal_supervisi_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `jadwal_supervisi_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  ADD CONSTRAINT `jadwal_supervisi_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`),
  ADD CONSTRAINT `jadwal_supervisi_ibfk_4` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `jadwal_supervisi_ibfk_5` FOREIGN KEY (`instrumen_id`) REFERENCES `instrumen` (`id`);

--
-- Ketidakleluasaan untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `kelas_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`),
  ADD CONSTRAINT `kelas_ibfk_3` FOREIGN KEY (`wali_guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kuesioner_jawaban`
--
ALTER TABLE `kuesioner_jawaban`
  ADD CONSTRAINT `kuesioner_jawaban_ibfk_1` FOREIGN KEY (`respon_id`) REFERENCES `kuesioner_respon` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kuesioner_jawaban_ibfk_2` FOREIGN KEY (`butir_id`) REFERENCES `instrumen_butir` (`id`);

--
-- Ketidakleluasaan untuk tabel `kuesioner_kode`
--
ALTER TABLE `kuesioner_kode`
  ADD CONSTRAINT `kuesioner_kode_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `kuesioner_kode_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  ADD CONSTRAINT `kuesioner_kode_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kuesioner_respon`
--
ALTER TABLE `kuesioner_respon`
  ADD CONSTRAINT `kuesioner_respon_ibfk_1` FOREIGN KEY (`instrumen_id`) REFERENCES `instrumen` (`id`),
  ADD CONSTRAINT `kuesioner_respon_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `kuesioner_respon_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  ADD CONSTRAINT `kuesioner_respon_ibfk_4` FOREIGN KEY (`kode_id`) REFERENCES `kuesioner_kode` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kuesioner_respon_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `mapel_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`);

--
-- Ketidakleluasaan untuk tabel `mapel_semester`
--
ALTER TABLE `mapel_semester`
  ADD CONSTRAINT `mapel_semester_ibfk_1` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mapel_semester_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`instrumen_id`) REFERENCES `instrumen` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_4` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penilaian_ibfk_5` FOREIGN KEY (`kepsek_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penilaian_ibfk_6` FOREIGN KEY (`penilai_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_7` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_supervisi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penilaian_ibfk_8` FOREIGN KEY (`mapel_semester_id`) REFERENCES `mapel_semester` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penilaian_ibfk_9` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penilaian_detail`
--
ALTER TABLE `penilaian_detail`
  ADD CONSTRAINT `penilaian_detail_ibfk_1` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penilaian_detail_ibfk_2` FOREIGN KEY (`butir_id`) REFERENCES `instrumen_butir` (`id`),
  ADD CONSTRAINT `penilaian_detail_ibfk_3` FOREIGN KEY (`opsi_id`) REFERENCES `instrumen_opsi` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penugasan_guru`
--
ALTER TABLE `penugasan_guru`
  ADD CONSTRAINT `penugasan_guru_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penugasan_guru_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penugasan_guru_ibfk_3` FOREIGN KEY (`mapel_semester_id`) REFERENCES `mapel_semester` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `semester`
--
ALTER TABLE `semester`
  ADD CONSTRAINT `semester_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `standar_butir`
--
ALTER TABLE `standar_butir`
  ADD CONSTRAINT `standar_butir_ibfk_1` FOREIGN KEY (`standar_id`) REFERENCES `standar_mutu` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `standar_penilaian`
--
ALTER TABLE `standar_penilaian`
  ADD CONSTRAINT `standar_penilaian_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`),
  ADD CONSTRAINT `standar_penilaian_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  ADD CONSTRAINT `standar_penilaian_ibfk_3` FOREIGN KEY (`butir_id`) REFERENCES `standar_butir` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `standar_penilaian_ibfk_4` FOREIGN KEY (`diisi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `standar_penilaian_ibfk_5` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_sekolah` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `user_token`
--
ALTER TABLE `user_token`
  ADD CONSTRAINT `user_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
