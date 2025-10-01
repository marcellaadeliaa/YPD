<?php
// FILE: dashboard_direktur.php

// Tidak ada session_start() atau require 'config.php' untuk menghilangkan login
// dan koneksi ke database akan dilakukan di dalam skrip
require 'config.php';

// --- LOGIKA PENGAMBILAN DATA DARI DATABASE ---

// Asumsi: Anda sudah menginisialisasi $conn di config.php dan koneksi berhasil.

// Data Direktur (Placeholder) - Sebaiknya diambil dari session atau DB
$nama_direktur = "Pico"; 
$jabatan = "Direktur";

// 1. Mengambil jumlah cuti yang menunggu persetujuan
$query_cuti = "SELECT COUNT(id) AS total FROM pengajuan_cuti WHERE status = 'Menunggu Direktur'";
$result_cuti = $conn->query($query_cuti);
$cuti_menunggu = $result_cuti->fetch_assoc()['total'] ?? 0;

// 2. Mengambil jumlah KHL yang menunggu persetujuan
$query_khl = "SELECT COUNT(id) AS total FROM pengajuan_khl WHERE status = 'Menunggu Direktur'";
$result_khl = $conn->query($query_khl);
$khl_menunggu = $result_khl->fetch_assoc()['total'] ?? 0;

// 3. MENGHITUNG TOTAL PJ/Direktur
$query_total_direktur = "SELECT COUNT(kode_karyawan) AS total FROM data_karyawan WHERE role = 'direktur'";
$result_total_direktur = $conn->query($query_total_direktur);
$total_pj_direksi = $result_total_direktur->fetch_assoc()['total'] ?? 0; 

// 4. MENGHITUNG TOTAL KARYAWAN (Selain Direktur)
$query_total_karyawan = "SELECT COUNT(kode_karyawan) AS total FROM data_karyawan WHERE role != 'direktur'";
$result_total_karyawan = $conn->query($query_total_karyawan);
$total_karyawan = $result_total_karyawan->fetch_assoc()['total'] ?? 0; 

// 5. Mengambil 5 data cuti & KHL terbaru
// Ambil 5 data cuti terbaru
$query_cuti_latest = "SELECT 'Cuti' AS jenis, nama_karyawan, divisi, created_at FROM pengajuan_cuti ORDER BY created_at DESC LIMIT 5";
$result_cuti_latest = $conn->query($query_cuti_latest);

// Ambil 5 data KHL terbaru
$query_khl_latest = "SELECT 'KHL' AS jenis, nama_karyawan, divisi, created_at FROM pengajuan_khl ORDER BY created_at DESC LIMIT 5";
$result_khl_latest = $conn->query($query_khl_latest);

