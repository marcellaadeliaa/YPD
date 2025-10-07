-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Okt 2025 pada 10.29
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 7.3.33

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
CREATE DEFINER=`root`@`localhost` PROCEDURE `KurangiSisaCuti` (IN `p_kode_karyawan` VARCHAR(20), IN `p_jenis_cuti` VARCHAR(50), IN `p_tanggal_mulai` DATE, IN `p_tanggal_akhir` DATE)  BEGIN
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `data_karyawan`
--

INSERT INTO `data_karyawan` (`id_karyawan`, `kode_karyawan`, `nama_lengkap`, `email`, `password`, `jabatan`, `divisi`, `role`, `no_telp`, `sisa_cuti_tahunan`, `sisa_cuti_lustrum`, `status_aktif`, `created_at`) VALUES
(1, 'YPD001', 'Pico', 'pico.dir@ypd.com', 'hashed_password_direktur', 'Direktur Utama', 'Direktsi', 'direktur', '081234567890', 0, 0, 'aktif', '2025-09-30 23:37:17'),
(2, 'YPD002', 'Cell', 'cell.sdm@ypd.com', 'hashed_password_admin', 'Administrator', '', 'admin', '081234567891', 0, 0, 'aktif', '2025-09-30 23:37:17'),
(3, 'YPD010', 'Adrian', 'adrian.karyawan@ypd.com', 'hashed_password_karyawan', 'Staf Training', 'Training', 'karyawan', '081234567892', 12, 4, 'aktif', '2025-09-30 23:37:17'),
(4, 'YPD003', 'Ria', 'ria.direksi@ypd.com', 'hashed_password_ria', 'Penanggung Jawab Training', 'Training', 'penanggung jawab', '081234567893', 12, 5, 'aktif', '2025-09-30 23:45:32'),
(5, 'YPD004', 'Dani', 'dani.pj@ypd.com', 'hashed_password_dani', 'Staf Keuangan', 'Keuangan', 'karyawan', '081234567894', 12, 5, 'aktif', '2025-09-30 23:45:32'),
(6, 'YPD005', 'Budi', 'budibudi@gmail.com', 'hashed_password_budi', 'Penanggung Jawab Konsultasi', 'Konsultasi', 'penanggung jawab', '12345677654', 12, 5, 'aktif', '2025-10-02 08:30:16'),
(7, 'YPD006', 'Cica', 'cica@ypd.com', 'hashed_password_cica', 'Penanggung Jawab Wisma', 'Wisma', 'penanggung jawab', '918347914', 12, 5, 'aktif', '2025-10-02 14:01:16'),
(8, 'YPD007', 'Dian', 'didi@gmail.com', 'hashed_password_dian', 'Penanggung Jawab SDM', 'SDM', 'penanggung jawab', '5981731412', 12, 5, 'aktif', '2025-10-02 15:10:28'),
(9, 'YPD008', 'Jasmine', 'minminja@gmail.com', 'hashed_password_jasmine', 'Penanggung Jawab Sekretariat', 'Sekretariat', 'penanggung jawab', '123415654312', 12, 5, 'aktif', '2025-10-02 15:24:59'),
(10, 'YPD009', 'Mega', 'gamega@gmail.com', 'hashed_password_mega', 'Penanggung Jawab Keuangan', 'Keuangan', 'penanggung jawab', '12347358642879', 12, 5, 'aktif', '2025-10-02 15:46:27'),
(11, 'YPD011', 'Lala Marcella', 'lala.marcella@ypd.com', 'hashed_password_lala', 'Staf Keuangan', 'Keuangan', 'karyawan', '08186845699', 4, 0, 'aktif', '2025-10-04 05:27:28'),
(12, 'YPD012', 'Hezkiel', 'hezkiel@ypd.com', 'hashed_password_hezkiel', 'Staf Sekretariat', 'Sekretariat', 'karyawan', '08186889777', 12, 0, 'aktif', '2025-10-04 06:13:54'),
(13, 'YPD013', 'Adelia', 'adelia@ypd.com', 'hashed_password_adelia', 'Staf Sekretariat', 'Sekretariat', 'karyawan', '08186889755', 12, 0, 'aktif', '2025-10-04 06:15:55'),
(14, 'YPD014', 'Cici', 'cici@ypd.com', 'hashed_password_cici', 'Staf Keuangan', 'Keuangan', 'karyawan', '081868456987', 12, 2, 'aktif', '2025-10-04 06:18:48'),
(15, 'YPD015', 'Leonardo', 'leonardo@ypd.com', 'hashed_password_leonardo', 'Staf Training', 'Training', 'karyawan', '08186889751', 6, 0, 'aktif', '2025-10-04 06:26:05'),
(16, 'YPD016', 'Naomi', 'naomi@ypd.com', 'hashed_password_naomi', 'Staf Konsultasi', 'Konsultasi', 'karyawan', '081868456987', 0, 0, 'aktif', '2025-10-04 06:30:39'),
(17, 'YPD017', 'Aurora', 'aurora@ypd.com', 'hashed_password_aurora', 'Staf Konsultasi', 'Konsultasi', 'karyawan', '081868456988', 0, 0, 'aktif', '2025-10-04 06:35:02'),
(18, 'YPD018', 'Selena', 'selena@ypd.com', 'hashed_password_selena', 'Staf Wisma', 'Wisma', 'karyawan', '081868456978', 0, 0, 'aktif', '2025-10-04 06:38:26'),
(19, 'YPD019', 'Kelra', 'kelra@ypd.com', 'hashed_password_kelra', 'Staf Wisma', 'Wisma', 'karyawan', '081868456985', 0, 0, 'aktif', '2025-10-04 06:46:13'),
(20, 'YPD020', 'Lyra', 'lyra@ypd.com', 'hashed_password_lyra', 'Staf SDM', 'SDM', 'karyawan', '081868456987', 5, 0, 'aktif', '2025-10-04 06:47:18'),
(21, 'YPD021', 'Yovan', 'yovan@ypd.com', 'hashed_password_yovan', 'Staf SDM', 'SDM', 'karyawan', '081868456985', 5, 0, 'aktif', '2025-10-04 06:48:16');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(17, 26, 'Feli ', 'Konsultasi', 'Perempuan', 'Tangerang', '2000-01-30', '1234512349', 'Tangerang', 'Jl. Pawiyatan Luhur Semarang', '08186889755', 'feli1234@gmail.com', 'Buddha', '081256478988', 'Sarjana', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', 'uploads/1759668945-Pernyataan_Penyelesaian_Proyek.docx', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-10-05 12:55:45');

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
  `waktu_persetujuan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `data_pengajuan_cuti`
--

INSERT INTO `data_pengajuan_cuti` (`id`, `kode_karyawan`, `nama_karyawan`, `divisi`, `jabatan`, `role`, `jenis_cuti`, `tanggal_mulai`, `tanggal_akhir`, `alasan`, `file_surat_dokter`, `status`, `created_at`, `waktu_persetujuan`) VALUES
(1, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Khusus - Menikah', '2025-10-06', '2025-10-08', NULL, NULL, 'Diterima', '2025-10-06 16:49:59', '2025-10-07 08:00:13'),
(2, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Sakit', '2025-10-06', '2025-10-08', 'sakit', 'uploads/surat_sakit/1759769935_Request for Quotation.pdf', 'Diterima', '2025-10-06 16:58:55', '2025-10-07 07:47:58'),
(3, 'YPD021', 'Yovan', 'SDM', 'Staf SDM', 'karyawan', 'Sakit', '2025-10-07', '2025-10-09', 'nono ya', 'uploads/surat_sakit/1759816970_ChatGPT Image 5 Okt 2025, 00.02.09.png', 'Ditolak', '2025-10-07 06:02:50', '2025-10-07 08:04:59'),
(4, 'YPD011', 'Lala Marcella', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Tahunan', '2025-12-28', '2026-01-04', 'mau holiday', NULL, 'Diterima', '2025-10-07 06:07:58', '2025-10-07 08:28:27'),
(5, 'YPD014', 'Cici', 'Keuangan', 'Staf Keuangan', 'karyawan', 'Lustrum', '2025-10-17', '2025-10-17', 'Mau ke luar kota', NULL, 'Diterima', '2025-10-07 06:11:46', '2025-10-07 08:27:27'),
(6, 'YPD010', 'Adrian', 'Training', 'Staf Training', 'karyawan', 'Lustrum', '2025-10-15', '2025-10-15', 'Pergi 1 hari', NULL, 'Diterima', '2025-10-07 06:48:59', '2025-10-07 08:51:02'),
(7, 'YPD015', 'Leonardo', 'Training', 'Staf Training', 'karyawan', 'Tahunan', '2025-11-10', '2025-11-17', 'pergi holiday ke LN hehe', NULL, 'Diterima', '2025-10-07 06:49:55', '2025-10-07 08:50:45'),
(8, 'YPD003', 'Ria', 'Training', 'Penanggung Jawab Training', 'penanggung jawab', 'Tahunan', '2025-10-09', '2025-10-09', 'sakit', NULL, 'Menunggu Persetujuan', '2025-10-07 07:57:10', NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `data_pengajuan_khl`
--

INSERT INTO `data_pengajuan_khl` (`id_khl`, `kode_karyawan`, `divisi`, `jabatan`, `role`, `proyek`, `tanggal_khl`, `jam_mulai_kerja`, `jam_akhir_kerja`, `tanggal_cuti_khl`, `jam_mulai_cuti_khl`, `jam_akhir_cuti_khl`, `status_khl`, `alasan_penolakan`, `created_at`) VALUES
(1, 'YPD021', 'SDM', 'Staf SDM', 'karyawan', 'Projek Training SDM baru', '2025-10-06', '08:00:00', '18:00:00', '2025-10-28', '08:00:00', '17:00:00', 'disetujui', NULL, '2025-10-05 05:25:22'),
(2, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Projek Training SDM baru', '2025-10-06', '07:00:00', '19:00:00', '2025-10-30', '10:00:00', '18:00:00', 'disetujui', NULL, '2025-10-05 05:32:47'),
(3, 'YPD021', 'SDM', 'Staf SDM', 'karyawan', 'Projek Training Karyawan Baru', '2025-10-08', '08:00:00', '17:00:00', '2025-10-22', '08:00:00', '17:00:00', 'ditolak', 'tidak boleh!!', '2025-10-05 05:56:20'),
(4, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Projek Training SDM baru', '2025-10-06', '10:00:00', '17:00:00', '2025-10-23', '09:00:00', '18:00:00', 'pending', NULL, '2025-10-05 06:10:51'),
(5, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'Projek Training SDM baru oawkok', '2025-10-15', '10:00:00', '16:00:00', '2025-10-30', '08:00:00', '17:00:00', 'disetujui', '', '2025-10-05 06:27:52'),
(6, 'YPD007', 'SDM', 'Penanggung Jawab SDM', 'penanggung jawab', 'projekan2', '2025-10-06', '09:00:00', '18:00:00', '2025-11-08', '08:00:00', '17:00:00', 'ditolak', 'Tidak Boleh', '2025-10-05 06:48:08'),
(7, 'YPD001', 'Direksi', 'Direktur Utama', 'direktur', 'bandungan', '2025-11-06', '15:35:00', '16:35:00', '2025-11-06', '17:35:00', '18:35:00', 'disetujui', NULL, '2025-10-05 08:35:46'),
(8, 'YPD001', 'Direksi', '', 'direktur', 'bandungan', '2025-10-07', '00:11:00', '01:11:00', '2025-10-07', '03:11:00', '04:11:00', 'disetujui', NULL, '2025-10-05 17:11:49'),
(9, 'YPD001', 'Direksi', '', 'direktur', 'bandungan', '2025-10-06', '00:18:00', '01:19:00', '2025-10-06', '03:19:00', '04:19:00', 'disetujui', NULL, '2025-10-05 17:19:13'),
(10, 'YPD001', 'Direksi', '', 'direktur', 'bandungan', '2025-10-06', '00:22:00', '01:22:00', '2025-10-06', '02:22:00', '03:22:00', 'disetujui', NULL, '2025-10-05 17:22:19'),
(11, 'YPD001', 'Direksi', '', 'direktur', 'Tugu Muda', '2025-10-06', '01:35:00', '02:35:00', '2025-10-06', '03:35:00', '04:36:00', 'disetujui', NULL, '2025-10-05 17:36:05'),
(12, 'YPD001', 'Direksi', '', 'direktur', 'Tugu Muda', '2025-10-07', '00:37:00', '01:37:00', '2025-10-07', '02:37:00', '03:37:00', 'disetujui', NULL, '2025-10-05 17:37:58'),
(13, 'YPD001', 'Direktsi', 'Direktur Utama', 'direktur', 'proyek baru', '2025-10-06', '01:04:00', '02:04:00', '2025-10-06', '04:04:00', '05:04:00', 'disetujui', NULL, '2025-10-05 18:04:55'),
(14, 'YPD001', 'Direktsi', 'Direktur Utama', 'direktur', 'proyek baru', '2025-10-06', '01:04:00', '02:04:00', '2025-10-06', '04:04:00', '05:04:00', 'disetujui', NULL, '2025-10-05 18:05:33'),
(15, 'YPD001', 'Direktsi', 'Direktur Utama', 'direktur', 'proyek desa', '2025-10-06', '01:23:00', '02:23:00', '2025-10-06', '03:23:00', '04:23:00', 'disetujui', NULL, '2025-10-05 18:23:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lamaran`
--

