<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$divisi_pj = $_SESSION['user']['divisi'];
// ... (Logika approve/reject dan query SELECT ... WHERE divisi = ? AND status = 'Menunggu') ...
?>
<!DOCTYPE html>
<html>
<head><title>Persetujuan KHL</title></head>
<body>
<header></header>
<main>
    <div class="card">
        <h2>Persetujuan KHL (Divisi <?= htmlspecialchars($divisi_pj) ?>)</h2>
        </div>
</main>
</body>
</html>