// Gabungkan kedua hasil query
$latest_requests = [];
if ($result_cuti_latest && $result_cuti_latest->num_rows > 0) {
    while($row = $result_cuti_latest->fetch_assoc()) {
        $latest_requests[] = $row;
    }
}
if ($result_khl_latest && $result_khl_latest->num_rows > 0) {
    while($row = $result_khl_latest->fetch_assoc()) {
        $latest_requests[] = $row;
    }
}
// Urutkan berdasarkan tanggal terbaru dan batasi 5
usort($latest_requests, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$latest_requests = array_slice($latest_requests, 0, 5);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Direktur</title>
    <style>
        /* CSS yang sudah ada tetap dipertahankan */
        :root {
            --primary-color: #1E105E;
            --secondary-color: #8897AE;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-color-light: #fff;
            --text-color-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
            --shadow-strong: rgba(0,0,0,0.25);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
            min-height: 100vh;
            color: var(--text-color-light);
            padding-bottom: 40px;
        }
        
        header {
            background: var(--card-bg);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent-color);
            flex-wrap: wrap;
            box-shadow: 0 4px 15px var(--shadow-light);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 500;
            font-size: 20px;
            color: var(--text-color-dark);
        }
        .logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 50%;
        }
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 30px;
        }
        nav li {
            position: relative;
        }
        nav a {
            text-decoration: none;
            color: var(--text-color-dark);
            font-weight: 600;
            padding: 8px 4px;
            display: block;
            transition: color 0.3s ease;
        }
        nav a:hover {
            color: var(--accent-color);
        }
        nav li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--card-bg);
            padding: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-light);
            min-width: 200px;
            z-index: 999;
        }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 20px; }
        nav li ul li a {
            color: var(--text-color-dark);
            font-weight: 400;
            white-space: nowrap;
        }
        
        main {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        /* == HEADER MAIN CONTENT & BUTTONS == */
        .header-main-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .heading-section {
            text-align: left;
        }
        .heading-section h1 {
            font-size: 2.5rem;
            margin: 0;
        }
        .heading-section p {
            font-size: 1.1rem;
            margin-top: 5px;
            opacity: 0.9;
        }
        
        /* Container untuk grup tombol */
        .action-buttons {
            display: flex;
            gap: 15px; /* Jarak antar tombol */
            flex-wrap: wrap; /* Pastikan responsif */
        }

        .action-buttons a {
            background: var(--accent-color);
            color: var(--text-color-light);
            padding: 15px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s;
            white-space: nowrap;
            /* Tambahan: pastikan tombol merata di layar kecil */
            flex-grow: 1; 
            text-align: center;
        }
        .action-buttons a:hover {
            background-color: #352d5c;
            transform: translateY(-2px);
        }
        /* Akhir style button */

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        .card {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 5px 20px var(--shadow-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow-strong);
        }
        .card h3 {
            margin-top: 0;
            font-size: 20px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .pending-count {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: auto;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            background: var(--accent-color);
            color: var(--text-color-light);
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-top: auto;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #352d5c;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
        .data-table tbody tr:hover {
            background-color: #f1f1f1;
        }

        @media(max-width:768px){
            header{flex-direction: column; align-items: flex-start;}
            nav ul{flex-direction: column; gap: 10px; width: 100%; margin-top: 15px;}
            nav li ul {
                position: static;
                border: none;
                box-shadow: none;
                padding-left: 20px;
            }
            .header-main-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .action-buttons {
                flex-direction: column;
                width: 100%;
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
                    <li><a href="data_direktur_pj.php">Data Direktur</a></li>
                </ul>
            </li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_direktur.php">Profil Direktur</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
            
        </ul>
    </nav>
</header>

<main>
    <div class="header-main-content">
        <div class="heading-section">
            <h1>Welcome, <?= htmlspecialchars($nama_direktur) ?>!</h1>
            <p><?= htmlspecialchars($jabatan) ?></p>
        </div>
        
        <div class="action-buttons">
            <a href="pengajuan_cuti_direktur.php" class="btn-cuti-direktur">Pengajuan Cuti</a>
            <a href="pengajuan_khl_direktur.php" class="btn-cuti-direktur">Pengajuan KHL</a> </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3>Cuti PJ/DIREKSI <br> Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $cuti_menunggu ?></p>
            <a href="persetujuan_cuti_direktur.php" class="btn">Lihat Rincian</a>
        </div>

        <div class="card">
            <h3>KHL PJ/DIREKSI <br> Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $khl_menunggu ?></p>
            <a href="persetujuan_khl_direktur.php" class="btn">Lihat Rincian</a>
        </div>

        <div class="card">
            <h3>Data PJ/DIREKSI</h3>
            <p class="pending-count"><?= $total_pj_direksi ?></p> 
            <a href="data_direkturPJ.php" class="btn">Lihat Rincian</a>
        </div>

        <div class="card">
            <h3>Cuti KARYAWAN <br> Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $cuti_menunggu ?></p>
            <a href="persetujuan_cuti_karyawan.php" class="btn">Lihat Rincian</a>
        </div>

        <div class="card">
            <h3>KHL KARYAWAN <br> Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $khl_menunggu ?></p>
            <a href="persetujuan_khl_karyawan.php" class="btn">Lihat Rincian</a>
        </div>

        <div class="card">
            <h3>Data Karyawan</h3>
            <p class="pending-count"><?= $total_karyawan ?></p> 
            <a href="data_karyawan_direktur.php" class="btn">Lihat Rincian</a>
        </div>
        
    </div>
    
    <div class="card">
        <h3>5 Pengajuan Terbaru</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal Pengajuan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($latest_requests)): ?>
                    <?php foreach($latest_requests as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['jenis']) ?></td>
                            <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($row['divisi']) ?></td>
                            <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Tidak ada pengajuan terbaru.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>