<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];
$kode_direktur = $user['kode_karyawan']; 

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status_filter = $_GET['status'] ?? '';

$query = "SELECT dk.nama_lengkap, dpk.* 
          FROM data_pengajuan_khl dpk
          JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan
          WHERE dk.kode_karyawan = ?";

$params = [$kode_direktur];
$types = 's';

if (!empty($start_date)) {
    $query .= " AND dpk.tanggal_khl >= ?";
    $params[] = $start_date;
    $types .= 's';
}
if (!empty($end_date)) {
    $query .= " AND dpk.tanggal_khl <= ?";
    $params[] = $end_date;
    $types .= 's';
}
if (!empty($status_filter)) {
    $query .= " AND dpk.status_khl = ?";
    $params[] = strtolower($status_filter);
    $types .= 's';
}

$query .= " ORDER BY dpk.created_at DESC";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_khl = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $data_khl = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat KHL Pribadi - Direktur</title>
<style>
    body {
        margin:0;
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
        min-height:100vh;
        color:#333;
    }
    header {
        background:#fff;
        padding:20px 40px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:2px solid #34377c;
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
        width:50px; height:50px; object-fit:contain; border-radius:50%;
    }
    nav ul {
        list-style:none; margin:0; padding:0; display:flex; gap:30px;
    }
    nav a {
        text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block;
    }
    nav li { position:relative; }
    nav li ul {
        display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0;
        border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999;
    }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color:#fff; }
    h1 { font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size:16px; margin-top:0; margin-bottom:30px; opacity:0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size:24px; font-weight:600; text-align:center; margin-bottom:30px; color:#1E105E; }

    .filter-section { background:#f8f9fa; padding:20px; border-radius:10px; margin-bottom:25px; }
    .filter-row { display:flex; gap:15px; align-items:end; flex-wrap:wrap; }
    .filter-group { display:flex; flex-direction:column; gap:5px; }
    .filter-group input, .filter-group select {
        padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px;
    }
    .filter-group label { font-weight:600; font-size:14px; color:#333; }
    .filter-group.date-group { min-width:150px; }
    .filter-group.status-group { min-width:180px; }

    .action-bar { display:flex; gap:10px; margin-top:15px; }
    .btn { padding:10px 20px; border:none; border-radius:6px; font-size:14px; font-weight:600; color:#fff; cursor:pointer; text-decoration:none; }
    .btn-cari { background-color:#4a3f81; }
    .btn-cari:hover { background-color:#3a3162; }
    .btn-reset { background-color:#6c757d; }
    .btn-reset:hover { background-color:#545b62; }

    .data-table { width:100%; border-collapse:collapse; font-size:14px; margin-top:20px; }
    .data-table th, .data-table td { padding:12px 15px; border-bottom:1px solid #ddd; }
    .data-table th { background-color:#f8f9fa; font-weight:600; }
    .data-table tbody tr:hover { background-color:#f1f1f1; }

    .status-diterima { color:#28a745; font-weight:600; }
    .status-ditolak { color:#d9534f; font-weight:600; }
    .status-menunggu { color:#ffc107; font-weight:600; }

    .no-data { text-align:center; padding:40px; color:#666; font-style:italic; }
</style>
</head>
<body>

<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboarddirektur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Semua Cuti</a></li>
                    <li><a href="riwayat_cuti_pribadi_direktur.php">Riwayat Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_direktur.php">Kalender Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat Semua KHL</a></li>
                    <li><a href="riwayat_khl_pribadi_direktur.php">Riwayat KHL Pribadi</a></li>
                    <li><a href="kalender_khl_direktur.php">Kalender KHL</a></li>
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
    <h1>Welcome, <?= htmlspecialchars($nama_direktur) ?>!</h1>
    <p class="admin-title">Riwayat KHL Pribadi</p>

    <div class="card">
        <h2 class="page-title">Riwayat KHL Saya</h2>

        <div class="filter-section">
            <form method="GET" action="riwayat_khl_pribadi_direktur.php">
                <div class="filter-row">
                    <div class="filter-group date-group">
                        <label for="start_date">Dari Tanggal Kerja</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    </div>

                    <div class="filter-group date-group">
                        <label for="end_date">Sampai Tanggal Kerja</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    </div>

                    <div class="filter-group status-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Semua</option>
                            <option value="disetujui" <?= $status_filter=='disetujui'?'selected':'' ?>>Diterima</option>
                            <option value="ditolak" <?= $status_filter=='ditolak'?'selected':'' ?>>Ditolak</option>
                            <option value="pending" <?= $status_filter=='pending'?'selected':'' ?>>Menunggu</option>
                        </select>
                    </div>
                </div>

                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="riwayat_khl_pribadi_direktur.php" class="btn btn-reset">Reset</a>
                </div>
            </form>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Proyek</th>
                    <th>Tanggal Kerja</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data_khl)): ?>
                    <?php $no=1; foreach($data_khl as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['proyek']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_khl'])) ?></td>
                        <td><?= $row['jam_mulai_kerja'] ? date('H:i', strtotime($row['jam_mulai_kerja'])) : '-' ?></td>
                        <td><?= $row['jam_akhir_kerja'] ? date('H:i', strtotime($row['jam_akhir_kerja'])) : '-' ?></td>
                        <td>
                            <?php
                            $status = strtolower($row['status_khl']);
                            if ($status=='disetujui') echo "<span class='status-diterima'>Diterima</span>";
                            elseif ($status=='ditolak') echo "<span class='status-ditolak'>Ditolak</span>";
                            else echo "<span class='status-menunggu'>Menunggu</span>";
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="no-data">Tidak ada data KHL ditemukan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