CREATE TABLE `lamaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Menunggu Proses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 'marcellaadelia06@gmail.com', '707f132ed4b6cb900a8a63b045fc1dcf72067d541e03862305c5e78e28472fc38c45d769f3db3a402e5a4aac19cd987a601c', '2025-09-29 19:42:20', 0, '2025-09-29 16:42:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_cuti`
--

CREATE TABLE `pengajuan_cuti` (
  `id` int(11) NOT NULL,
  `kode_karyawan` varchar(11) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `role` enum('karyawan','direktur','admin','penanggung jawab') NOT NULL,
  `jenis_cuti` varchar(50) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('Menunggu Persetujuan','Diterima','Ditolak') NOT NULL DEFAULT 'Menunggu Persetujuan',
  `waktu_persetujuan` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengajuan_cuti`
--

INSERT INTO `pengajuan_cuti` (`id`, `kode_karyawan`, `nama_karyawan`, `divisi`, `jabatan`, `role`, `jenis_cuti`, `tanggal_mulai`, `tanggal_akhir`, `alasan`, `status`, `waktu_persetujuan`, `created_at`) VALUES
(1, 'YPD001', 'Pico', 'Direksi', '', 'karyawan', 'Tahunan', '2025-10-02', '2025-10-05', 'keluarga menikah', 'Menunggu Persetujuan', NULL, '2025-10-01 11:36:39'),
(3, 'YPD101', 'Adrian', 'Training', '', 'karyawan', 'Tahunan', '2025-09-29', '2025-10-02', 'Menjemput keluarga', 'Diterima', NULL, '2025-10-01 11:49:45'),
(4, 'YPD003', 'Ria', 'Wisma', '', 'karyawan', 'Tahunan', '2025-09-02', '2025-10-07', 'Liburan', 'Ditolak', NULL, '2025-10-01 11:08:46'),
(6, 'YPD001', 'Pico', 'Direksi', '', 'karyawan', 'Sakit', '2025-10-02', '2025-10-02', 'Sakit', '', NULL, '2025-10-01 11:23:08'),
(7, 'YPD001', 'Pico', 'Direksi', '', 'karyawan', 'Tahunan', '2025-10-04', '2025-10-04', 'Liburan', '', NULL, '2025-10-03 00:15:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_cuti_pj`
--

CREATE TABLE `pengajuan_cuti_pj` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `jenis_cuti` varchar(50) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `alasan` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Disetujui',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_khl`
--

CREATE TABLE `pengajuan_khl` (
  `id` int(11) NOT NULL,
  `kode_karyawan` varchar(11) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `jam_mulai_kerja` time DEFAULT NULL,
  `jam_akhir_kerja` time DEFAULT NULL,
  `tanggal_khl` date NOT NULL,
  `tanggal_akhir_khl` date DEFAULT NULL,
  `jam_mulai_libur` time DEFAULT NULL,
  `jam_akhir_libur` time DEFAULT NULL,
  `nama_proyek` text DEFAULT NULL,
  `status` enum('Menunggu Persetujuan','Diterima','Ditolak') NOT NULL DEFAULT 'Menunggu Persetujuan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengajuan_khl`
--

INSERT INTO `pengajuan_khl` (`id`, `kode_karyawan`, `nama_karyawan`, `divisi`, `jam_mulai_kerja`, `jam_akhir_kerja`, `tanggal_khl`, `tanggal_akhir_khl`, `jam_mulai_libur`, `jam_akhir_libur`, `nama_proyek`, `status`, `created_at`) VALUES
(1, 'YPD0001', 'Pico', 'Direksi', '08:00:00', '17:00:00', '2025-10-04', '2025-10-06', '08:00:00', '17:00:00', 'Seminar', 'Menunggu Persetujuan', '2025-10-01 11:54:11'),
(2, 'YPD001', 'Pico', 'Direksi', '07:00:00', '17:00:00', '2025-10-06', '2025-10-07', '09:00:00', '18:00:04', 'Proyek C (Surabaya)', 'Menunggu Persetujuan', '2025-10-01 16:51:46'),
(3, 'YPD001', 'Pico', 'Direksi', '09:00:00', '18:00:00', '2025-10-04', '2025-10-05', '09:00:00', '18:00:00', 'Proyek B (Bandung)', 'Diterima', '2025-10-01 16:52:23'),
(4, 'YPD001', 'Pico', 'Direksi', '08:00:00', '16:30:00', '2025-10-03', '2025-10-03', '08:30:00', '17:30:00', 'Proyek C (Surabaya)', '', '2025-10-02 02:47:23'),
(5, 'YPD001', 'Pico', 'Direksi', '07:00:00', '17:00:00', '2025-10-11', '2025-10-11', '07:00:00', '17:00:00', 'Proyek Internal', '', '2025-10-02 05:19:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `tanggal`, `status`) VALUES
(1, 'Pengumuman Seleksi Tahap 1', 'Tes seleksi tahap pertama akan dilaksanakan pada tanggal 30 September 2025. Silakan persiapkan diri dengan baik.', '2025-09-29 14:59:32', 'active'),
(2, 'Info Jadwal Wawancara', 'Bagi pelamar yang lulus seleksi administrasi, akan dihubungi via email untuk jadwal wawancara.', '2025-09-29 14:59:32', 'active');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(99, 17, 'Seleksi Kesehatan', 'Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.', '2025-10-05', '2025-10-05 12:56:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman_umum`
--

CREATE TABLE `pengumuman_umum` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_cuti`
--

CREATE TABLE `riwayat_cuti` (
  `id` int(11) NOT NULL,
  `kode_karyawan` varchar(50) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(50) NOT NULL,
  `jenis_cuti` varchar(50) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `waktu_persetujuan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(60, 17, 'Lolos', 'Lolos', 'Lolos', 'Lolos', 'Diterima', '2025-10-05 12:56:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sisa_cuti`
--

CREATE TABLE `sisa_cuti` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sisa_cuti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `created_at`) VALUES
(1, '', 'marcellaadelia06@gmail.com', '$2y$10$IDfw5.PGi8gmnxb3N4apteA47WEQxU0raaIjHP1FVn9goGnoGJHD2', '2025-09-28 09:40:50'),
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
(26, '', 'feli1234@gmail.com', '$2y$10$ZJQMII7TnZNUG1wJ0yR6VOlFiCNKpmp6.ej3GKkJVm3pjoGd3JGla', '2025-10-05 12:53:53');

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
-- Indeks untuk tabel `lamaran`
--
ALTER TABLE `lamaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indeks untuk tabel `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`kode_karyawan`);

--
-- Indeks untuk tabel `pengajuan_cuti_pj`
--
ALTER TABLE `pengajuan_cuti_pj`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`kode_karyawan`);

--
-- Indeks untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengumuman_pelamar`
--
ALTER TABLE `pengumuman_pelamar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengumuman_umum`
--
ALTER TABLE `pengumuman_umum`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat_cuti`
--
ALTER TABLE `riwayat_cuti`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat_pelamar`
--
ALTER TABLE `riwayat_pelamar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pelamar_id` (`pelamar_id`);

--
-- Indeks untuk tabel `sisa_cuti`
--
ALTER TABLE `sisa_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `data_pelamar`
--
ALTER TABLE `data_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `data_pengajuan_cuti`
--
ALTER TABLE `data_pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `data_pengajuan_khl`
--
ALTER TABLE `data_pengajuan_khl`
  MODIFY `id_khl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `lamaran`
--
ALTER TABLE `lamaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_cuti_pj`
--
ALTER TABLE `pengajuan_cuti_pj`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengumuman_pelamar`
--
ALTER TABLE `pengumuman_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT untuk tabel `pengumuman_umum`
--
ALTER TABLE `pengumuman_umum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_cuti`
--
ALTER TABLE `riwayat_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pelamar`
--
ALTER TABLE `riwayat_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT untuk tabel `sisa_cuti`
--
ALTER TABLE `sisa_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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

--
-- Ketidakleluasaan untuk tabel `lamaran`
--
ALTER TABLE `lamaran`
  ADD CONSTRAINT `lamaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
