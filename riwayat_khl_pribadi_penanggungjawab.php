<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$user = $_SESSION['user'];
$id_pj = $user['id_karyawan'];
// ... (Logika sama seperti riwayat cuti pribadi, tapi query ke tabel pengajuan_khl) ...
?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat KHL Pribadi</title>
    </head>
<body>
<header></header>
<main>
    <div class="card">
        <h2 class="page-title">Riwayat KHL Pribadi</h2>
        </div>
</main>
</body>
</html>