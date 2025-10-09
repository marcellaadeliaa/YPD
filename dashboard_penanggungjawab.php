<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_pj = $user['nama_lengkap'];
$divisi_pj = $user['divisi'];
$jabatan = "Penanggung Jawab Divisi " . $divisi_pj;

$stmt_cuti = $conn->prepare("SELECT COUNT(id) as total 
                            FROM data_pengajuan_cuti 
                            WHERE divisi = ? 
                            AND status = ? 
                            AND role = 'karyawan'");
$status_cuti = 'Menunggu Persetujuan';
$stmt_cuti->bind_param("ss", $divisi_pj, $status_cuti);
$stmt_cuti->execute();
$cuti_menunggu = $stmt_cuti->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_cuti->close();

$stmt_khl = $conn->prepare("SELECT COUNT(id_khl) as total 
                           FROM data_pengajuan_khl 
                           WHERE divisi = ? 
                           AND status_khl = ? 
                           AND role = 'karyawan'");
$status_khl = 'pending';
$stmt_khl->bind_param("ss", $divisi_pj, $status_khl);
$stmt_khl->execute();
$khl_menunggu = $stmt_khl->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_khl->close();

$stmt_karyawan = $conn->prepare("SELECT COUNT(id_karyawan) AS total 
                                FROM data_karyawan 
                                WHERE divisi = ? 
                                AND status_aktif = 'aktif' 
                                AND role IN ('karyawan', 'penanggung jawab')");
$stmt_karyawan->bind_param("s", $divisi_pj);
$stmt_karyawan->execute();
$total_karyawan_divisi = $stmt_karyawan->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_karyawan->close();

$stmt_recent_cuti = $conn->prepare("SELECT nama_karyawan, jenis_cuti, tanggal_mulai, tanggal_akhir, created_at 
                                    FROM data_pengajuan_cuti 
                                    WHERE divisi = ? 
                                    AND status = 'Menunggu Persetujuan' 
                                    AND role = 'karyawan'
                                    ORDER BY created_at DESC 
                                    LIMIT 5");
$stmt_recent_cuti->bind_param("s", $divisi_pj);
$stmt_recent_cuti->execute();
$recent_cuti = $stmt_recent_cuti->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_recent_cuti->close();

$stmt_recent_khl = $conn->prepare("SELECT kode_karyawan, proyek, tanggal_khl, tanggal_cuti_khl, created_at 
                                   FROM data_pengajuan_khl 
                                   WHERE divisi = ? 
                                   AND status_khl = 'pending' 
                                   AND role = 'karyawan'
                                   ORDER BY created_at DESC 
                                   LIMIT 5");
$stmt_recent_khl->bind_param("s", $divisi_pj);
$stmt_recent_khl->execute();
$recent_khl = $stmt_recent_khl->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_recent_khl->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penanggung Jawab</title>
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
            font-family: 'Segoe UI', sans-serif; 
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
        
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; padding: 5px 20px; }

        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .heading-section h1 { font-size: 2.5rem; margin: 0; color: #fff; }
        .heading-section p { font-size: 1.1rem; margin-top: 5px; opacity: 0.9; margin-bottom: 30px; color: #fff; }

        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 30px; }
        .card { background: var(--card-bg); color: var(--text-color-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 5px 20px var(--shadow-light); display: flex; flex-direction: column; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
        .card h3 { margin-top: 0; font-size: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: var(--primary-color); }
        .pending-count { font-size: 3.5rem; font-weight: 700; color: var(--primary-color); text-align: center; margin: 20px 0; }
        .btn { display: inline-block; background: var(--accent-color); color: var(--text-color-light); padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: auto; text-align: center; transition: background-color 0.3s ease; }
        .btn:hover { background: var(--primary-color); }
        .calendar-icon { font-size: 3rem; text-align: center; margin: 20px 0; color: var(--primary-color); }
        .info-text { font-size: 0.85rem; color: #666; text-align: center; margin-top: 10px; font-style: italic; }
        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboard_penanggungjawab.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Divisi</a></li>
                    <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Divisi</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
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
            <p class="info-text">Hanya dari karyawan divisi <?= htmlspecialchars($divisi_pj) ?></p>
            <a href="persetujuancuti_penanggungjawab.php" class="btn">Lihat Rincian</a>
        </div>
        <div class="card">
            <h3>KHL Menunggu Persetujuan</h3>
            <p class="pending-count"><?= $khl_menunggu ?></p>
            <p class="info-text">Hanya dari karyawan divisi <?= htmlspecialchars($divisi_pj) ?></p>
            <a href="persetujuankhl_penanggungjawab.php" class="btn">Lihat Rincian</a>
        </div>
        <div class="card">
            <h3>Total Karyawan Divisi</h3>
            <p class="pending-count"><?= $total_karyawan_divisi ?></p>
            <p class="info-text">Karyawan aktif di divisi <?= htmlspecialchars($divisi_pj) ?></p>
            <a href="karyawan_divisi.php" class="btn">Lihat Data</a>
        </div>
        <div class="card">
            <h3>Kalender Cuti Divisi</h3>
            <div class="calendar-icon">📅</div>
            <p class="info-text">Lihat jadwal cuti yang sudah diterima</p>
            <a href="kalender_cuti_penanggungjawab.php" class="btn">Lihat Kalender Cuti</a>
        </div>
        <div class="card">
            <h3>Kalender KHL Divisi</h3>
            <div class="calendar-icon">📅</div>
            <p class="info-text">Lihat jadwal KHL yang sudah diterima</p>
            <a href="kalender_khl_penanggungjawab.php" class="btn">Lihat Kalender KHL</a>
        </div>
    </div>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div class="card" style="flex: 1; min-width: 48%;">
            <h3>5 Pengajuan Cuti Terbaru (Menunggu Persetujuan)</h3>
            <?php if (count($recent_cuti) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Karyawan</th>
                            <th>Jenis Cuti</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Akhir</th>
                            <th>Diajukan Pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cuti as $cuti): ?>
                        <tr>
                            <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                            <td><?= htmlspecialchars($cuti['tanggal_mulai']) ?></td>
                            <td><?= htmlspecialchars($cuti['tanggal_akhir']) ?></td>
                            <td><?= htmlspecialchars($cuti['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color:#555;">Tidak ada pengajuan cuti yang menunggu persetujuan.</p>
            <?php endif; ?>
        </div>

        <!-- KHL -->
        <div class="card" style="flex: 1; min-width: 48%;">
            <h3>5 Pengajuan KHL Terbaru (Pending)</h3>
            <?php if (count($recent_khl) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Kode Karyawan</th>
                            <th>Proyek</th>
                            <th>Tanggal KHL</th>
                            <th>Tanggal Cuti KHL</th>
                            <th>Diajukan Pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_khl as $khl): ?>
                        <tr>
                            <td><?= htmlspecialchars($khl['kode_karyawan']) ?></td>
                            <td><?= htmlspecialchars($khl['proyek']) ?></td>
                            <td><?= htmlspecialchars($khl['tanggal_khl']) ?></td>
                            <td><?= htmlspecialchars($khl['tanggal_cuti_khl']) ?></td>
                            <td><?= htmlspecialchars($khl['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color:#555;">Tidak ada pengajuan KHL yang pending.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
