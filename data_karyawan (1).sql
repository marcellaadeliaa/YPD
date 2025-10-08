-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 06:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

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

-- --------------------------------------------------------

--
-- Table structure for table `data_karyawan`
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
-- Dumping data for table `data_karyawan`
--

INSERT INTO `data_karyawan` (`id_karyawan`, `kode_karyawan`, `nama_lengkap`, `email`, `password`, `jabatan`, `divisi`, `role`, `no_telp`, `sisa_cuti_tahunan`, `sisa_cuti_lustrum`, `status_aktif`, `created_at`) VALUES
(1, 'YPD001', 'Pico', 'pico.dir@ypd.com', 'hashed_password_direktur', 'Direktur Utama', 'Direksi', 'direktur', '081234567890', 0, 0, 'aktif', '2025-09-30 23:37:17'),
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
(14, 'YPD014', 'Cici', 'cici@ypd.com', 'hashed_password_cici', 'Staf Keuangan', 'Keuangan', 'karyawan', '081868456987', 11, 2, 'aktif', '2025-10-04 06:18:48'),
(15, 'YPD015', 'Leonardo', 'leonardo@ypd.com', 'hashed_password_leonardo', 'Staf Training', 'Training', 'karyawan', '08186889751', 6, 0, 'aktif', '2025-10-04 06:26:05'),
(16, 'YPD016', 'Naomi', 'naomi@ypd.com', 'hashed_password_naomi', 'Staf Konsultasi', 'Konsultasi', 'karyawan', '081868456987', 0, 0, 'aktif', '2025-10-04 06:30:39'),
(17, 'YPD017', 'Aurora', 'aurora@ypd.com', 'hashed_password_aurora', 'Staf Konsultasi', 'Konsultasi', 'karyawan', '081868456988', 0, 0, 'aktif', '2025-10-04 06:35:02'),
(18, 'YPD018', 'Selena', 'selena@ypd.com', 'hashed_password_selena', 'Staf Wisma', 'Wisma', 'karyawan', '081868456978', 0, 0, 'aktif', '2025-10-04 06:38:26'),
(19, 'YPD019', 'Kelra', 'kelra@ypd.com', 'hashed_password_kelra', 'Staf Wisma', 'Wisma', 'karyawan', '081868456985', 0, 0, 'aktif', '2025-10-04 06:46:13'),
(20, 'YPD020', 'Lyra', 'lyra@ypd.com', 'hashed_password_lyra', 'Staf SDM', 'SDM', 'karyawan', '081868456987', 5, 0, 'aktif', '2025-10-04 06:47:18'),
(21, 'YPD021', 'Yovan', 'yovan@ypd.com', 'hashed_password_yovan', 'Staf SDM', 'SDM', 'karyawan', '081868456985', 4, 0, 'aktif', '2025-10-04 06:48:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_karyawan`
--
ALTER TABLE `data_karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `kode_karyawan` (`kode_karyawan`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data_karyawan`
--
ALTER TABLE `data_karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
