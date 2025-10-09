<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];

$dashboard_map = [
    'admin'            => 'dashboardadmin.php',
    'karyawan'         => 'dashboardkaryawan.php',
    'direktur'         => 'dashboarddirektur.php',
    'penanggung jawab' => 'dashboard_penanggungjawab.php' 
];

if (isset($dashboard_map[$role])) {
    header("Location: " . $dashboard_map[$role]);
    exit();
} else {
    header("Location: login_karyawan.php?error=invalid_role");
    exit();
}
?>