<?php
// FILE: riwayatkhl_direktur.php

session_start(); 

// Asumsi 'config.php' berisi koneksi database ($conn)
require 'config.php';

// --- 1. Data Direktur (Untuk Header) ---
$nama_direktur = $_SESSION['nama_user'] ?? "Direktur"; 
$jabatan = $_SESSION['jabatan_user'] ?? "Direktur"; 


// --- 2. Ambil Data Riwayat KHL SELURUH KARYAWAN (Scope Direktur) ---

$riwayat_khl = [];

// Query untuk mengambil data KHL SEMUA karyawan (tanpa filter divisi)
$sql = "SELECT nama_karyawan, divisi, tanggal_khl, status 
        FROM pengajuan_khl 
        ORDER BY created_at DESC"; // Urutkan dari yang terbaru diajukan

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // Logika Penyederhanaan Status (dari database ke badge UI)
        $status_sederhana = '';
        if (strpos($row['status'], 'Disetujui') !== false) {
            $status_sederhana = 'Disetujui';
        } elseif (strpos($row['status'], 'Ditolak') !== false) {
            $status_sederhana = 'Ditolak';
        } else {
            // Status menunggu, termasuk 'Menunggu PJ', 'Menunggu HRD', 'Menunggu Direktur'
            $status_sederhana = 'Menunggu Persetujuan'; 
        }

        $riwayat_khl[] = [
            'nama_karyawan' => $row['nama_karyawan'],
            'divisi' => $row['divisi'], // Kolom Divisi ditambahkan untuk Direktur
            'tgl_khl' => $row['tanggal_khl'],
            'status' => $status_sederhana,
        ];
    }
}

// Tutup koneksi
$conn->close();

// --- 3. Fungsi Utility untuk Badge ---
function getStatusBadge($status) {
    if ($status == 'Disetujui') return '<span class="badge badge-success">Disetujui</span>';
    if ($status == 'Ditolak') return '<span class="badge badge-danger">Ditolak</span>';
    return '<span class="badge badge-warning">Menunggu Persetujuan</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat KHL Seluruh Karyawan</title>
    <style>
        /* ================================================= */
        /* CSS DIAMBIL DARI dashboard_direktur.php */
        /* ================================================= */
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
        
        nav li:hover > ul { 
            display: block; 
        } 
        
        nav li ul li { 
            padding: 5px 20px; 
        } 
        
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
        
        .card { 
            background: var(--card-bg); 
            color: var(--text-color-dark); 
            border-radius: 20px; 
            padding: 30px 40px; 
            box-shadow: 0 5px 20px var(--shadow-light); 
        } 

        .welcome-section {
            text-align: left;
            margin-bottom: 30px;
        }
        .welcome-section h1 {
            font-size: 2.5rem;
            margin: 0;
            color: var(--text-color-light);
        }
        .welcome-section p {
            font-size: 1.1rem;
            margin-top: 5px;
            opacity: 0.9;
            color: var(--text-color-light);
        }

        .card h2 {
            margin-top: 0;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 25px;
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
        
        /* Badge Styles */
        .badge { 
            color: #fff; 
            padding: 5px 10px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: 600; 
            display: inline-block; 
        }
        
        .badge-success { 
            background-color: #28a745; 
        }
        
        .badge-danger { 
            background-color: #dc3545; 
        }
        
        .badge-warning { 
            background-color: #ffc107; 
            color: #212529; 
        }
        
        /* Responsive Table Styles */
        @media(max-width:768px){ 
            header{flex-direction: column; align-items: flex-start;} 
            nav ul{flex-direction: column; gap: 10px; width: 100%; margin-top: 15px;} 
            nav li ul { position: static; border: none; box-shadow: none; padding-left: 20px; } 
            
            .data-table, .data-table tbody, .data-table tr, .data-table td {
                display: block;
                width: 100%;
            }
            .data-table thead { display: none; } 
            .data-table tr {
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }
            .data-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .data-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px);
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }
            .card {
                padding: 20px;
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
            <li style="border-bottom: 2px solid var(--accent-color);"><a href="#" style="color: var(--accent-color);">KHL ▾</a> 
                <ul> 
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li> 
                    <li><a href="riwayat_khl_direktur.php" style="color: var(--accent-color); font-weight: 700;">Riwayat KHL</a></li> 
                    <li><a href="pengajuan_khl_direktur.php">Pengajuan KHL</a></li> 
                </ul> 
            </li> 
            <li><a href="#">Karyawan ▾</a> 
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
    <div class="welcome-section">
        <h1>Riwayat KHL</h1>
        <p>Anda login sebagai <?= htmlspecialchars($jabatan) ?></p>
    </div>

    <div class="card">
        <h2>Riwayat Pengajuan KHL Seluruh Karyawan</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th> 
                    <th>Tanggal KHL</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($riwayat_khl)): 
                    foreach($riwayat_khl as $khl): ?>
                    <tr>
                        <td data-label="Nama Karyawan"><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                        <td data-label="Divisi"><?= htmlspecialchars($khl['divisi']) ?></td>
                        <td data-label="Tanggal KHL"><?= date('d-m-Y', strtotime($khl['tgl_khl'])) ?></td>
                        <td data-label="Status"><?= getStatusBadge($khl['status']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" style="text-align:center;">Belum ada riwayat pengajuan KHL.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>