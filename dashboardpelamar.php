<?php
session_start();
require 'config.php'; // file koneksi ke database

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pelamar
$queryUser = $conn->prepare("SELECT nama_lengkap FROM data_pelamar WHERE id = ?");
$queryUser->bind_param("i", $user_id);
$queryUser->execute();
$resultUser = $queryUser->get_result();
$user = $resultUser->fetch_assoc();
$nama_user = $user['nama_lengkap'] ?? 'Pelamar';

// Ambil status lamaran
$queryStatus = $conn->prepare("SELECT status FROM lamaran WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$queryStatus->bind_param("i", $user_id);
$queryStatus->execute();
$resultStatus = $queryStatus->get_result();
$rowStatus = $resultStatus->fetch_assoc();
$status = $rowStatus['status'] ?? 'Belum ada status';

// Ambil pengumuman terbaru
$queryPengumuman = $conn->query("SELECT isi FROM pengumuman ORDER BY tanggal DESC LIMIT 1");
$rowPengumuman = $queryPengumuman->fetch_assoc();
$pengumuman = $rowPengumuman['isi'] ?? 'Belum ada pengumuman saat ini.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Pelamar</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to bottom, #1E105E, #8897AE);
      color: #333;
    }
    header {
      background: #fff;
      padding: 10px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    header img {
      height: 50px;
    }
    nav a {
      margin-left: 20px;
      text-decoration: none;
      font-weight: 600;
      color: #000;
    }
    nav a:hover {
      color: #4A3AFF;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }
    h2 {
      color: #fff;
    }
    .card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin: 20px 0;
    }
    .card h3 {
      margin: 0 0 10px 0;
      color: #333;
    }
    .logout {
      margin-top: 30px;
      display: inline-block;
      background: #4A3AFF;
      color: #fff;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
    }
    .logout:hover {
      background: #362ECC;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="img/logo.png" alt="Logo">
    </div>
    <nav>
      <a href="#">Beranda</a>
      <a href="#">Profil</a>
    </nav>
  </header>

  <div class="container">
    <h2>Welcome, <?= htmlspecialchars($nama_user) ?>!</h2>

    <div class="card">
      <h3>Status</h3>
      <p><?= htmlspecialchars($status) ?></p>
    </div>

    <div class="card">
      <h3>Pengumuman</h3>
      <p><?= htmlspecialchars($pengumuman) ?></p>
    </div>

    <a href="logout.php" class="logout">Logout</a>
  </div>
</body>
</html>
