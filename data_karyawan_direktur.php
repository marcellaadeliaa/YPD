<?php
// FILE: data_karyawan_direktur.php

session_start();

// --- Koneksi ke Database ---
// Pastikan file 'config.php' ada dan berisi koneksi database ($conn)
require 'config.php'; 

// Cek autentikasi Direktur (dipertahankan dari kode Anda sebelumnya)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'direktur') {
    // Jika tidak ada session atau role bukan direktur, arahkan ke login.
    // header("Location: login.php");
    // exit();
}

// 1. Query untuk mengambil SEMUA data Karyawan
$query_data_karyawan = "
    SELECT 
        kode_karyawan, 
        nama_lengkap, 
        jabatan, 
        role, 
        no_telp, 
        email, 
        sisa_cuti_tahunan, 
        status_aktif 
    FROM data_karyawan 
    ORDER BY nama_lengkap ASC"; // Mengambil SEMUA data

$result_karyawan = $conn->query($query_data_karyawan);

// Tutup koneksi database setelah mengambil data
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan | Direktur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS ini dipertahankan sama persis seperti yang Anda berikan */
        body {
          margin:0;
          font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
          min-height:100vh;
          color:#fff;
        }
        header {
          background:rgba(255,255,255,1);
          padding:20px 40px;
          display:flex;
          justify-content:space-between;
          align-items:center;
          border-bottom:2px solid #34377c;
          flex-wrap:wrap;
        }
        .logo {
          display:flex;
          align-items:center;
          gap:16px;
          font-weight:500;
          font-size:20px;
          color:#2e1f4f;
        }
        .logo img {
          width: 50px;
          height: 50px;
          object-fit: contain;
          border-radius: 50%;
        }
        nav ul {
          list-style:none;
          margin:0;
          padding:0;
          display:flex;
          gap:30px;
        }
        nav li {
          position:relative;
        }
        nav a {
          text-decoration:none;
          color:#333;
          font-weight:600;
          padding:8px 4px;
          display:block;
        }

        /* ===== DROPDOWN ===== */
        nav li ul {
          display:none;
          position:absolute;
          top:100%;
          left:0;
          background:#fff;
          padding:10px 0;
          border-radius:8px;
          box-shadow:0 2px 8px rgba(0,0,0,.15);
          min-width:200px;
          z-index:999;
        }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a {
          color:#333;
          font-weight:400;
          white-space:nowrap;
        }

        /* ===== MAIN CONTENT ===== */
        main {
          max-width:1200px;
          margin:40px auto;
          padding:0 20px;
        }
        h1 {
          text-align:left;
          font-size:28px;
          margin-bottom:10px;
          color:#fff;
        }
        .card {
          background:#fff;
          color:#2e1f4f;
          border-radius:20px;
          padding:30px 40px;
          box-shadow:0 2px 10px rgba(0,0,0,0.15);
          display: flex;
          flex-direction: column;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            color: #2e1f4f;
        }
        .data-table th, .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center; /* Menyesuaikan tampilan header */
        }
        .data-table td {
            text-align: center; /* Default teks rata tengah */
        }
        .data-table td:nth-child(2) {
            text-align: left; /* Nama Karyawan rata kiri */
        }
        .data-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .nav-active {
            border-bottom: 2px solid #34377c;
        }
        /* Tambahan styling untuk teks deskripsi */
        .info-text {
            color:#fff; 
            margin-bottom: 20px; 
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <header>
      <div class="logo">
        <img src="image/namayayasan.png" alt="Logo Yayasan">
        <span>Yayasan Purba Danarta</span>
      </div>
      <nav>
        <ul>
          <li><a href="dashboard_direktur.php">Beranda</a></li>
          <li><a href="#">Cuti ▾</a>
            <ul>
              <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
              <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li>
            </ul>
          </li>
          <li><a href="#">KHL ▾</a>
            <ul>
              <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
              <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li>
            </ul>
          </li>
          <li class="nav-active"><a href="#">Karyawan ▾</a>
            <ul>
              <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
              <li><a href="data_direktur_pj.php">Data Direktur</a></li> 
            </ul>
          </li>
          <li><a href="#">Profil ▾</a></li>
        </ul>
      </nav>
    </header>

    <main>
        <h1>Data Karyawan</h1>
        <p class="info-text">Lihat semua data dan informasi detail seluruh karyawan perusahaan.</p>
        
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Kode Karyawan</th>
                        <th>Nama Karyawan</th>
                        <th>Jabatan</th> 
                        <th>Role Sistem</th>
                        <th>No Telepon</th>
                        <th>Email</th>
                        <th>Sisa Cuti Tahunan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_karyawan->num_rows > 0): ?>
                        <?php while($row = $result_karyawan->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode_karyawan']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['jabatan']) ?></td> 
                                <td><?= htmlspecialchars(strtoupper($row['role'])) ?></td> 
                                <td><?= htmlspecialchars($row['no_telp']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['sisa_cuti_tahunan']) ?> hari</td>
                                <td><?= htmlspecialchars($row['status_aktif']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tidak ada data karyawan ditemukan di database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>