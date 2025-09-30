<?php
session_start();
require 'config.php';

// Pastikan pengguna memiliki role direktur sebelum mengakses halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'direktur') {
    // header("Location: login.php");
    // exit();
}

// Mengambil semua riwayat cuti dari database
$query_riwayat_cuti = "SELECT * FROM pengajuan_cuti ORDER BY created_at DESC";
$result_riwayat_cuti = $conn->query($query_riwayat_cuti);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Cuti Direktur</title>
    <style>
        body {
          margin:0;
          font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
          min-height:100vh;
          color:#fff;
        }
        header {
          background:#fff;
          padding:20px 40px;
          display:flex;
          justify-content:space-between;
          align-items:center;
          border-bottom:2px solid #34377c;
          flex-wrap:nowrap; /* fix supaya tombol tidak turun */
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
        nav li { position:relative; }
        nav a {
          text-decoration:none;
          color:#333;
          font-weight:600;
          padding:8px 4px;
          display:block;
        }
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
        .btn {
          display:inline-block;
          color:#fff;
          padding:8px 14px;
          border-radius:6px;
          text-decoration:none;
          font-weight:600;
          font-size:13px;
          text-align: center;
          border: none;
          cursor: pointer;
          margin-right:5px;
        }
        .btn-approve { background:#28a745; }
        .btn-reject { background:#dc3545; }
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
        }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        @media(max-width:768px){
          header{flex-direction:column;align-items:flex-start;}
          nav ul{flex-direction:column;gap:10px;width:100%;margin-top:15px;}
          nav li ul {
            position:static;
            border:none;
            box-shadow:none;
            padding-left: 20px;
          }
          .user-section {margin-top:10px;}
        }

        /* Tambahan tombol Pengajuan Cuti */
        .user-section {
          display: flex;
          align-items: center;
          gap: 15px;
        }
        .welcome-text {
          color: #2e1f4f;
          font-weight: 600;
        }
        .btn-pengajuan {
          background: #2e1f4f;
          color: #fff !important;
          padding: 10px 20px;
          border-radius: 8px;
          text-decoration: none;
          font-weight: 600;
          display: inline-flex;
          align-items: center;
        }
        .btn-pengajuan:hover {
          background: #4b2a7a;
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
          <li><a href="#">Karyawan ▾</a>
            <ul>
              <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
            </ul>
          </li>
          <li><a href="#">Profil ▾</a></li>
        </ul>
      </nav>

      <!-- Tambahan di kanan -->
      <div class="user-section">
        <span class="welcome-text">Welcome Pico, Direktur</span>
        <a href="pengajuan_cuti.php" class="btn-pengajuan">Pengajuan Cuti</a>
      </div>
    </header>

    <main>
        <h1>Riwayat Cuti Seluruh Karyawan</h1>
        <p style="color:#fff; margin-bottom: 20px; opacity: 0.9;">Lihat semua riwayat cuti karyawan di seluruh divisi.</p>
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Divisi</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th>Alasan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_riwayat_cuti->num_rows > 0): ?>
                        <?php while($row = $result_riwayat_cuti->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                                <td><?= htmlspecialchars($row['divisi']) ?></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_akhir'])) ?></td>
                                <td><?= htmlspecialchars($row['alasan']) ?></td>
                                <td>
                                    <button class="btn btn-approve">✔️</button>
                                    <button class="btn btn-reject">❌</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Data dummy jika kosong -->
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <tr>
                                <td>Karyawan <?= $i ?></td>
                                <td>Divisi <?= $i ?></td>
                                <td>Cuti Tahunan</td>
                                <td><?= date('d-m-Y') ?></td>
                                <td><?= date('d-m-Y', strtotime('+3 days')) ?></td>
                                <td>Alasan dummy ke-<?= $i ?></td>
                                <td>
                                    <button class="btn btn-approve">✔️</button>
                                    <button class="btn btn-reject">❌</button>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
      const table = document.querySelector(".data-table");

      table.addEventListener("click", function(e) {
        if (e.target.classList.contains("btn-approve")) {
          const cell = e.target.parentElement;
          cell.innerHTML = "<span style='color:green; font-weight:bold;'>Disetujui</span>";
        }
        if (e.target.classList.contains("btn-reject")) {
          const cell = e.target.parentElement;
          cell.innerHTML = "<span style='color:red; font-weight:bold;'>Ditolak</span>";
        }
      });
    });
    </script>
</body>
</html>
