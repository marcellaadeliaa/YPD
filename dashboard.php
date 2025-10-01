<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];

// Mapping role ke dashboard
$dashboard_map = [
    'admin' => 'dashboardadmin.php',
    'karyawan' => 'dashboardkaryawan.php',
    'penanggung jawab' => 'dashboardpenanggungjawab.php',
    'direktur' => 'dashboarddirektur.php'
];

if (isset($dashboard_map[$role])) {
    header("Location: " . $dashboard_map[$role]);
} else {
    header("Location: login_karyawan.php?error=invalid_role");
}
exit();
?>