-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 03:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Table structure for table `data_pelamar`
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
-- Dumping data for table `data_pelamar`
--

INSERT INTO `data_pelamar` (`id`, `user_id`, `nama_lengkap`, `posisi_dilamar`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `nik`, `alamat_rumah`, `no_telp`, `email`, `agama`, `kontak_darurat`, `pendidikan_terakhir`, `surat_lamaran`, `cv`, `photo_formal`, `ijazah_transkrip`, `berkas_pendukung`, `ktp`, `status`, `status_seleksi`, `pengumuman`, `created_at`) VALUES
(3, 1, 'marcella adelia', 'Wisma', 'Laki-laki', 'tegal', '2000-03-10', '222324', 'jl. residen tegal', '0818', 'marcellaadelia06@gmail.com', 'Kristen', 'sss', 'SMA', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-09-28 10:22:33'),
(4, 2, 'marcella ', 'Training', 'Perempuan', 'tegal', '2006-03-10', '222324', 'jl. residen tegal', '0818', 'marcellaadelia1003@gmail.com', 'Katholik', 'sss', 'SMA', 'uploads/1759213604-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025.pdf', 'uploads/1759213604-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025.pdf', 'uploads/1759213604-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025.pdf', 'uploads/1759213604-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025.pdf', 'uploads/1759213604-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025.pdf', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-09-30 06:26:44'),
(5, 4, 'marcella ap', 'Training\r\n', 'Perempuan', 'tegal', '2000-03-10', '222324', 'tegal', '0818', 'marcellaadelia07@gmail.com', 'Katholik', 'sss', 'SMA', 'uploads/1759216978-Abstaksi.pdf', 'uploads/1759216978-Abstaksi.pdf', 'uploads/1759216978-Abstaksi.docx', 'uploads/1759216978-Abstaksi.pdf', 'uploads/1759216978-Abstaksi.pdf', NULL, 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-09-30 07:22:58'),
(6, 5, 'marcella adelia', 'Konsultasi', 'Perempuan', 'tegal', '2006-03-10', '222324', 'tegal', '0818', 'marcellaadelia06@gmail.com', 'Katholik', 'sss', 'SMA', 'uploads/1759218679-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025 (1).pdf', 'uploads/1759218679-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025 (1).pdf', 'uploads/1759218679-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025 (1).pdf', 'uploads/1759218679-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025 (1).pdf', 'uploads/1759218679-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025 (1).pdf', 'uploads/1759218679-24.N1.0004_Marcella Adelia Putri_Tugas CRM Meeting 29 September 2025 (1).pdf', 'Diterima', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-09-30 07:51:19');

-- --------------------------------------------------------

--
-- Table structure for table `lamaran`
--

CREATE TABLE `lamaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Menunggu Proses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 'marcellaadelia06@gmail.com', '707f132ed4b6cb900a8a63b045fc1dcf72067d541e03862305c5e78e28472fc38c45d769f3db3a402e5a4aac19cd987a601c', '2025-09-29 19:42:20', 0, '2025-09-29 16:42:20');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_cuti`
--

CREATE TABLE `pengajuan_cuti` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `jenis_cuti` varchar(50) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu PJ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_cuti_pj`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_khl`
--

CREATE TABLE `pengajuan_khl` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `tanggal_khl` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu PJ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `tanggal`, `status`) VALUES
(1, 'Pengumuman Seleksi Tahap 1', 'Tes seleksi tahap pertama akan dilaksanakan pada tanggal 30 September 2025. Silakan persiapkan diri dengan baik.', '2025-09-29 14:59:32', 'active'),
(2, 'Info Jadwal Wawancara', 'Bagi pelamar yang lulus seleksi administrasi, akan dihubungi via email untuk jadwal wawancara.', '2025-09-29 14:59:32', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman_pelamar`
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
-- Dumping data for table `pengumuman_pelamar`
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
(18, 6, 'Seleksi Kesehatan', 'Selamat marcella adelia! Anda diterima sebagai karyawan tetap. Selamat bergabung!', '2025-09-30', '2025-09-30 07:52:16');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman_umum`
--

CREATE TABLE `pengumuman_umum` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sisa_cuti`
--

CREATE TABLE `sisa_cuti` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sisa_cuti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `created_at`) VALUES
(1, '', 'marcellaadelia06@gmail.com', '$2y$10$IDfw5.PGi8gmnxb3N4apteA47WEQxU0raaIjHP1FVn9goGnoGJHD2', '2025-09-28 09:40:50'),
(2, '', 'marcellaadelia1003@gmail.com', '$2y$10$3STTMIcjJptqnI4paiNI.uFWD03II3QUblNpxpxUqn4SLtiSBS4Lm', '2025-09-30 06:25:09'),
(4, '', 'marcellaadelia07@gmail.com', '$2y$10$JPf5yS847fIq3d92uxvot.0dKnlccHktiZrsAZYQ.KkrHTdf1l3kq', '2025-09-30 07:21:32'),
(5, '', 'marcellaadelia08@gmail.com', '$2y$10$Hqxci5VEETSDKYsXCOa0LOyULs.M/gJXD8qXbDwFEi8giiiXJsmyu', '2025-09-30 07:50:15'),
(6, '', 'lauren@gmail.com', '$2y$10$04sTY/X5sG98okLJPRyl9uq38fmAFpjGQ22AVtUg9ryKfAjWdeTbG', '2025-09-30 12:30:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_pelamar`
--
ALTER TABLE `data_pelamar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_cuti_pj`
--
ALTER TABLE `pengajuan_cuti_pj`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumuman_pelamar`
--
ALTER TABLE `pengumuman_pelamar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumuman_umum`
--
ALTER TABLE `pengumuman_umum`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sisa_cuti`
--
ALTER TABLE `sisa_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data_pelamar`
--
ALTER TABLE `data_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lamaran`
--
ALTER TABLE `lamaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_cuti_pj`
--
ALTER TABLE `pengajuan_cuti_pj`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengumuman_pelamar`
--
ALTER TABLE `pengumuman_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pengumuman_umum`
--
ALTER TABLE `pengumuman_umum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sisa_cuti`
--
ALTER TABLE `sisa_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `data_pelamar`
--
ALTER TABLE `data_pelamar`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD CONSTRAINT `lamaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_cuti_pj`
--
ALTER TABLE `pengajuan_cuti_pj`
  ADD CONSTRAINT `pengajuan_cuti_pj_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  ADD CONSTRAINT `pengajuan_khl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sisa_cuti`
--
ALTER TABLE `sisa_cuti`
  ADD CONSTRAINT `sisa_cuti_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
