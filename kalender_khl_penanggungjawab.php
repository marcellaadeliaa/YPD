<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$divisi_pj = $_SESSION['user']['divisi'];

$khl_by_date = [];
$sql = "SELECT nama_karyawan, tanggal_kerja, status FROM pengajuan_khl WHERE divisi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $divisi_pj);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $date_key = $row['tanggal_kerja'];
    if (!isset($khl_by_date[$date_key])) { $khl_by_date[$date_key] = []; }
    $khl_by_date[$date_key][] = ['nama_karyawan' => $row['nama_karyawan'], 'status' => $row['status']];
}
// ... (Logika kalender lainnya sama seperti kalender cuti) ...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kalender KHL Divisi</title>
    </head>
<body>
<header></header>
<main>
    <div class="card">
        </div>
</main>
</body>
</html>