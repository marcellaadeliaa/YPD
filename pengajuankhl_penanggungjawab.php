<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$user = $_SESSION['user'];
$nama_pj = $user['nama_lengkap'];
$kode_pj = $user['kode_karyawan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengajuan KHL Pribadi</title>
    </head>
<body>
<header></header>
<main>
    <div class="form-container">
        <h2>Formulir Pengajuan KHL Pribadi</h2>
        <form action="proses_pengajuan.php" method="POST">
            <label>No. Kode Karyawan</label>
            <input type="text" value="<?= htmlspecialchars($kode_pj) ?>" readonly>
            <label>Nama Karyawan</label>
            <input type="text" value="<?= htmlspecialchars($nama_pj) ?>" readonly>
            <button type="submit">Kirim Pengajuan</button>
        </form>
    </div>
</main>
</body>
</html>