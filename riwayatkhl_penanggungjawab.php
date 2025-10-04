<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$divisi_pj = $_SESSION['user']['divisi'];
// ... (Logika filter dan query SELECT ... WHERE divisi = ?) ...
?>
<!DOCTYPE html>
<html>
<head><title>Riwayat KHL Divisi</title></head>
<body>
<header></header>
<main>
    <div class="card">
        <h2>Riwayat KHL Divisi <?= htmlspecialchars($divisi_pj) ?></h2>
        </div>
</main>
</body>
</html>