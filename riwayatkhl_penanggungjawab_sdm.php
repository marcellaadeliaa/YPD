<?php
// FILE: riwayatkhl_penanggungjawab_sdm.php
session_start();
$divisi_pj = "sdm";
$riwayat_khl_asli = [
    [ 'id' => 12, 'nama_karyawan' => 'Hana', 'projek' => 'Rekrutmen Karyawan Baru', 'tanggal_kerja' => '2025-10-18', 'jam_mulai_kerja' => '09:00:00', 'jam_selesai_kerja' => '13:00:00', 'tanggal_libur' => '2025-10-20', 'status' => 'Menunggu' ],
    [ 'id' => 15, 'nama_karyawan' => 'Gilang', 'projek' => 'Evaluasi Kinerja', 'tanggal_kerja' => '2025-09-15', 'jam_mulai_kerja' => '10:00:00', 'jam_selesai_kerja' => '17:00:00', 'tanggal_libur' => '2025-09-22', 'status' => 'Diterima' ],
];
// Logika Filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';
$riwayat_khl_filter = $riwayat_khl_asli;
if ($start_date && $end_date) {
    $riwayat_khl_filter = array_filter($riwayat_khl_filter, function($k) use ($start_date, $end_date) { return $k['tanggal_kerja'] >= $start_date && $k['tanggal_kerja'] <= $end_date; });
}
if ($search_query) {
    $riwayat_khl_filter = array_filter($riwayat_khl_filter, function($k) use ($search_query) { return stripos($k['nama_karyawan'], $search_query) !== false; });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat KHL Divisi - SDM</title>
    <style>
        :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4A3F81; --card-bg: #FFFFFF; --text-light: #fff; --text-dark: #333; --shadow-light: rgba(0,0,0,0.1); }
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
        main { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
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
        .table-responsive { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; white-space: nowrap; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .status-diterima, .status-ditolak, .status-menunggu { padding: 5px 10px; border-radius: 15px; font-weight: 600; font-size: 12px; }
        .status-diterima { background-color: #d4edda; color: #155724; }
        .status-ditolak { background-color: #f8d7da; color: #721c24; }
        .status-menunggu { background-color: #fff3cd; color: #856404; }
        .no-data { text-align: center; padding: 20px; color: #6c757d; }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboardpenanggungjawab_sdm.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab_sdm.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab_sdm.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab_sdm.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab_sdm.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab_sdm.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab_sdm.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab_sdm.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab_sdm.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab_sdm.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab_sdm.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi_sdm.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab_sdm.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<main>
    <div class="card">
        <h2>Riwayat Pengajuan KHL (Divisi <?= strtoupper($divisi_pj) ?>)</h2>
        <div class="filter-container">
            <form action="" method="GET">
                <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
                    <div class="filter-group">
                        <label for="search">Cari Nama</label>
                        <input type="text" id="search" name="search" placeholder="Masukkan nama..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="start_date">Dari Tgl Kerja</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="end_date">Sampai Tgl Kerja</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">Filter</button>
                        <a href="riwayatkhl_penanggungjawab_sdm.php" class="btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nama</th><th>Proyek</th><th>Tgl Kerja</th><th>Jam Kerja</th><th>Tgl Libur</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($riwayat_khl_filter)): ?>
                        <?php foreach($riwayat_khl_filter as $khl): ?>
                        <tr>
                            <td><?= htmlspecialchars($khl['id']) ?></td>
                            <td><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($khl['projek']) ?></td>
                            <td><?= date('d M Y', strtotime($khl['tanggal_kerja'])) ?></td>
                            <td><?= date('H:i', strtotime($khl['jam_mulai_kerja'])) ?> - <?= date('H:i', strtotime($khl['jam_selesai_kerja'])) ?></td>
                            <td><?= date('d M Y', strtotime($khl['tanggal_libur'])) ?></td>
                            <td><span class="status-<?= strtolower(str_replace(' ', '-', $khl['status'])) ?>"><?= htmlspecialchars($khl['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="no-data">Tidak ada data riwayat KHL yang cocok dengan filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>