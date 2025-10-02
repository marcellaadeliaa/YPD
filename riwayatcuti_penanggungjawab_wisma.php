<?php
// FILE: riwayatcuti_penanggungjawab_wisma.php
session_start();

// --- DATA DUMMY ---
$divisi_pj = "wisma";
$riwayat_cuti_asli = [
    // Dikosongkan karena tidak ada riwayat cuti untuk Wisma
];

// --- LOGIKA FILTER ---
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';

$riwayat_cuti_filter = $riwayat_cuti_asli; // Akan tetap kosong

// Logika filter tidak akan berpengaruh karena data awal kosong
if ($start_date && $end_date) {
    // ...
}
if ($search_query) {
    // ...
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Cuti Divisi - Wisma</title>
    <style>
        :root {
            --primary-color: #1E105E;
            --secondary-color: #8897AE;
            --accent-color: #4A3F81;
            --card-bg: #FFFFFF;
            --text-light: #fff;
            --text-dark: #333;
            --shadow-light: rgba(0,0,0,0.1);
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; }
        header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li a { color: var(--text-dark); font-weight: 400; padding: 5px 20px; white-space: nowrap; }
        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); color: var(--text-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        h2 { color: var(--primary-color); font-size: 24px; margin-top: 0; }
        .filter-container { margin-bottom: 25px; padding: 20px; background-color: #f8f9fa; border-radius: 10px; display: flex; flex-wrap: wrap; align-items: flex-end; gap: 20px; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { font-weight: 600; font-size: 14px; display: block; margin-bottom: 8px; }
        .filter-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; }
        .filter-buttons { display: flex; gap: 10px; }
        .filter-buttons button, .filter-buttons a { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-filter { background: var(--accent-color); color: var(--text-light); }
        .btn-reset { background: #6c757d; color: var(--text-light); text-decoration: none; display: inline-block; line-height: 1.5; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .status-diterima { background-color: #d4edda; color: #155724; }
        .status-ditolak { background-color: #f8d7da; color: #721c24; }
        .status-menunggu { background-color: #fff3cd; color: #856404; }
        .status-diterima, .status-ditolak, .status-menunggu { padding: 5px 10px; border-radius: 15px; font-weight: 600; font-size: 12px; }
        .no-data { text-align: center; padding: 20px; color: #6c757d; }
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
            <li><a href="dashboardpenanggungjawab_wisma.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab_wisma.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab_wisma.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab_wisma.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab_wisma.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab_wisma.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab_wisma.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab_wisma.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab_wisma.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab_wisma.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab_wisma.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi_wisma.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab_wisma.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <div class="card">
        <h2>Riwayat Pengajuan Cuti (Divisi <?= htmlspecialchars(ucfirst($divisi_pj)) ?>)</h2>
        
        <div class="filter-container">
            <form action="" method="GET">
                <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
                    <div class="filter-group">
                        <label for="search">Cari Nama Karyawan</label>
                        <input type="text" id="search" name="search" placeholder="Masukkan nama..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">Filter</button>
                        <a href="riwayatcuti_penanggungjawab_wisma.php" class="btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal Cuti</th>
                    <th>Jenis Cuti</th>
                    <th>Status</th>
                    <th>Waktu Persetujuan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($riwayat_cuti_filter)): ?>
                    <?php foreach($riwayat_cuti_filter as $cuti): ?>
                    <tr>
                       
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">Tidak ada data riwayat cuti.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>