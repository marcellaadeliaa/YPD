<?php
// FILE: dashboardpenanggungjawab_konsultasi.php
$nama_pj = "Budi"; 
$jabatan = "Penanggung Jawab Divisi Konsultasi";
$divisi_pj = "konsultasi"; // Menggunakan huruf kecil untuk konsistensi di URL

// Data dummy untuk kartu
$cuti_menunggu = 2; 
$khl_menunggu = 0;
$total_karyawan_divisi = 8;

// Data dummy untuk tabel pengajuan terbaru
$latest_requests = [
    ['jenis' => 'Cuti', 'nama_karyawan' => 'Gita', 'created_at' => '2025-10-02'],
    ['jenis' => 'KHL', 'nama_karyawan' => 'Hadi', 'created_at' => '2025-09-29'],
    ['jenis' => 'Cuti', 'nama_karyawan' => 'Indra', 'created_at' => '2025-09-28'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penanggung Jawab - Konsultasi</title>
    <style>
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE; 
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF; 
            --text-color-light: #fff; 
            --text-color-dark: #2e1f4f; 
            --shadow-light: rgba(0,0,0,0.15); 
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
        .heading-section h1 { 
            font-size: 2.5rem; 
            margin: 0; 
        }
        .heading-section p { 
            font-size: 1.1rem; 
            margin-top: 5px; 
            opacity: 0.9; 
            margin-bottom: 30px;
        }
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
            display: flex; 
            flex-direction: column; 
        }
        .card h3 { 
            margin-top: 0; 
            font-size: 20px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 10px; 
            margin-bottom: 15px; 
        }
        .pending-count { 
            font-size: 3.5rem; 
            font-weight: 700; 
            color: var(--primary-color); 
        }
        .btn { 
            display: inline-block; 
            background: var(--accent-color); 
            color: var(--text-color-light); 
            padding: 12px 20px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            margin-top: auto; 
            text-align: center; 
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
        }
        .calendar-icon { 
            font-size: 3rem; 
            text-align: center; 
            margin: 20px 0; 
            color: var(--primary-color); 
        }
        .card p { 
            margin: 4px 0; 
            line-height: 1.6; 
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
            <li><a href="dashboardpenanggungjawab_konsultasi.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab_konsultasi.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab_konsultasi.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab_konsultasi.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab_konsultasi.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab_konsultasi.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab_konsultasi.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab_konsultasi.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab_konsultasi.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab_konsultasi.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab_konsultasi.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi_konsultasi.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab_konsultasi.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<main>
    <div class="heading-section">
        <h1>Welcome, <?= htmlspecialchars($nama_pj) ?>!</h1>
        <p><?= htmlspecialchars($jabatan) ?></p>
    </div>
    
    <div class="dashboard-grid">
        <div class="card">
            <h3>Cuti Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $cuti_menunggu ?></p>
            <a href="persetujuancuti_penanggungjawab_<?= $divisi_pj ?>.php" class="btn">Lihat Rincian</a>
        </div>
        <div class="card">
            <h3>KHL Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $khl_menunggu ?></p>
            <a href="persetujuankhl_penanggungjawab_<?= $divisi_pj ?>.php" class="btn">Lihat Rincian</a>
        </div>
        <div class="card">
            <h3>Total Karyawan Divisi</h3>
            <p class="pending-count"><?= $total_karyawan_divisi ?></p>
            <a href="karyawan_divisi_<?= $divisi_pj ?>.php" class="btn">Lihat Data</a>
        </div>

        <div class="card">
            <h3>Kalender Cuti Divisi</h3>
            <div class="calendar-icon">📅</div>
            <p>Akses kalender untuk melihat jadwal cuti karyawan di divisi Anda.</p>
            <a href="kalender_cuti_penanggungjawab_<?= $divisi_pj ?>.php" class="btn">Lihat Kalender Cuti</a>
        </div>

        <div class="card">
            <h3>Kalender KHL Divisi</h3>
            <div class="calendar-icon">📅</div>
            <p>Akses kalender untuk melihat jadwal KHL karyawan di divisi Anda.</p>
            <a href="kalender_khl_penanggungjawab_<?= $divisi_pj ?>.php" class="btn">Lihat Kalender KHL</a>
        </div>
    </div>
    <div class="card">
        <h3>Pengajuan Terbaru di Divisi Anda</h3>
        <table class="data-table">
            <thead><tr><th>Jenis</th><th>Nama Karyawan</th><th>Tanggal Pengajuan</th></tr></thead>
            <tbody>
                <?php if (empty($latest_requests)): ?>
                    <tr><td colspan="3" style="text-align:center;">Tidak ada pengajuan terbaru.</td></tr>
                <?php else: ?>
                    <?php foreach($latest_requests as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['jenis']) ?></td>
                            <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                            <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>