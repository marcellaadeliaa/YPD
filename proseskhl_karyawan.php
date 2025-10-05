<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit();
}

// Cek apakah form dikirim dengan method POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: formkhlkaryawan.php?status=error&message=Metode request tidak valid");
    exit();
}

// Ambil data dari form
$nik = $_POST['nik'];
$proyek = $_POST['proyek'];
$tanggal_khl = $_POST['tanggal_khl'];
$jam_mulai_kerja = $_POST['jam_mulai_kerja'];
$jam_akhir_kerja = $_POST['jam_akhir_kerja'];
$tanggal_cuti_khl = $_POST['tanggal_cuti_khl'];
$jam_mulai_cuti_khl = $_POST['jam_mulai_cuti_khl'];
$jam_akhir_cuti_khl = $_POST['jam_akhir_cuti_khl'];

// Validasi data wajib
if (empty($nik) || empty($proyek) || empty($tanggal_khl) || empty($jam_mulai_kerja) || 
    empty($jam_akhir_kerja) || empty($tanggal_cuti_khl) || empty($jam_mulai_cuti_khl) || 
    empty($jam_akhir_cuti_khl)) {
    header("Location: formkhlkaryawan.php?status=error&message=Semua field harus diisi");
    exit();
}

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'data_pengajuan_khl'");
if (mysqli_num_rows($check_table) == 0) {
    // Tabel tidak ada, buat tabel dengan struktur yang benar
    $create_table = "CREATE TABLE `data_pengajuan_khl` (
        `id_khl` int(11) NOT NULL AUTO_INCREMENT,
        `kode_karyawan` varchar(20) NOT NULL,
        `proyek` varchar(100) NOT NULL,
        `tanggal_khl` date NOT NULL,
        `jam_mulai_kerja` time NOT NULL,
        `jam_akhir_kerja` time NOT NULL,
        `tanggal_cuti_khl` date NOT NULL,
        `jam_mulai_cuti_khl` time NOT NULL,
        `jam_akhir_cuti_khl` time NOT NULL,
        `status_khl` enum('pending','disetujui','ditolak') DEFAULT 'pending',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_khl`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!mysqli_query($conn, $create_table)) {
        header("Location: formkhlkaryawan.php?status=error&message=Gagal membuat tabel database");
        exit();
    }
} else {
    // Tabel sudah ada, cek struktur kolom
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM data_pengajuan_khl LIKE 'proyek'");
    if (mysqli_num_rows($check_columns) == 0) {
        // Kolom proyek tidak ada, tambahkan kolom
        $add_column = "ALTER TABLE data_pengajuan_khl ADD COLUMN proyek VARCHAR(100) NOT NULL AFTER kode_karyawan";
        if (!mysqli_query($conn, $add_column)) {
            header("Location: formkhlkaryawan.php?status=error&message=Gagal menambahkan kolom proyek");
            exit();
        }
    }
}

// Insert data
$sql = "INSERT INTO data_pengajuan_khl 
        (kode_karyawan, proyek, tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, 
         tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl, status_khl) 
        VALUES ('$nik', '$proyek', '$tanggal_khl', '$jam_mulai_kerja', '$jam_akhir_kerja',
                '$tanggal_cuti_khl', '$jam_mulai_cuti_khl', '$jam_akhir_cuti_khl', 'pending')";

if (mysqli_query($conn, $sql)) {
    // Redirect ke dashboard dengan pesan sukses
    header("Location: dashboardkaryawan.php?status=success&message=Pengajuan KHL berhasil dikirim!");
} else {
    $error_msg = "Gagal menyimpan data: " . mysqli_error($conn);
    header("Location: formkhlkaryawan.php?status=error&message=" . urlencode($error_msg));
}

mysqli_close($conn);
?>