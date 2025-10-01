<?php
// FILE: data_karyawan_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI DAN PENGAMBILAN DATA
// ===========================================

session_start();

// --- Koneksi ke Database ---
// Pastikan file 'config.php' ada dan berisi koneksi database ($conn)
// Jika Anda tidak memiliki config.php, ganti baris ini dengan koneksi manual:
/*
$conn = new mysqli("localhost", "root", "", "ypd_ibd");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
*/
require 'config.php'; 

// Cek autentikasi Direktur (dipertahankan dari kode Anda sebelumnya)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'direktur') {
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
    ORDER BY nama_lengkap ASC";

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
        /* ==================== */
        /* CSS STYLING */
        /* ==================== */
        :root {
            --primary-color: #1E105E;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-color-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
        }
        body {
          margin:0;
          font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
          min-height:100vh;
          color:#fff;
          padding-bottom: 40px;
        }
        header {
          background:var(--card-bg);
          padding:20px 40px;
          display:flex;
          justify-content:space-between;
          align-items:center;
          box-shadow: 0 4px 15px var(--shadow-light);
          border-bottom:2px solid var(--accent-color);
          flex-wrap:wrap;
        }
        .logo {
          display:flex;
          align-items:center;
          gap:16px;
          font-weight:500;
          font-size:20px;
          color:var(--text-color-dark);
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
          color:var(--text-color-dark);
          font-weight:600;
          padding:8px 4px;
          display:block;
          transition: color 0.3s ease;
        }
        nav a:hover { color: var(--accent-color); }
        .nav-active > a {
            border-bottom: 2px solid var(--accent-color);
            color: var(--accent-color);
        }

        /* ===== DROPDOWN ===== */
        nav li ul {
          display:none;
          position:absolute;
          top:100%;
          left:0;
          background:var(--card-bg);
          padding:10px 0;
          border-radius:8px;
          box-shadow:0 2px 10px var(--shadow-light);
          min-width:200px;
          z-index:999;
        }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a {
          color:var(--text-color-dark);
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
          background:var(--card-bg);
          color:var(--text-color-dark);
          border-radius:20px;
          padding:30px 40px;
          box-shadow:0 5px 20px var(--shadow-light);
        }
        .data-table-container {
            overflow-x: auto; /* Memungkinkan tabel di-scroll secara horizontal di layar kecil */
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            color: var(--text-color-dark);
            min-width: 900px; /* Lebar minimum agar tidak terlalu sempit */
        }
        .data-table th, .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center; 
        }
        .data-table td {
            text-align: center; 
        }
        .data-table td:nth-child(2) {
            text-align: left; /* Nama Karyawan rata kiri */
        }
        .data-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .info-text {
            color:#fff; 
            margin-bottom: 20px; 
            opacity: 0.9;
        }

        /* Responsive Styles for Table */
        @media (max-width: 768px) {
            header{flex-direction: column; align-items: flex-start;} 
            nav ul{flex-direction: column; gap: 10px; width: 100%; margin-top: 15px;} 
            nav li ul { position: static; border: none; box-shadow: none; padding-left: 20px; }
            
            .data-table {
                /* Mengatur ulang tabel di mobile. Karena terlalu banyak kolom, 
                   kita biarkan scroll horizontal */
                display: block;
                width: 900px; /* Memaksa lebar agar bisa di-scroll */
            }
            .data-table-container {
                -webkit-overflow-scrolling: touch; /* Untuk iOS smooth scrolling */
            }
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
            <li><a href="dashboarddirektur.php">Beranda</a></li>
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
        
                </ul>
            </li>
            <li><a href="#">Pelamar ▾</a>
                <ul>
                    <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
                </ul>
            </li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_direktur.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
      </nav>
    </header>

    <main>
        <h1>Data Karyawan</h1>
        <p class="info-text">Lihat semua data dan informasi detail seluruh karyawan perusahaan.</p>
        
        <div class="card">
            <div class="data-table-container">
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
        </div>
    </main>
</body>
</html>