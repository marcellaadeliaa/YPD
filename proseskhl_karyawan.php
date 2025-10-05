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

// Ambil data karyawan untuk mendapatkan divisi, jabatan, dan role
$query_karyawan = "SELECT divisi, jabatan, role FROM data_karyawan WHERE kode_karyawan = ?";
$stmt_karyawan = mysqli_prepare($conn, $query_karyawan);
mysqli_stmt_bind_param($stmt_karyawan, "s", $nik);
mysqli_stmt_execute($stmt_karyawan);
$result_karyawan = mysqli_stmt_get_result($stmt_karyawan);
$karyawan = mysqli_fetch_assoc($result_karyawan);

if (!$karyawan) {
    header("Location: formkhlkaryawan.php?status=error&message=Data karyawan tidak ditemukan");
    exit();
}

$divisi = $karyawan['divisi'];
$jabatan = $karyawan['jabatan'];
$role = $karyawan['role'];

mysqli_stmt_close($stmt_karyawan);

// Cek dan buat tabel jika belum ada
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'data_pengajuan_khl'");
if (mysqli_num_rows($check_table) == 0) {
    // Tabel tidak ada, buat tabel dengan struktur yang lengkap
    $create_table = "CREATE TABLE `data_pengajuan_khl` (
        `id_khl` int(11) NOT NULL AUTO_INCREMENT,
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
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_khl`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!mysqli_query($conn, $create_table)) {
        header("Location: formkhlkaryawan.php?status=error&message=Gagal membuat tabel database");
        exit();
    }
} else {
    // Periksa dan tambahkan kolom yang belum ada
    $columns_to_check = [
        'divisi' => "ALTER TABLE data_pengajuan_khl ADD COLUMN divisi VARCHAR(50) NOT NULL AFTER kode_karyawan",
        'jabatan' => "ALTER TABLE data_pengajuan_khl ADD COLUMN jabatan VARCHAR(50) NOT NULL AFTER divisi",
        'role' => "ALTER TABLE data_pengajuan_khl ADD COLUMN role ENUM('karyawan','direktur','admin','penanggung jawab') NOT NULL DEFAULT 'karyawan' AFTER jabatan",
        'proyek' => "ALTER TABLE data_pengajuan_khl ADD COLUMN proyek VARCHAR(100) NOT NULL AFTER role"
    ];
    
    foreach ($columns_to_check as $column_name => $alter_query) {
        $check_column = mysqli_query($conn, "SHOW COLUMNS FROM data_pengajuan_khl LIKE '$column_name'");
        if (mysqli_num_rows($check_column) == 0) {
            if (!mysqli_query($conn, $alter_query)) {
                header("Location: formkhlkaryawan.php?status=error&message=Gagal menambahkan kolom $column_name");
                exit();
            }
        }
    }
}

// Insert data dengan informasi lengkap
$sql = "INSERT INTO data_pengajuan_khl 
        (kode_karyawan, divisi, jabatan, role, proyek, tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, 
         tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl, status_khl) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssssss", 
        $nik, $divisi, $jabatan, $role, $proyek, $tanggal_khl, $jam_mulai_kerja, $jam_akhir_kerja,
        $tanggal_cuti_khl, $jam_mulai_cuti_khl, $jam_akhir_cuti_khl);
    
    if (mysqli_stmt_execute($stmt)) {
        // Redirect ke dashboard dengan pesan sukses
        header("Location: dashboardkaryawan.php?status=success&message=Pengajuan KHL berhasil dikirim!");
    } else {
        $error_msg = "Gagal menyimpan data: " . mysqli_error($conn);
        header("Location: formkhlkaryawan.php?status=error&message=" . urlencode($error_msg));
    }
    mysqli_stmt_close($stmt);
} else {
    $error_msg = "Gagal mempersiapkan statement: " . mysqli_error($conn);
    header("Location: formkhlkaryawan.php?status=error&message=" . urlencode($error_msg));
}

mysqli_close($conn);
?>                       