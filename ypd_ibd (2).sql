-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Okt 2025 pada 06.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ypd_ibd`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `KurangiSisaCuti` (IN `p_kode_karyawan` VARCHAR(20), IN `p_jenis_cuti` VARCHAR(50), IN `p_tanggal_mulai` DATE, IN `p_tanggal_akhir` DATE)   BEGIN
    DECLARE jumlah_hari INT DEFAULT 0;
    DECLARE sisa_tahunan INT DEFAULT 0;
    DECLARE sisa_lustrum INT DEFAULT 0;
    DECLARE tgl DATE;
    
    -- Hitung hari kerja (Senin-Jumat)
    SET tgl = p_tanggal_mulai;
    WHILE tgl <= p_tanggal_akhir DO
        IF DAYOFWEEK(tgl) BETWEEN 2 AND 6 THEN
            SET jumlah_hari = jumlah_hari + 1;
        END IF;
        SET tgl = DATE_ADD(tgl, INTERVAL 1 DAY);
    END WHILE;
    
    -- Ambil sisa cuti
    SELECT sisa_cuti_tahunan, sisa_cuti_lustrum 
    INTO sisa_tahunan, sisa_lustrum
    FROM data_karyawan 
    WHERE kode_karyawan = p_kode_karyawan;
    
    -- Proses pengurangan
    IF p_jenis_cuti = 'Tahunan' THEN
        IF sisa_tahunan >= jumlah_hari THEN
            UPDATE data_karyawan 
            SET sisa_cuti_tahunan = sisa_cuti_tahunan - jumlah_hari
            WHERE kode_karyawan = p_kode_karyawan;
        END IF;
        
    ELSEIF p_jenis_cuti = 'Lustrum' THEN
        IF sisa_lustrum >= jumlah_hari THEN
            UPDATE data_karyawan 
            SET sisa_cuti_lustrum = sisa_cuti_lustrum - jumlah_hari
            WHERE kode_karyawan = p_kode_karyawan;
        END IF;
        
    -- Untuk Sakit dan Khusus, tidak ada pengurangan
    END IF;
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_karyawan`
--

