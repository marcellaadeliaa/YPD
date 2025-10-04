<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$user = $_SESSION['user'];
$id_pj = $user['id_karyawan'];

$riwayat_pribadi = [];
$sql = "SELECT tgl_mulai, jenis_cuti, status, waktu_persetujuan FROM pengajuan_cuti WHERE id_karyawan = ? ORDER BY tgl_mulai DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pj);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) { $riwayat_pribadi[] = $row; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Cuti Pribadi</title>
    </head>
<body>
<header></header>
<main>
    <div class="card">
        <h2 class="page-title">Riwayat Cuti Pribadi</h2>
        </div>
</main>
</body>
</html>