<?php
// FILE: dashboard.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];

// Mapping role ke dashboard yang sesuai
$dashboard_map = [
    'admin'            => 'dashboardadmin.php',
    'karyawan'         => 'dashboardkaryawan.php',
    'direktur'         => 'dashboarddirektur.php',
    'penanggung jawab' => 'dashboard_penanggungjawab.php' // SEMUA penanggung jawab ke satu file ini
];

// Arahkan pengguna ke dashboard yang benar
if (isset($dashboard_map[$role])) {
    header("Location: " . $dashboard_map[$role]);
    exit();
} else {
    // Jika role tidak dikenali, kembalikan ke login
    header("Location: login_karyawan.php?error=invalid_role");
    exit();
}
?>