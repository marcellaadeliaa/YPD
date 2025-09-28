-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Sep 2025 pada 18.44
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

INSERT INTO `data_pelamar` (`id`, `user_id`, `nama_lengkap`, `posisi_dilamar`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `nik`, `alamat_rumah`, `no_telp`, `email`, `agama`, `kontak_darurat`, `pendidikan_terakhir`, `surat_lamaran`, `cv`, `photo_formal`, `ijazah_transkrip`, `berkas_pendukung`, `ktp`, `status`, `status_seleksi`, `pengumuman`, `created_at`) VALUES
(3, 1, 'marcella adelia', 'divisi1', 'Laki-laki', 'tegal', '2000-03-10', '222324', 'jl. residen tegal', '0818', 'marcellaadelia06@gmail.com', 'Kristen', 'sss', 'SMA', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'uploads/1759054953-Form Pendaftaran Pelamar.png', 'Menunggu Proses', 'Proses seleksi sedang berlangsung', 'Belum ada pengumuman saat ini.', '2025-09-28 10:22:33');

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
(1, '', 'marcellaadelia06@gmail.com', '$2y$10$81pl/UjSrJg9IcPvYGdnWucxJRd50Yx8ZB9ry8jjjR/RJuX4YbkrW', '2025-09-28 09:40:50');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_pelamar`
--
ALTER TABLE `data_pelamar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

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
-- AUTO_INCREMENT untuk tabel `data_pelamar`
--
ALTER TABLE `data_pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `data_pelamar`
--
ALTER TABLE `data_pelamar`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
