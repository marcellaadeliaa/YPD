<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit();
}

// Ambil data dari session
$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];

// Query riwayat KHL dengan data lengkap
$query = "SELECT * FROM data_pengajuan_khl WHERE kode_karyawan = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $nik);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat KHL</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
            min-height: 100vh;
        }
        
        /* Header Styles */
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
            width:120px;
            height:50px;
            object-fit:contain;
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
            color:#333;
            font-weight:600;
        }
        nav li ul {
            display:none;
            position:absolute;
            background:#fff;
            padding:10px 0;
            border-radius:8px;
            box-shadow:0 2px 8px rgba(0,0,0,.15);
            min-width:150px;
        }
        nav li:hover ul {display:block;}
        nav li ul li {padding:5px 20px;}
        nav li ul li a {color:#333;font-weight:400;}

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h2 {
            color: #2e1f4f;
            margin-bottom: 10px;
        }
        
        .user-info {
            color: #666;
            font-size: 16px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #4a3f81;
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .status-pending { 
            color: #e67e22; 
            font-weight: bold; 
            background: #fef9e7;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .status-disetujui { 
            color: #27ae60; 
            font-weight: bold; 
            background: #eafaf1;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .status-ditolak { 
            color: #e74c3c; 
            font-weight: bold; 
            background: #fdedec;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .back-button {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            background-color: #4a3f81;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #3a3162;
            text-decoration: none;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px;
            }
            
            header {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
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
                <li><a href="dashboardkaryawan.php">Beranda</a></li>
                <li><a href="#">Cuti ▾</a>
                    <ul>
                        <li><a href="formcutikaryawan.php">Pengajuan Cuti</a></li>
                        <li><a href="riwayat_cuti_pribadi.php">Riwayat Cuti</a></li>
                    </ul>
                </li>
                <li><a href="#">KHL ▾</a>
                    <ul>
                        <li><a href="formkhlkaryawan.php">Pengajuan KHL</a></li>
                        <li><a href="riwayat_khl_pribadi.php">Riwayat KHL</a></li>
                    </ul>
                </li>
                <li><a href="#">Profil ▾</a>
                    <ul>
                        <li><a href="data_pribadi.php">Data Pribadi</a></li>
                        <li><a href="logout2.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h2>Riwayat Pengajuan KHL</h2>
            <div class="user-info">
                <strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($nik); ?> | 
                <strong>Nama:</strong> <?php echo htmlspecialchars($nama_lengkap); ?>
            </div>
        </div>
        
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Proyek</th>
                            <th>Divisi</th>
                            <th>Jabatan</th>
                            <th>Role</th>
                            <th>Tanggal KHL</th>
                            <th>Jam Kerja</th>
                            <th>Tanggal Cuti</th>
                            <th>Jam Cuti</th>
                            <th>Status</th>
                            <th>Tanggal Pengajuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['proyek']); ?></td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['role']))); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_khl'])); ?></td>
                            <td><?php echo $row['jam_mulai_kerja'] . ' - ' . $row['jam_akhir_kerja']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_cuti_khl'])); ?></td>
                            <td><?php echo $row['jam_mulai_cuti_khl'] . ' - ' . $row['jam_akhir_cuti_khl']; ?></td>
                            <td>
                                <?php 
                                $status_class = 'status-' . $row['status_khl'];
                                $status_text = ucfirst($row['status_khl']);
                                echo '<span class="' . $status_class . '">' . $status_text . '</span>';
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div>📋</div>
                <h3>Belum ada riwayat pengajuan KHL</h3>
                <p>Anda belum pernah mengajukan Kerja Hari Libur.</p>
                <a href="formkhlkaryawan.php" class="back-button">Ajukan KHL Pertama</a>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="formkhlkaryawan.php" class="back-button">Kembali ke Form Pengajuan KHL</a>
            <a href="dashboardkaryawan.php" class="back-button">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
// Tutup statement dan koneksi
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>