CREATE TABLE `data_karyawan` (
  `id_karyawan` int(11) NOT NULL,
  `kode_karyawan` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `divisi` varchar(50) NOT NULL,
  `role` enum('karyawan','direktur','admin','penanggung jawab') NOT NULL DEFAULT 'karyawan',
  `no_telp` varchar(20) DEFAULT NULL,
  `sisa_cuti_tahunan` int(3) DEFAULT 0,
  `sisa_cuti_lustrum` int(3) DEFAULT 0,
  `status_aktif` enum('aktif','non_aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_karyawan`
--

INSERT INTO `data_karyawan` (`id_karyawan`, `kode_karyawan`, `nama_lengkap`, `email`, `password`, `jabatan`, `divisi`, `role`, `no_telp`, `sisa_cuti_tahunan`, `sisa_cuti_lustrum`, `status_aktif`, `created_at`) VALUES
(1, 'YPD001', 'Pico', 'pico.dir@ypd.com', 'hashed_password_direktur', 'Direktur Utama', 'Direksi', 'direktur', '081234567890', 4, 4, 'aktif', '2025-09-30 23:37:17'),
(2, 'YPD002', 'Admin', 'cell.sdm@ypd.com', 'hashed_password_admin', 'Administrator', '', 'admin', '081234567891', 0, 0, 'aktif', '2025-09-30 23:37:17'),
(3, 'YPD010', 'Adrian', 'adrian.karyawan@ypd.com', 'hashed_password_karyawan', 'Staf Training', 'Training', 'karyawan', '081234567892', 12, 2, 'aktif', '2025-09-30 23:37:17'),
(4, 'YPD003', 'Ria', 'ria.direksi@ypd.com', 'hashed_password_ria', 'Penanggung Jawab Training', 'Training', 'penanggung jawab', '081234567893', 12, 5, 'aktif', '2025-09-30 23:45:32'),
(5, 'YPD004', 'Dani', 'dani.pj@ypd.com', 'hashed_password_dani', 'Staf Keuangan', 'Keuangan', 'karyawan', '081234567894', 12, 5, 'aktif', '2025-09-30 23:45:32'),
(6, 'YPD005', 'Budi', 'budibudi@gmail.com', 'hashed_password_budi', 'Penanggung Jawab Konsultasi', 'Konsultasi', 'penanggung jawab', '12345677654', 12, 5, 'aktif', '2025-10-02 08:30:16'),
(7, 'YPD006', 'Cica', 'cica@ypd.com', 'hashed_password_cica', 'Penanggung Jawab Wisma', 'Wisma', 'penanggung jawab', '085918347914', 12, 5, 'aktif', '2025-10-02 14:01:16'),
(8, 'YPD007', 'Dian', 'didi@gmail.com', 'hashed_password_dian', 'Penanggung Jawab SDM', 'SDM', 'penanggung jawab', '5981731413', 12, 5, 'aktif', '2025-10-02 15:10:28'),
(9, 'YPD008', 'Jasmine', 'minminja@gmail.com', 'hashed_password_jasmine', 'Penanggung Jawab Sekretariat', 'Sekretariat', 'penanggung jawab', '123415654312', 12, 5, 'aktif', '2025-10-02 15:24:59'),
(10, 'YPD009', 'Mega', 'gamega@gmail.com', 'hashed_password_mega', 'Penanggung Jawab Keuangan', 'Keuangan', 'penanggung jawab', '584937383', 12, 5, 'aktif', '2025-10-02 15:46:27'),
(11, 'YPD011', 'Lala Marcella', 'lala.marcella@ypd.com', 'hashed_password_lala', 'Staf Keuangan', 'Keuangan', 'karyawan', '08186845699', 4, 0, 'aktif', '2025-10-04 05:27:28'),
(12, 'YPD012', 'Hezkiel', 'hezkiel@ypd.com', 'hashed_password_hezkiel', 'Staf Sekretariat', 'Sekretariat', 'karyawan', '08186889777', 12, 0, 'aktif', '2025-10-04 06:13:54'),
(13, 'YPD013', 'Adelia', 'adelia@ypd.com', 'hashed_password_adelia', 'Staf Sekretariat', 'Sekretariat', 'karyawan', '08186889755', 12, 0, 'aktif', '2025-10-04 06:15:55'),
(14, 'YPD014', 'Cici', 'cici@ypd.com', 'hashed_password_cici', 'Staf Keuangan', 'Keuangan', 'karyawan', '081868456987', 11, 2, 'aktif', '2025-10-04 06:18:48'),
(15, 'YPD015', 'Leonardo', 'leonardo@ypd.com', 'hashed_password_leonardo', 'Staf Training', 'Training', 'karyawan', '08186889751', 6, 0, 'aktif', '2025-10-04 06:26:05'),
(16, 'YPD016', 'Naomi', 'naomi@ypd.com', 'hashed_password_naomi', 'Staf Konsultasi', 'Konsultasi', 'karyawan', '081868456987', 0, 0, 'aktif', '2025-10-04 06:30:39'),
(17, 'YPD017', 'Aurora', 'aurora@ypd.com', 'hashed_password_aurora', 'Staf Konsultasi', 'Konsultasi', 'karyawan', '081868456988', 0, 0, 'aktif', '2025-10-04 06:35:02'),
(18, 'YPD018', 'Selena', 'selena@ypd.com', 'hashed_password_selena', 'Staf Wisma', 'Wisma', 'karyawan', '081868456978', 0, 0, 'aktif', '2025-10-04 06:38:26'),
(19, 'YPD019', 'Kelra', 'kelra@ypd.com', 'hashed_password_kelra', 'Staf Wisma', 'Wisma', 'karyawan', '081868456985', 0, 0, 'aktif', '2025-10-04 06:46:13'),
(20, 'YPD020', 'Lyra', 'lyra@ypd.com', 'hashed_password_lyra', 'Staf SDM', 'SDM', 'karyawan', '081868456987', 5, 0, 'aktif', '2025-10-04 06:47:18'),
(21, 'YPD021', 'Yovan', 'yovan@ypd.com', 'hashed_password_yovan', 'Staf SDM', 'SDM', 'karyawan', '081868456985', 2, 0, 'aktif', '2025-10-04 06:48:16'),
(22, 'YPD022', 'Marcella', 'marcella@ypd.com', 'hashed_password_marcella', 'Staf Keuangan', 'Keuangan', 'karyawan', '081868456988', 0, 0, 'aktif', '2025-10-12 05:21:51'),
(23, 'YPD023', 'Sasa', 'sasa@ypd.com', 'hashed_password_sasa', 'Staf SDM', 'SDM', 'karyawan', '081868456988', 0, 0, 'aktif', '2025-10-13 17:01:55'),
(24, 'YPD024', 'Nindya', 'nindya@ypd.com', 'hashed_password_nindya', 'Staf SDM', 'SDM', 'karyawan', '081868456989', 0, 0, 'aktif', '2025-10-14 13:12:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_pelamar`
--

CREATE TABLE `data_pelamar` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `posisi_dilamar` varchar(100) DEFAULT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `alamat_rumah` text DEFAULT NULL,
  `alamat_domisili` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `kontak_darurat` varchar(50) DEFAULT NULL,
  `pendidikan_terakhir` varchar(50) DEFAULT NULL,
  `surat_lamaran` varchar(255) DEFAULT NULL,
  `cv` varchar(255) DEFAULT NULL,
  `photo_formal` varchar(255) DEFAULT NULL,
  `ijazah_transkrip` varchar(255) DEFAULT NULL,
  `berkas_pendukung` varchar(255) DEFAULT NULL,
  `ktp` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Menunggu Proses',
  `status_seleksi` varchar(100) DEFAULT 'Proses seleksi sedang berlangsung',
  `pengumuman` text DEFAULT 'Belum ada pengumuman saat ini.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_pelamar`
--

INSERT INTO `data_pelamar` (`id`, `user_id`, `nama_lengkap`, `posisi_dilamar`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `nik`, `alamat_rumah`, `alamat_domisili`, `no_telp`, `email`, `agama`, `kontak_darurat`, `pendidikan_terakhir`, `surat_lamaran`, `cv`, `photo_formal`, `ijazah_transkrip`, `berkas_pendukung`, `ktp`, `status`, `status_seleksi`, `pengumuman`, `created_at`) VALUES
(1, 10, 'Lala Marcella', 'Keuangan', 'Perempuan', 'Tegal', '2006-03-10', '1122334455', 'Jl. Residence Tegal', 'Jl. Pawiyatan Luhur II Semarang', '08186845698', 'marcellaadelia10@gmail.com', 'Katholik', '08125647895', 'Diploma', 'uploads/1759506116-Abstaksi.docx', 'uploads/1759506116-Abstaksi.pdf', 'uploads/1759506116-Abstaksi.docx', 'uploads/1759506116-Abstaksi.pdf', 'uploads/1759506116-Abstaksi.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 15:41:56'),
(2, 11, 'Andrianna Khim', 'Training', 'Perempuan', 'Jakarta', '2000-12-12', '1234512345', 'Jl. Mawar Jakarta', 'Jl. Pawiyatan Luhur Semarang', '08186889755', 'andrianna123@gmail.com', 'Buddha', '08125896285', 'Diploma', 'uploads/1759506884-Abstaksi.docx', 'uploads/1759506884-Abstaksi.docx', 'uploads/1759506884-Abstaksi.docx', 'uploads/1759506884-Abstaksi.docx', 'uploads/1759506884-Abstaksi.docx', NULL, 'Tidak Lolos', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 15:54:44'),
(3, 12, 'Alexander', 'Wisma', 'Laki-laki', 'Jakarta', '2001-12-11', '1234512347', 'Jl. Anggrek Jakarta', 'Jl. Pawiyatan Luhur Semarang', '08186889777', 'alexander000@gmail.com', 'Kristen', '081256478987', 'Diploma', 'uploads/1759507918-Abstaksi - Copy.docx', 'uploads/1759507918-Abstaksi - Copy.docx', 'uploads/1759507918-Abstaksi - Copy.docx', 'uploads/1759507918-Abstaksi.docx', 'uploads/1759507918-Abstaksi.docx', 'uploads/1759507918-Abstaksi - Copy.docx', 'Tidak Lolos', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 16:11:58'),
(4, 13, 'Joffy An', 'Konsultasi', 'Laki-laki', 'Surabaya', '2003-03-13', '1234512348', 'Jl. Macan Surabaya', 'Jl. Pawiyatan Luhur Semarang', '081868456988', 'joffy1234@gmail.com', 'Buddha', '081258962812', 'Diploma', 'uploads/1759509151-Abstaksi.pdf', 'uploads/1759509151-Abstaksi.pdf', 'uploads/1759509151-Abstaksi.pdf', 'uploads/1759509151-Abstaksi.pdf', 'uploads/1759509151-Abstaksi.pdf', 'uploads/1759509151-Abstaksi.pdf', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 16:32:31'),
(5, 14, 'Jeffra', 'Training', 'Laki-laki', 'Tegal', '2000-12-12', '1234512347', 'Jl. Tulip Jakarta', 'Jl. Pawiyatan Luhur Semarang', '08186845698', 'jeffra1234@gmail.com', 'Khonghucu', '081256478987', 'Diploma', 'uploads/1759510277-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759510277-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759510277-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759510277-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759510277-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 16:51:17'),
(6, 15, 'Hezkiel', 'Sekretariat', 'Laki-laki', 'Jakarta', '2004-12-12', '1234512348', 'Jl. Mawar Jakarta', 'Jl. Pawiyatan Luhur Semarang', '08186889777', 'hezkiel1234@gmail.com', 'Kristen', '081258962852', 'Diploma', 'uploads/1759511213-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511213-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511213-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511213-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511213-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511213-Pernyataan_Penyelesaian_Proyek.docx', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 17:06:53'),
(7, 16, 'Adelia', 'Sekretariat', 'Perempuan', 'Tegal', '2006-03-10', '1234512345', 'Jl. Residence Tegal', 'Jl. Pawiyatan Luhur II Semarang', '08186889755', 'marcellaadelia11@gmail.com', 'Katholik', '081258962812', 'Diploma', 'uploads/1759511511-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511511-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511511-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511511-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759511511-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 17:11:51'),
(8, 17, 'Cici', 'Keuangan', 'Perempuan', 'Tegal', '2000-12-15', '1234512348', 'Jl. Mawar Jakarta', 'Jl. Pawiyatan Luhur Semarang', '081868456987', 'cici1234@gmail.com', 'Katholik', '08125896285', 'Diploma', 'uploads/1759512060-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512060-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512060-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512060-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512060-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 17:21:00'),
(9, 18, 'Lindi', 'Training', 'Perempuan', 'Surabaya', '2001-11-11', '1122334455', 'Jl. Anggrek Jakarta', 'Jl. Pawiyatan Luhur II Semarang', '081868456987', 'lindi1234@gmail.com', 'Kristen', '081258962812', 'Diploma', 'uploads/1759512673-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512673-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512673-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512673-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759512673-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Tidak Lolos', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-03 17:31:13'),
(10, 19, 'Leonardo', 'Training', 'Laki-laki', 'Semarang', '2000-10-10', '1234512349', 'Jl. Anggur Jakarta', 'Jl. Pawiyatan Luhur Semarang', '08186889751', 'leonardo123@gmail.com', 'Buddha', '081258962813', 'Diploma', 'uploads/1759559061-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559061-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559061-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559061-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559061-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:24:21'),
(11, 20, 'Naomi', 'Konsultasi', 'Perempuan', 'Tangerang', '2000-12-13', '1234512349', 'Jl. Anggrek Jakarta', 'Jl. Pawiyatan Luhur II Semarang', '081868456987', 'naomi123@gmail.com', 'Islam', '081258962854', 'Sarjana', 'uploads/1759559328-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559328-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559328-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559328-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559328-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559328-Pernyataan_Penyelesaian_Proyek.docx', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:28:48'),
(12, 21, 'Aurora', 'Konsultasi', 'Perempuan', 'Tangerang', '2000-04-12', '1234512350', 'Jl. Anggrek Tangerang', 'Jl. Pawiyatan Luhur II Semarang', '081868456988', 'aurora123@gmail.com', 'Hindu', '081256478984', 'Sarjana', 'uploads/1759559620-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559620-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559620-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559620-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559620-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559620-Pernyataan_Penyelesaian_Proyek.docx', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:33:40'),
(13, 22, 'Selena', 'Wisma', 'Perempuan', 'Yogyakarta', '2004-04-04', '1234512344', 'Jl. Musang Jakarta', 'Jl. Pawiyatan Luhur II Semarang', '081868456978', 'selena123@gmail.com', 'Kristen', '081258962812', 'Diploma', 'uploads/1759559839-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559839-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559839-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559839-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759559839-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:37:19'),
(14, 23, 'Kelra', 'Wisma', 'Laki-laki', 'Medan', '2001-02-11', '1234512347', 'Jl. Mawar Medan', 'Jl. Pawiyatan Luhur Semarang', '081868456985', 'kelra123@gmail.com', 'Khonghucu', '081256478988', 'Sarjana', 'uploads/1759560025-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560025-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560025-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560025-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560025-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:40:25'),
(15, 24, 'Lyra', 'SDM', 'Perempuan', 'Denpasar', '2005-05-05', '1122334457', 'Jl. Residence Denpasar', 'Jl. Pawiyatan Luhur II Semarang', '081868456987', 'lyra1234@gmail.com', 'Hindu', '081256478988', 'Sarjana', 'uploads/1759560177-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560177-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560177-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560177-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560177-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:42:57'),
(16, 25, 'Yovan', 'SDM', 'Laki-laki', 'Malang', '2001-02-21', '1234512346', 'Jl. Mawar Malang', 'Jl. Pawiyatan Luhur Semarang', '081868456985', 'yovan1234@gmail.com', 'Katholik', '081258962812', 'Sarjana', 'uploads/1759560300-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560300-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560300-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560300-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759560300-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-04 06:45:00'),
(17, 26, 'Feli ', 'Konsultasi', 'Perempuan', 'Tangerang', '2000-01-30', '1234512349', 'Tangerang', 'Jl. Pawiyatan Luhur Semarang', '08186889755', 'feli1234@gmail.com', 'Buddha', '081256478988', 'Sarjana', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-05 12:55:45'),
(18, 27, 'Dicky', 'Wisma', 'Laki-laki', 'Medan', '2001-11-11', '1234512349', 'Medan', 'Jl. Pawiyatan Luhur II Semarang', '08186889777', 'dicky1234@gmail.com', 'Buddha', '081256478988', 'Sarjana', 'uploads/1759853623-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759853623-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759853623-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759853623-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759853623-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759853623-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-07 16:13:43'),
(19, 28, 'Nindya', 'SDM', 'Perempuan', 'Jakarta', '2002-02-02', '1234512348', 'Jl. Anggrek Jakarta', 'Jl. Pawiyatan Luhur Semarang', '081868456988', 'nindya123@gmail.com', 'Islam', '081258962815', 'Sarjana', 'uploads/1759855931-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759855931-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759855931-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759855931-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1759855931-ChatGPT Image 5 Okt 2025, 00.02.09.png', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-07 16:52:11'),
(20, 30, 'Cece', 'Sekretariat', 'Perempuan', 'Tegal', '2006-03-10', '33587425695841', 'Tegal', 'Semarang', '084569875321', 'adeliamarcellaa@gmail.com', 'Katholik', '084569875321', 'Diploma', 'uploads/1760004826-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760004826-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760004826-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760004826-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760004826-ChatGPT Image 5 Okt 2025, 00.02.09.png', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-09 10:13:46'),
(21, 1, 'Sasa', 'SDM', 'Perempuan', 'Jakarta', '2000-12-12', '1234512348', 'Tegal', 'Tegal', '081868456988', 'marcellaadelia06@gmail.com', 'Katholik', '081258962815', 'Diploma', 'uploads/1760109541-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760109541-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760109541-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760109541-ChatGPT Image 5 Okt 2025, 00.02.09.png', 'uploads/1760109541-ChatGPT Image 5 Okt 2025, 00.02.09.png', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-10 15:19:01'),
(22, 31, 'Marcella', 'Keuangan', 'Perempuan', 'Tegal', '2006-03-10', '1122334455', 'Jl. Residence Tegal', 'Jl. Pawiyatan Luhur II Semarang', '081868456988', '24n10004@student.unika.ac.id', 'Katholik', '081256478987', 'Diploma', 'uploads/1760245662-Tabel Jarkom.docx', 'uploads/1760245662-Tabel Jarkom.docx', 'uploads/1760245662-Tabel Jarkom.docx', 'uploads/1760245662-Tabel Jarkom.docx', 'uploads/1760245662-Tabel Jarkom.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-12 05:07:42'),
(23, 32, 'Gege', 'Sekretariat', 'Laki-laki', 'Jakarta', '2004-04-14', '1234512349', 'Jl. Anggrek Jakarta', 'Jl. Pawiyatan Luhur II Semarang', '081868456987', 'gege1234@gmail.com', 'Buddha', '081256478988', 'Sarjana', 'uploads/1760448178-UTS IBD.png', 'uploads/1760448178-UTS IBD.png', 'uploads/1760448178-UTS IBD.png', 'uploads/1760448178-UTS IBD.png', 'uploads/1760448178-UTS IBD.png', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-14 13:22:58'),
(24, 34, 'aditya', 'staf keuangan', 'Laki-laki', 'Semarang', '2025-10-22', '332', 'Jl. Gombel Permai VI / 548', 'Jl. Gombel Permai VI / 548', '089647928297', 'aditdisini@gmail.com', 'Khonghucu', '333', 'SMK', 'uploads/1761100426-sertif iwak.jpg', 'uploads/1761100426-Paper PP kelompok 5.docx', 'uploads/1761100426-sertif iwak.jpg', 'uploads/1761100426-Paper PP kelompok 5.docx', 'uploads/1761100426-Paper PP kelompok 5.docx', NULL, 'Menunggu Proses', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-22 02:33:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_pengajuan_cuti`
--

CREATE TABLE `data_pengajuan_cuti` (
  `id` int(11) NOT NULL,
  `kode_karyawan` varchar(20) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(50) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `role` enum('karyawan','direktur','admin','penanggung jawab') NOT NULL,
  `jenis_cuti` varchar(50) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `file_surat_dokter` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu Persetujuan','Diterima','Ditolak') NOT NULL DEFAULT 'Menunggu Persetujuan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_persetujuan` datetime DEFAULT NULL,
  `alasan_penolakan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_pengajuan_cuti`
--

INSERT INTO `data_pengajuan_cuti` (`id`, `kode_karyawan`, `nama_karyawan`, `divisi`, `jabatan`, `role`, `jenis_cuti`, `tanggal_mulai`, `tanggal_akhir`, `alasan`, `file_surat_dokter`, `status`, `created_at`, `waktu_persetujuan`, `alasan_penolakan`) VALUES
(1, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Khusus - Menikah', '2025-10-06', '2025-10-08', NULL, NULL, 'Diterima', '2025-10-06 16:49:59', '2025-10-07 08:00:13', NULL),
(2, 'YPD015', 'Leonardo', 'Training', 'Staf Training', 'karyawan', 'Sakit', '2025-10-06', '2025-10-08', 'sakit', 'uploads/surat_sakit/1759769935_Request for Quotation.pdf', 'Diterima', '2025-10-06 16:58:55', '2025-10-07 07:47:58', NULL),
(3, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Sakit', '2025-10-07', '2025-10-09', 'nono ya', 'uploads/surat_sakit/1759816970_ChatGPT Image 5 Okt 2025, 00.02.09.png', 'Ditolak', '2025-10-07 06:02:50', '2025-10-07 08:04:59', NULL),
(4, 'YPD011', 'Lala Marcella', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Tahunan', '2025-12-28', '2026-01-04', 'mau holiday', NULL, 'Diterima', '2025-10-07 06:07:58', '2025-10-07 08:28:27', NULL),
(5, 'YPD014', 'Cici', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Lustrum', '2025-10-17', '2025-10-17', 'Mau ke luar kota', NULL, 'Diterima', '2025-10-07 06:11:46', '2025-10-07 08:27:27', NULL),
(6, 'YPD010', 'Adrian', 'Training', 'Staf Training', 'karyawan', 'Lustrum', '2025-10-27', '2025-10-27', 'Pergi 1 hari', NULL, 'Diterima', '2025-10-07 06:48:59', '2025-10-07 08:51:02', NULL),
(7, 'YPD015', 'Leonardo', 'Training', 'Staf Training', 'karyawan', 'Tahunan', '2025-11-10', '2025-11-17', 'pergi holiday ke LN hehe', NULL, 'Diterima', '2025-10-07 06:49:55', '2025-10-07 08:50:45', NULL),
(8, 'YPD003', 'Ria', 'Training', 'Penanggung Jawab Training', 'penanggung jawab', 'Tahunan', '2025-10-09', '2025-10-09', 'sakit', NULL, 'Menunggu Persetujuan', '2025-10-07 07:57:10', NULL, NULL),
(9, 'YPD020', 'Lyra', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-10-07', '2025-10-07', 'gaboleh, kerja', NULL, 'Ditolak', '2025-10-07 13:17:58', '2025-10-07 15:45:25', NULL),
(10, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-10-07', '2025-10-07', 'no no yovan', NULL, 'Ditolak', '2025-10-07 13:23:36', '2025-10-07 15:57:18', NULL),
(11, 'YPD007', 'Dian', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Tahunan', '2025-10-07', '2025-10-07', 'bobok', NULL, 'Menunggu Persetujuan', '2025-10-07 13:25:38', NULL, NULL),
(12, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-10-29', '2025-10-29', 'tidur', NULL, 'Diterima', '2025-10-07 13:59:38', '2025-10-07 16:00:04', NULL),
(13, 'YPD014', 'Cici', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Tahunan', '2025-10-07', '2025-10-07', 'wergth', NULL, 'Diterima', '2025-10-07 14:57:21', '2025-10-07 16:58:44', NULL),
(14, 'YPD018', 'Selena', 'Wisma', 'Staf Wisma', 'karyawan', 'DiluarTanggungan', '2025-10-16', '2025-10-17', 'Mau pergi ke luar kota', NULL, 'Menunggu Persetujuan', '2025-10-07 18:00:27', NULL, NULL),
(15, 'YPD011', 'Lala Marcella', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Ibadah', '2025-10-30', '2025-10-31', 'Ziarah', NULL, 'Diterima', '2025-10-07 18:04:22', '2025-10-10 17:48:39', NULL),
(16, 'YPD011', 'Lala Marcella', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Tahunan', '2025-10-11', '2025-10-12', 'tidur', NULL, 'Diterima', '2025-10-11 15:34:19', '2025-10-22 03:45:34', NULL),
(17, 'YPD014', 'Cici', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Lustrum', '2025-10-11', '2025-10-12', 'tidak dieterima', NULL, 'Ditolak', '2025-10-11 15:36:34', NULL, NULL),
(18, 'YPD009', 'Mega', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Tahunan', '2025-10-11', '2025-10-11', 'liburan', NULL, 'Menunggu Persetujuan', '2025-10-11 15:42:14', NULL, NULL),
(19, 'YPD022', 'Marcella', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Diluar Tanggungan', '2025-11-25', '2025-11-26', 'tidak boleh', NULL, 'Ditolak', '2025-10-12 05:27:50', '2025-10-12 07:35:36', NULL),
(20, 'YPD009', 'Mega', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Tahunan', '2025-10-12', '2025-10-13', 'tidak diterima karena tidak memenuhi syarat', NULL, 'Ditolak', '2025-10-12 05:38:12', NULL, NULL),
(23, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-11-04', '2025-11-05', 'no', NULL, 'Ditolak', '2025-10-12 07:15:05', '2025-10-22 05:45:38', NULL),
(24, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-12-08', '2025-12-09', 'no', NULL, 'Ditolak', '2025-10-12 07:19:27', '2025-10-22 05:29:06', NULL),
(25, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-11-27', '2025-11-28', 'pergi ke luar kota', NULL, 'Diterima', '2025-10-12 07:39:44', '2025-10-22 05:28:57', NULL),
(26, 'YPD010', 'Adrian', 'Training', 'Staf Training', 'karyawan', 'Lustrum', '2025-10-14', '2025-10-15', 'pergi', NULL, 'Diterima', '2025-10-13 16:42:47', '2025-10-13 18:43:36', NULL),
(27, 'YPD003', 'Ria', 'Training', 'Penanggung Jawab Training', 'penanggung jawab', 'Tahunan', '2025-10-29', '2025-10-30', 'ga boleh', NULL, 'Ditolak', '2025-10-13 16:44:43', NULL, NULL),
(29, 'YPD024', 'Nindya', 'SDM', 'Staf SDM', 'karyawan', 'Diluar Tanggungan', '2025-11-27', '2025-11-28', 'belum bisa', NULL, 'Ditolak', '2025-10-14 13:25:46', '2025-10-14 15:27:11', NULL),
(35, 'YPD001', 'Pico', 'Direksi', 'Direktur Utama', 'direktur', 'Lustrum', '2025-10-21', '2025-10-21', 'ya itulah pokonya', NULL, 'Diterima', '2025-10-21 04:42:11', NULL, NULL),
(36, 'YPD001', 'Pico', 'Direksi', 'Direktur Utama', 'direktur', 'Tahunan', '2025-10-21', '2025-10-21', 'bermain playground', NULL, 'Diterima', '2025-10-21 05:07:39', NULL, NULL),
(37, 'YPD009', 'Mega', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Tahunan', '2025-10-29', '2025-10-31', 'libur keluarga', NULL, 'Menunggu Persetujuan', '2025-10-22 01:36:38', NULL, NULL),
(38, 'YPD007', 'Dian', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Sakit', '2025-10-22', '2025-10-24', 'tidur', 'uploads/surat_sakit/1761103891_sertif iwak.jpg', 'Menunggu Persetujuan', '2025-10-22 03:31:31', NULL, NULL),
(39, 'YPD007', 'Dian', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Sakit', '2025-10-29', '2025-10-31', 'pergi', 'uploads/surat_sakit/1761103942_sertif iwak.jpg', 'Menunggu Persetujuan', '2025-10-22 03:32:22', NULL, NULL),
(40, 'YPD009', 'Mega', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Lustrum', '2025-11-11', '2025-11-14', 'libur', NULL, 'Menunggu Persetujuan', '2025-10-22 03:35:23', NULL, NULL),
(41, 'YPD009', 'Mega', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Lustrum', '2025-11-11', '2025-11-14', 'libur', NULL, 'Menunggu Persetujuan', '2025-10-22 03:40:59', NULL, NULL),
(42, 'YPD009', 'Mega', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Khusus - Menikah', '2025-11-25', '2025-11-27', 'menikah', NULL, 'Menunggu Persetujuan', '2025-10-22 03:41:34', NULL, NULL),
(43, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Ibadah', '2025-10-22', '2025-10-30', 'ibadah', NULL, 'Diterima', '2025-10-22 03:45:05', '2025-10-22 05:45:31', NULL),
(44, 'YPD020', 'Lyra', 'SDM', 'Staf SDM', 'karyawan', 'Tahunan', '2025-10-22', '2025-10-23', 'liburan', NULL, 'Ditolak', '2025-10-22 04:15:52', '2025-10-22 06:16:22', 'gaboleh');

--
-- Trigger `data_pengajuan_cuti`
--
DELIMITER $$
CREATE TRIGGER `after_cuti_disetujui` AFTER UPDATE ON `data_pengajuan_cuti` FOR EACH ROW BEGIN
    -- Hanya trigger ketika status berubah menjadi 'Diterima'
    IF NEW.status = 'Diterima' AND OLD.status != 'Diterima' THEN
        -- Panggil stored procedure untuk mengurangi sisa cuti
        CALL KurangiSisaCuti(
            NEW.kode_karyawan, 
            NEW.jenis_cuti, 
            NEW.tanggal_mulai, 
            NEW.tanggal_akhir
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_pengajuan_khl`
--

CREATE TABLE `data_pengajuan_khl` (
  `id_khl` int(11) NOT NULL,
  `kode_karyawan` varchar(20) NOT NULL,
  `divisi` varchar(50) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `role` enum('karyawan','direktur','admin','penanggung jawab') NOT NULL DEFAULT 'karyawan',
  `proyek` varchar(100) NOT NULL,
  `tanggal_khl` date NOT NULL,
  `jam_mulai_kerja` time NOT NULL,
  `jam_akhir_kerja` time NOT NULL,
  `tanggal_cuti_khl` date NOT NULL,
  `jam_mulai_cuti_khl` time NOT NULL,
  `jam_akhir_cuti_khl` time NOT NULL,
  `status_khl` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `alasan_penolakan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_pengajuan_khl`
--

INSERT INTO `data_pengajuan_khl` (`id_khl`, `kode_karyawan`, `divisi`, `jabatan`, `role`, `proyek`, `tanggal_khl`, `jam_mulai_kerja`, `jam_akhir_kerja`, `tanggal_cuti_khl`, `jam_mulai_cuti_khl`, `jam_akhir_cuti_khl`, `status_khl`, `alasan_penolakan`, `created_at`) VALUES
(1, 'YPD021', 'SDM', 'Staf SDM', 'karyawan', 'Projek Training SDM baru', '2025-10-06', '08:00:00', '18:00:00', '2025-10-28', '08:00:00', '17:00:00', 'disetujui', NULL, '2025-10-05 05:25:22'),
(3, 'YPD021', 'SDM', 'Staf SDM', 'karyawan', 'Projek Training Karyawan Baru', '2025-10-08', '08:00:00', '17:00:00', '2025-10-22', '08:00:00', '17:00:00', 'ditolak', 'tidak boleh!!', '2025-10-05 05:56:20'),
(4, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Projek Training SDM baru', '2025-10-06', '10:00:00', '17:00:00', '2025-10-23', '09:00:00', '18:00:00', 'pending', NULL, '2025-10-05 06:10:51'),
(5, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Projek Training SDM baru oawkok', '2025-10-15', '10:00:00', '16:00:00', '2025-10-30', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-05 06:27:52'),
(6, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'projekan2', '2025-10-06', '09:00:00', '18:00:00', '2025-11-08', '08:00:00', '17:00:00', 'ditolak', 'Tidak Boleh', '2025-10-05 06:48:08'),
(7, 'YPD001', 'Direksi', 'Direktur Utama', 'direktur', 'bandungan', '2025-11-06', '15:35:00', '16:35:00', '2025-11-06', '17:35:00', '18:35:00', 'disetujui', NULL, '2025-10-05 08:35:46'),
(12, 'YPD001', 'Direksi', '', 'direktur', 'Tugu Muda', '2025-10-07', '00:37:00', '01:37:00', '2025-10-07', '02:37:00', '03:37:00', 'disetujui', NULL, '2025-10-05 17:37:58'),
(14, 'YPD001', 'Direktsi', 'Direktur Utama', 'direktur', 'proyek baru', '2025-10-06', '01:04:00', '02:04:00', '2025-10-06', '04:04:00', '05:04:00', 'disetujui', NULL, '2025-10-05 18:05:33'),
(19, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'projek baru', '2025-10-07', '09:00:00', '18:00:00', '2025-10-22', '08:00:00', '17:00:00', 'pending', NULL, '2025-10-07 13:29:49'),
(20, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'projekprojek', '2025-10-28', '09:00:00', '17:00:00', '2025-10-29', '08:00:00', '16:00:00', 'pending', NULL, '2025-10-07 13:30:22'),
(22, 'YPD014', 'Keuangan', 'Staf Keuangan', 'karyawan', 'projeksekai', '2025-10-16', '09:00:00', '16:00:00', '2025-10-30', '08:00:00', '17:00:00', 'ditolak', 'no no', '2025-10-07 14:57:39'),
(23, 'YPD019', 'Wisma', 'Staf Wisma', 'karyawan', 'Projek Training Karyawan Baru', '2025-10-08', '08:00:00', '18:00:00', '2025-10-20', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-07 17:26:35'),
(24, 'YPD018', 'Wisma', 'Staf Wisma', 'karyawan', 'Projek Training Karyawan Wisma', '2025-10-20', '08:00:00', '18:00:00', '2025-10-30', '08:00:00', '17:00:00', 'pending', NULL, '2025-10-07 17:45:24'),
(25, 'YPD011', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Projek Training Karyawan Keuangan', '2025-10-10', '08:00:00', '18:00:00', '2025-10-15', '08:00:00', '16:00:00', 'disetujui', '', '2025-10-07 18:05:06'),
(26, 'YPD009', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'projek baru 123', '2025-10-13', '08:00:00', '17:00:00', '2025-10-24', '09:00:00', '17:00:00', 'pending', NULL, '2025-10-11 15:51:19'),
(27, 'YPD022', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Mengikuti Training Karyawan Keuangan Baru', '2025-10-20', '08:00:00', '18:00:00', '2025-11-04', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-12 05:29:38'),
(28, 'YPD009', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'Projek Training Karyawan Keuangan', '2025-10-13', '08:00:00', '16:00:00', '2025-10-20', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-12 05:39:42'),
(29, 'YPD001', 'Direksi', 'Direktur Utama', 'direktur', 'Projek Training Karyawan Baru', '2025-10-13', '08:00:00', '16:00:00', '2025-10-15', '08:00:00', '16:00:00', 'disetujui', NULL, '2025-10-12 06:21:00'),
(30, 'YPD021', 'SDM', 'Staf SDM', 'karyawan', 'Projek Training Karyawan SDM', '2025-11-17', '08:00:00', '18:00:00', '2025-11-25', '08:00:00', '16:00:00', 'disetujui', '', '2025-10-12 07:32:50'),
(31, 'YPD010', 'Training', 'Staf Training', 'karyawan', 'Projek Training Karyawan Baru', '2025-10-16', '08:00:00', '18:00:00', '2025-10-30', '08:00:00', '17:00:00', 'ditolak', 'belum bisa', '2025-10-13 16:43:05'),
(32, 'YPD003', 'Training', 'Penanggung Jawab Training', 'penanggung jawab', 'Projek Training Karyawan Baru', '2025-11-17', '08:00:00', '18:00:00', '2025-11-28', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-13 16:47:18'),
(33, 'YPD001', 'Direksi', 'Direktur Utama', 'direktur', 'Projek Training Karyawan Baru', '2025-11-18', '11:00:00', '19:00:00', '2025-12-04', '08:00:00', '16:00:00', 'disetujui', NULL, '2025-10-13 16:50:05'),
(34, 'YPD024', 'SDM', 'Staf SDM', 'karyawan', 'Mengikuti Training Karyawan Baru', '2025-12-08', '10:00:00', '18:00:00', '2025-12-22', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-14 13:26:19'),
(35, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Projek Training Karyawan Baru', '2025-12-08', '08:00:00', '18:00:00', '2025-12-23', '08:00:00', '17:00:00', 'ditolak', 'ganti tanggal saja', '2025-10-14 13:29:24'),
(36, 'YPD009', 'Keuangan', 'Penanggung Jawab Keuangan', 'penanggung jawab', 'ngitung uang', '2025-10-29', '10:00:00', '16:00:00', '2025-11-10', '09:00:00', '17:00:00', 'pending', NULL, '2025-10-22 03:42:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman_pelamar`
--

CREATE TABLE `pengumuman_pelamar` (
  `id` int(11) NOT NULL,
  `pelamar_id` int(11) DEFAULT NULL,
  `tahap` varchar(100) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengumuman_pelamar`
--

INSERT INTO `pengumuman_pelamar` (`id`, `pelamar_id`, `tahap`, `pesan`, `tanggal`, `created_at`) VALUES
(1, 3, 'Menunggu Proses', 'Selamat! Anda lolos seleksi awal. Tahap selanjutnya adalah seleksi administratif.', '2025-09-30', '2025-09-30 06:43:28'),
(2, 4, 'Menunggu Proses', 'Selamat! Anda lolos seleksi awal. Tahap selanjutnya adalah seleksi administratif.', '2025-09-30', '2025-09-30 06:43:34'),
(3, 3, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-09-30', '2025-09-30 06:44:56'),
(4, 4, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-09-30', '2025-09-30 06:45:01'),
(5, 3, 'Seleksi Wawancara', 'Selamat marcella adelia! Anda lolos tahap wawancara. Tahap selanjutnya adalah psikotes.', '2025-09-30', '2025-09-30 06:45:13'),
(6, 4, 'Seleksi Wawancara', 'Selamat marcella ! Anda lolos tahap wawancara. Tahap selanjutnya adalah psikotes.', '2025-09-30', '2025-09-30 06:45:17'),
(7, 3, 'Seleksi Psikotes', 'Selamat! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.', '2025-09-30', '2025-09-30 06:45:38'),
(8, 4, 'Seleksi Psikotes', 'Selamat! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.', '2025-09-30', '2025-09-30 06:45:40'),
(9, 3, 'Seleksi Kesehatan', 'Selamat! Anda diterima sebagai karyawan. Selamat bergabung!', '2025-09-30', '2025-09-30 06:45:53'),
(10, 4, 'Seleksi Kesehatan', 'Selamat! Anda diterima sebagai karyawan. Selamat bergabung!', '2025-09-30', '2025-09-30 06:45:55'),
(11, 5, 'Menunggu Proses', 'Selamat marcella ap! Lamaran Anda telah diterima dan saat ini sedang dalam tahap seleksi administratif.', '2025-09-30', '2025-09-30 07:43:06'),
(12, 5, 'Seleksi Administratif', 'Selamat marcella ap! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-09-30', '2025-09-30 07:43:49'),
(13, 5, 'Seleksi Wawancara', 'Selamat marcella ap! Anda lolos tahap wawancara. Selanjutnya anda masuk ke tahap seleksi psikotes\r\n', '2025-09-30', '2025-09-30 07:44:33'),
(14, 6, 'Menunggu Proses', 'Selamat marcella adelia! Lamaran Anda telah diterima dan sedang  dalam tahap seleksi administratif.', '2025-09-30', '2025-09-30 07:51:40'),
(15, 6, 'Seleksi Administratif', 'Selamat marcella adelia! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-09-30', '2025-09-30 07:51:49'),
(16, 6, 'Seleksi Wawancara', 'Selamat marcella adelia! Anda lolos tahap wawancara. Tahap selanjutnya adalah psikotes dan kesehatan.', '2025-09-30', '2025-09-30 07:52:02'),
(17, 6, 'Seleksi Psikotes', 'Selamat marcella adelia! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.', '2025-09-30', '2025-09-30 07:52:07'),
(18, 6, 'Seleksi Kesehatan', 'Selamat marcella adelia! Anda diterima sebagai karyawan tetap. Selamat bergabung!', '2025-09-30', '2025-09-30 07:52:16'),
(19, 7, 'Menunggu Proses', 'Selamat marcella adelia! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 04:53:24'),
(20, 7, 'Seleksi Administratif', 'Selamat marcella adelia! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 04:53:42'),
(21, 7, 'Seleksi Wawancara', 'Selamat marcella adelia! Anda lolos tahap wawancara. Tahap selanjutnya adalah psikotes.', '2025-10-03', '2025-10-03 04:53:50'),
(22, 7, 'Seleksi Psikotes', 'Selamat marcella adelia! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.', '2025-10-03', '2025-10-03 04:54:43'),
(23, 7, 'Seleksi Kesehatan', 'Selamat marcella adelia! Anda diterima sebagai karyawan tetap. Selamat bergabung!', '2025-10-03', '2025-10-03 04:56:12'),
(24, 1, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 14:53:44'),
(25, 1, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 14:53:53'),
(26, 1, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-03', '2025-10-03 14:54:05'),
(27, 1, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-03', '2025-10-03 14:54:17'),
(28, 1, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 15:21:16'),
(29, 1, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 15:21:44'),
(30, 1, 'Seleksi Wawancara', 'Selamat Lala Marcella! Anda lolos tahap wawancara, berikutnya silakan mengikuti tes psikotes dan kesehatan.', '2025-10-03', '2025-10-03 15:22:26'),
(31, 1, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-03', '2025-10-03 15:24:58'),
(32, 1, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-03', '2025-10-03 15:25:13'),
(33, 1, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 15:45:49'),
(34, 1, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 15:46:06'),
(35, 1, 'Seleksi Wawancara', '', '2025-10-03', '2025-10-03 15:46:09'),
(36, 1, 'Seleksi Kesehatan', 'Selamat Lala Marcella! Anda telah dinyatakan lolos sebagai karyawan tetap, silakan akses sebagai karyawan pada keterangan diatas dengan login \r\nUsername : Lala\r\nPassword : hashed_password_lala', '2025-10-03', '2025-10-03 15:47:11'),
(37, 2, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 15:54:55'),
(38, 3, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 16:28:37'),
(39, 3, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 16:28:44'),
(40, 3, 'Seleksi Wawancara', 'Maaf, Anda tidak lolos pada tahap seleksi ini. Terima kasih telah berpartisipasi.', '2025-10-03', '2025-10-03 16:28:45'),
(41, 4, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 16:32:41'),
(42, 4, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 16:32:44'),
(43, 4, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.', '2025-10-03', '2025-10-03 16:32:49'),
(44, 4, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-03', '2025-10-03 16:32:57'),
(45, 4, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-03', '2025-10-03 16:33:07'),
(46, 5, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-03', '2025-10-03 16:51:25'),
(47, 5, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-03', '2025-10-03 16:51:27'),
(48, 5, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-03', '2025-10-03 16:51:31'),
(49, 5, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-03', '2025-10-03 16:51:34'),
(50, 6, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-03 17:08:39'),
(51, 6, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-03 17:08:52'),
(52, 6, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.', '2025-10-04', '2025-10-03 17:09:01'),
(53, 6, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-04', '2025-10-03 17:09:04'),
(54, 6, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-03 17:09:07'),
(55, 8, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-03 17:26:06'),
(56, 8, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-03 17:26:19'),
(57, 8, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-04', '2025-10-03 17:26:24'),
(58, 8, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-03 17:26:27'),
(59, 7, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-03 17:26:42'),
(60, 7, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-03 17:26:53'),
(61, 7, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-04', '2025-10-03 17:26:58'),
(62, 7, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-03 17:27:00'),
(63, 9, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-03 17:31:22'),
(64, 9, 'Seleksi Administratif', 'Maaf, Anda tidak lolos pada tahap seleksi ini. Terima kasih telah berpartisipasi.', '2025-10-04', '2025-10-03 17:31:25'),
(65, 10, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:24:31'),
(66, 10, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:24:34'),
(67, 10, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-04', '2025-10-04 06:24:38'),
(68, 10, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:24:40'),
(69, 11, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:29:15'),
(70, 11, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:29:17'),
(71, 11, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.', '2025-10-04', '2025-10-04 06:29:22'),
(72, 11, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-04', '2025-10-04 06:29:29'),
(73, 11, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:29:32'),
(74, 12, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:33:52'),
(75, 12, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:33:54'),
(76, 12, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.', '2025-10-04', '2025-10-04 06:33:59'),
(77, 12, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-04', '2025-10-04 06:34:02'),
(78, 12, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:34:04'),
(79, 13, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:37:39'),
(80, 13, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:37:42'),
(81, 13, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-04', '2025-10-04 06:37:46'),
(82, 13, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:37:48'),
(83, 14, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:40:37'),
(84, 14, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:40:40'),
(85, 14, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-04', '2025-10-04 06:40:51'),
(86, 14, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:40:53'),
(87, 15, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:43:03'),
(88, 15, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:43:05'),
(89, 15, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-04', '2025-10-04 06:43:09'),
(90, 15, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:43:12'),
(91, 16, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-04', '2025-10-04 06:45:10'),
(92, 16, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-04', '2025-10-04 06:45:12'),
(93, 16, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-04', '2025-10-04 06:45:14'),
(94, 16, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-04', '2025-10-04 06:45:16'),
(95, 17, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-05', '2025-10-05 12:55:51'),
(96, 17, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-05', '2025-10-05 12:55:53'),
(97, 17, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.', '2025-10-05', '2025-10-05 12:55:57'),
(98, 17, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-05', '2025-10-05 12:56:00'),
(99, 17, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-05', '2025-10-05 12:56:02'),
(100, 18, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-07', '2025-10-07 16:13:54'),
(101, 18, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-07', '2025-10-07 16:14:14'),
(102, 18, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-07', '2025-10-07 16:29:57'),
(103, 18, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-07', '2025-10-07 16:30:13'),
(104, 19, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-07', '2025-10-07 16:52:53'),
(105, 20, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-09', '2025-10-09 10:14:12'),
(106, 20, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-09', '2025-10-09 10:14:21'),
(107, 20, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-09', '2025-10-09 10:14:25'),
(108, 20, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-09', '2025-10-09 10:14:28'),
(109, 19, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-10', '2025-10-10 15:23:57'),
(110, 22, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-12', '2025-10-12 05:09:11'),
(111, 22, 'Seleksi Administratif', 'Selamat Anda lolos ke tahap wawancara', '2025-10-12', '2025-10-12 05:09:56'),
(112, 22, 'Seleksi Wawancara', 'Selamat Anda lolos ke tahap tes psikotes', '2025-10-12', '2025-10-12 05:10:51'),
(113, 19, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.', '2025-10-12', '2025-10-12 05:11:21'),
(114, 22, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-12', '2025-10-12 05:17:26'),
(115, 21, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-13', '2025-10-13 16:56:45'),
(116, 21, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-13', '2025-10-13 16:59:19'),
(117, 21, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-13', '2025-10-13 16:59:29'),
(118, 21, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-14', '2025-10-13 17:00:35'),
(119, 19, 'Seleksi Psikotes & Kesehatan', 'Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.', '2025-10-14', '2025-10-14 13:11:06'),
(120, 19, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-14', '2025-10-14 13:11:08'),
(121, 23, 'Menunggu Proses', 'Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.', '2025-10-14', '2025-10-14 13:23:35'),
(122, 23, 'Seleksi Administratif', 'Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.', '2025-10-14', '2025-10-14 13:23:42'),
(123, 23, 'Seleksi Wawancara', 'Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.', '2025-10-14', '2025-10-14 13:24:05'),
(124, 23, 'Seleksi Psikotes', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-14', '2025-10-14 13:24:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pelamar`
--

CREATE TABLE `riwayat_pelamar` (
  `id` int(11) NOT NULL,
  `pelamar_id` int(11) NOT NULL,
  `status_administratif` varchar(50) DEFAULT NULL,
  `status_wawancara` varchar(50) DEFAULT NULL,
  `status_psikotes` varchar(50) DEFAULT NULL,
  `status_kesehatan` varchar(50) DEFAULT NULL,
  `status_final` varchar(50) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_pelamar`
--

INSERT INTO `riwayat_pelamar` (`id`, `pelamar_id`, `status_administratif`, `status_wawancara`, `status_psikotes`, `status_kesehatan`, `status_final`, `last_update`) VALUES
(1, 1, 'Lolos', 'Lolos', NULL, 'Lolos', 'Diterima', '2025-10-03 15:47:11'),
(2, 2, 'Tidak Lolos', NULL, NULL, NULL, 'Tidak Lolos', '2025-10-03 15:54:58'),
(3, 3, NULL, 'Tidak Lolos', NULL, NULL, 'Tidak Lolos', '2025-10-03 16:28:45'),
(6, 4, NULL, NULL, NULL, NULL, 'Diterima', '2025-10-03 16:33:07'),
(11, 5, NULL, NULL, NULL, NULL, 'Diterima', '2025-10-03 16:51:34'),
(15, 6, 'Lolos', 'Lolos', 'Lolos', 'Lolos', 'Diterima', '2025-10-03 17:09:07'),
(20, 8, 'Lolos', 'Lolos', NULL, 'Lolos', 'Diterima', '2025-10-03 17:26:27'),
(24, 7, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-03 17:27:00'),
(28, 9, 'Tidak Lolos', NULL, NULL, NULL, 'Tidak Lolos', '2025-10-03 17:31:25'),
(30, 10, 'Lolos', 'Lolos', NULL, 'Lolos', 'Diterima', '2025-10-04 06:24:40'),
(34, 11, 'Lolos', 'Lolos', 'Lolos', 'Lolos', 'Diterima', '2025-10-04 06:29:32'),
(39, 12, 'Lolos', 'Lolos', 'Lolos', 'Lolos', 'Diterima', '2025-10-04 06:34:04'),
(44, 13, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-04 06:37:48'),
(48, 14, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-04 06:40:53'),
(52, 15, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-04 06:43:12'),
(56, 16, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-04 06:45:16'),
(60, 17, 'Lolos', 'Lolos', 'Lolos', 'Lolos', 'Diterima', '2025-10-05 12:56:02'),
(65, 18, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-07 16:30:13'),
(69, 19, 'Lolos', 'Lolos', 'Lolos', 'Lolos', 'Diterima', '2025-10-14 13:11:08'),
(70, 20, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-09 10:14:28'),
(75, 22, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-12 05:17:26'),
(80, 21, 'Lolos', 'Lolos', NULL, 'Lolos', 'Diterima', '2025-10-13 17:00:35'),
(86, 23, 'Lolos', 'Lolos', 'Lolos', NULL, 'Diterima', '2025-10-14 13:24:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT '',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `created_at`) VALUES
(1, 'Marcella Adelia', 'marcellaadelia06@gmail.com', '$2y$10$IDfw5.PGi8gmnxb3N4apteA47WEQxU0raaIjHP1FVn9goGnoGJHD2', '2025-09-28 09:40:50'),
(2, '', 'marcellaadelia1003@gmail.com', '$2y$10$3STTMIcjJptqnI4paiNI.uFWD03II3QUblNpxpxUqn4SLtiSBS4Lm', '2025-09-30 06:25:09'),
(4, '', 'marcellaadelia07@gmail.com', '$2y$10$JPf5yS847fIq3d92uxvot.0dKnlccHktiZrsAZYQ.KkrHTdf1l3kq', '2025-09-30 07:21:32'),
(5, '', 'marcellaadelia08@gmail.com', '$2y$10$Hqxci5VEETSDKYsXCOa0LOyULs.M/gJXD8qXbDwFEi8giiiXJsmyu', '2025-09-30 07:50:15'),
(7, '', 'Coco@gmail.com', '$2y$10$yHuS8ErmWHhcyP2MUA7ET.nZWqQB0anK211C43Bc3UH9mQUeY/Y6m', '2025-10-01 12:01:33'),
(8, '', 'marcellaadelia09@gmail.com', '$2y$10$1b6B7tuhGQJ9ivUXJLTfdOMcazt1XI7y88702exTMI2AEBAUgCK2K', '2025-10-03 04:45:21'),
(10, '', 'marcellaadelia10@gmail.com', '$2y$10$BhuOyCbLsdZDfLfG34W2v.ZmlnhlcsDIl13.hJVrzYXWJMckEV4ca', '2025-10-03 14:42:56'),
(11, '', 'andrianna123@gmail.com', '$2y$10$4OzEnODMSNuVvQ4KX/QjSeUZ02tXw3ejNzKixxItjmW0G.0EBtTom', '2025-10-03 15:52:24'),
(12, '', 'alexander000@gmail.com', '$2y$10$jpAXXwdjQ0Q9n.R4tvN41ucTXXK5za6Kmg3rxHhAFp9.0muxVK8r.', '2025-10-03 16:09:39'),
(13, '', 'joffy1234@gmail.com', '$2y$10$d6rwUJx/VevbcRuVl5LLweZL3TXl1J9cNKjPcnYYdL76auJ8qPHTO', '2025-10-03 16:30:44'),
(14, '', 'jeffra1234@gmail.com', '$2y$10$nA48waEyhPtR2YsygUxYaekbJuI72P/HrPfJn/ByEVsN9LGrIAmsy', '2025-10-03 16:50:10'),
(15, '', 'hezkiel1234@gmail.com', '$2y$10$8qyKvcY3ZfiKrup2Qnx.Nejhe6YFz6bNmX7aIexTMNVHbzQpOrCQW', '2025-10-03 17:05:54'),
(16, '', 'marcellaadelia11@gmail.com', '$2y$10$CwSLVViSrsZwKc6zavOGaeaQPC9cMgvL6GEzOROncPkhnetpR8p9i', '2025-10-03 17:10:37'),
(17, '', 'cici1234@gmail.com', '$2y$10$adbfkoeT/vz7UZZaXCzxE.ecsRepWQlFnKq4fTbkgVa1.1UGaaut.', '2025-10-03 17:20:03'),
(18, '', 'lindi1234@gmail.com', '$2y$10$gkgB0d4YjczX24T5dChAQuaD53ftn7gR05MEkcoSlQf4EQK6EnxGu', '2025-10-03 17:30:15'),
(19, '', 'leonardo123@gmail.com', '$2y$10$XpKq.RSJm2lVYwoZtA.K8eaKxVRqT4rgyqphD1nU8DZXUmuUn8fui', '2025-10-04 06:21:06'),
(20, '', 'naomi123@gmail.com', '$2y$10$FsKqe37xnDfQ9liOixytReWyxzrGYefD/IdWMgjh6qZmhoZ8WvfhG', '2025-10-04 06:27:15'),
(21, '', 'aurora123@gmail.com', '$2y$10$YBN.J2YaoS2pVQMoaCJDPO5e66wgdsgowa0PWYyXu9br2s0bO6cKe', '2025-10-04 06:32:24'),
(22, '', 'selena123@gmail.com', '$2y$10$E4C18xROudLmOBAIcJu80.xSJ0lHtO7.bxGPSNcnuMmptrTM/UzZG', '2025-10-04 06:35:57'),
(23, '', 'kelra123@gmail.com', '$2y$10$1S4zteGUZunZI6u76KBfy.WiJlwvkjo27rGENid4lNZczI4sp9ZH.', '2025-10-04 06:39:17'),
(24, '', 'lyra1234@gmail.com', '$2y$10$ZkBYzUzxv8iyHwiVeZ.AFueAtZ2G77t2NawMb32fGIaeblRdc/lQ6', '2025-10-04 06:41:43'),
(25, '', 'yovan1234@gmail.com', '$2y$10$jz6l4OAxjH9p5WhfNikrfedgRwbJpN0zcOyhCGL3Zd7BwLG8SeuW2', '2025-10-04 06:43:43'),
(26, '', 'feli1234@gmail.com', '$2y$10$ZJQMII7TnZNUG1wJ0yR6VOlFiCNKpmp6.ej3GKkJVm3pjoGd3JGla', '2025-10-05 12:53:53'),
(27, '', 'dicky1234@gmail.com', '$2y$10$YsBUAwgpvY8oE5L80C4LuO/fwsz0WtaJw7U.iNY4jt81l3m6pzhVW', '2025-10-07 16:11:06'),
(28, '', 'nindya123@gmail.com', '$2y$10$Gx6RfcZR3qowXM54GSH4W.Y7YyiVaajscGYgTqEsp6Odidni8qI6m', '2025-10-07 16:50:23'),
(29, '', 'indah123@gmail.com', '$2y$10$1k3a35hJD6G/rq2KwKg9SOuiO5Wbu.4zqAtru0Kv67qCXHkXCUoDa', '2025-10-09 09:59:35'),
(30, 'Marcella Adelia', 'adeliamarcellaa@gmail.com', '', '2025-10-09 10:00:36'),
(31, 'MARCELLA ADELIA PUTRI', '24n10004@student.unika.ac.id', '', '2025-10-12 05:04:02'),
(32, '', 'gege1234@gmail.com', '$2y$10$PIABgvpbvsnAmRntNaoGMuYQrBElexx/YA/0Atpvzw6/q.oKdDST2', '2025-10-14 13:21:26'),
(33, 'FATYOSHi', 'ricardoadi06@gmail.com', '', '2025-10-22 02:28:40'),
(34, '', 'aditdisini@gmail.com', '$2y$10$hrYmLk9L1nf9/7rCw2KKzemd8JosEV118cVA2XbQlj2o8h6ePxD5C', '2025-10-22 02:32:13');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_karyawan`
--
ALTER TABLE `data_karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `kode_karyawan` (`kode_karyawan`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `data_pelamar`
--
ALTER TABLE `data_pelamar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_pengajuan_cuti`
--
ALTER TABLE `data_pengajuan_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_karyawan` (`kode_karyawan`);

--
-- Indeks untuk tabel `data_pengajuan_khl`
--
ALTER TABLE `data_pengajuan_khl`
  ADD PRIMARY KEY (`id_khl`),
  ADD KEY `kode_karyawan` (`kode_karyawan`);

--
-- Indeks untuk tabel `pengumuman_pelamar`
--
ALTER TABLE `pengumuman_pelamar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat_pelamar`
--
ALTER TABLE `riwayat_pelamar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pelamar_id` (`pelamar_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `data_karyawan`
--
ALTER TABLE `data_karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `data_pelamar`
--
ALTER TABLE `data_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `data_pengajuan_cuti`
--
ALTER TABLE `data_pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `data_pengajuan_khl`
--
ALTER TABLE `data_pengajuan_khl`
  MODIFY `id_khl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `pengumuman_pelamar`
--
ALTER TABLE `pengumuman_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pelamar`
--
ALTER TABLE `riwayat_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `data_pengajuan_cuti`
--
ALTER TABLE `data_pengajuan_cuti`
  ADD CONSTRAINT `data_pengajuan_cuti_ibfk_1` FOREIGN KEY (`kode_karyawan`) REFERENCES `data_karyawan` (`kode_karyawan`);

--
-- Ketidakleluasaan untuk tabel `data_pengajuan_khl`
--
ALTER TABLE `data_pengajuan_khl`
  ADD CONSTRAINT `data_pengajuan_khl_ibfk_1` FOREIGN KEY (`kode_karyawan`) REFERENCES `data_karyawan` (`kode_karyawan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
