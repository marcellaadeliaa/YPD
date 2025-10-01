<?php
// === BAGIAN LOGIKA BACKEND (PHP) ===

// 1. Memulai session dan menyertakan config (Asumsi Anda memiliki file config.php)
session_start();
// require 'config.php'; 

// 2. Ambil data Direktur yang sedang login (Menggunakan data dummy/session placeholder)
$nama_direktur = $_SESSION['nama_lengkap'] ?? 'Budi Direktur'; 
$jabatan_direktur = $_SESSION['jabatan'] ?? 'Direktur Utama'; 

// 3. LOGIKA UNTUK MENGAMBIL DATA PENGAJUAN CUTI (Data Dummy)
$data_pengajuan = [
    [
        'id' => 101,
        'nama_lengkap' => 'Budi Santoso',
        'divisi' => 'Marketing',
        'jenis_cuti' => 'Cuti Tahunan',
        'tanggal_mulai' => '2025-10-10',
        'tanggal_akhir' => '2025-10-14',
        'jumlah_hari' => 5,
        'status_sdm' => 'Disetujui SDM',
    ],
    [
        'id' => 102,
        'nama_lengkap' => 'Citra Dewi',
        'divisi' => 'Keuangan',
        'jenis_cuti' => 'Cuti Sakit',
        'tanggal_mulai' => '2025-10-05',
        'tanggal_akhir' => '2025-10-07',
        'jumlah_hari' => 3,
        'status_sdm' => 'Disetujui SDM',
    ],
];

// 4. LOGIKA UNTUK MENANGANI AKSI PERSETUJUAN/PENOLAKAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $cuti_id = $_POST['cuti_id'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    
    // Tempatkan kode untuk update status di database di sini
    
    // Redirect untuk mencegah resubmission
    header("Location: persetujuan_cuti_direktur.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Cuti Direktur</title>
    <style>
        /* CSS yang sama seperti sebelumnya untuk konsistensi */
        :root {
            --primary-color: #1E105E;
            --secondary-color: #8897AE;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-light: #fff;
            --text-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
        }
        body {
            margin:0;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height:100vh;
            color: var(--text-light);
        }
        
        /* === HEADER BARU (CSS Disederhanakan) === */
        header {
            background: var(--card-bg);
            padding: 15px 40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow: 0 2px 10px var(--shadow-light);
        }
        .logo {
            display:flex;
            align-items:center;
            gap:15px;
            font-size:24px;
            font-family: serif;
            color: var(--text-dark);
            font-weight: 600;
        }
        .logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
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
            color: var(--text-dark);
            font-weight:600;
            padding:10px 0;
            display:block;
        }
        nav li ul {
            display:none;
            position:absolute;
            top:100%;
            left:0;
            background: var(--card-bg);
            padding:10px 0;
            border-radius:8px;
            box-shadow:0 4px 12px var(--shadow-light);
            min-width:180px;
            z-index:1000;
        }
        nav li:hover > ul { display:block; }
        nav li ul li a {
            padding: 8px 15px;
            font-weight:400;
            white-space:nowrap;
        }
        nav li ul li a:hover {
            background-color: #f0f0f0;
        }
        /* === AKHIR HEADER BARU === */

        main {
            max-width:1200px;
            margin:40px auto;
            padding:0 20px;
        }
        h1 {
            text-align:left;
            font-size:2.2rem;
            margin-bottom:10px;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .main-header .welcome-text {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .btn-pengajuan {
            background: var(--accent-color);
            color: var(--text-light);
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-pengajuan:hover {
            background-color: #352d5c;
        }
        .card {
            background: var(--card-bg);
            color: var(--text-dark);
            border-radius:20px;
            padding:30px 40px;
            box-shadow:0 4px 20px var(--shadow-light);
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
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        
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
        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        .btn-detail { background-color: #007bff; }
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
        <div class="main-header">
            <div>
                <h1>Daftar Persetujuan Cuti</h1>
                <p class="welcome-text">Selamat datang, <?= htmlspecialchars($nama_direktur) ?>! Tinjau pengajuan cuti di bawah ini.</p>
            </div>
            <a href="pengajuan_cuti_direktur.php" class="btn-pengajuan">Ajukan Cuti Pribadi</a>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Divisi</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Jumlah Hari</th>
                        <th>Status SDM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data_pengajuan)): ?>
                        <?php foreach($data_pengajuan as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['divisi']) ?></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?></td>
                                <td><?= htmlspecialchars($row['jumlah_hari']) ?> Hari</td>
                                <td><?= htmlspecialchars($row['status_sdm']) ?></td>
                                <td>
                                    <form method="POST" action="persetujuan_cuti_direktur.php" style="display:inline;">
                                        <input type="hidden" name="cuti_id" value="<?= htmlspecialchars($row['id']) ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-approve" onclick="return confirm('Yakin ingin MENYETUJUI cuti ini?');">Setujui</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-reject" onclick="return confirm('Yakin ingin MENOLAK cuti ini?');">Tolak</button>
                                    </form>
                                    <a href="detail_cuti.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-detail">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tidak ada pengajuan cuti yang menunggu persetujuan Direktur saat ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>