<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

$karyawan_id = $_GET['id'] ?? '';

$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $karyawan_id);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();

if (!$karyawan) {
    $_SESSION['error_message'] = "Data karyawan tidak ditemukan!";
    header("Location: data_karyawan.php");
    exit;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
        header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
        .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
        .logo img { width: 140px; height: 50px; object-fit: contain; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
        nav li { position:relative; }
        nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
        nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
        main { max-width:1000px; margin:40px auto; padding:0 20px; }
        
        .welcome-section {
            margin-bottom: 20px;
        }
        
        .welcome-section h1 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .welcome-section h2 {
            color: #1E105E;
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .card { 
            background:#fff; 
            border-radius:20px; 
            padding:30px 40px; 
            box-shadow:0 2px 10px rgba(0,0,0,0.15); 
        }
        
        .employee-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
                
        
        .employee-info h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #1E105E;
        }
        
        .employee-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #1E105E;
        }
        
        .detail-card h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #1E105E;
            font-size: 18px;
        }
        
        .detail-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        
        .detail-value {
            color: #333;
        }
        
        .status-aktif {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-non-aktif {
            color: #dc3545;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 16px;
        }
        
        .btn-edit {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-back {
            background-color: #3498db;
            color: white;
        }
        
        .btn-print {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .employee-header {
                flex-direction: column;
                text-align: center;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="image/namayayasan.png" alt="Logo">
            <span>Yayasan Purba Danarta</span>
        </div>
        <nav>
            <ul>
                <li><a href="dashboardadmin.php">Beranda</a></li>
                <li><a href="#">Cuti ▾</a>
                    <ul>
                        <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
                        <li><a href="kalender_cuti.php">Kalender Cuti</a></li>
                        <li><a href="daftar_sisa_cuti.php">Sisa Cuti Karyawan</a></li>
                    </ul>
                </li>
                <li><a href="#">KHL ▾</a>
                    <ul>
                        <li><a href="riwayat_khl.php">Riwayat KHL</a></li>
                        <li><a href="kalender_khl.php">Kalender KHL</a></li>
                    </ul>
                </li>
                <li><a href="#">Lamaran Kerja ▾</a>
                    <ul>
                        <li><a href="administrasi_pelamar.php">Administrasi Pelamar</a></li>
                        <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
                    </ul>
                </li>
                <li><a href="#">Karyawan ▾</a>
                    <ul>
                        <li><a href="data_karyawan.php">Data Karyawan</a></li>
                    </ul>
                </li>
                <li><a href="logout2.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="welcome-section">
            <h1>Detail Karyawan</h1>
        </div>
        
        <div class="card">
            <div class="employee-header">
                <div class="employee-info">
                    <h3><?php echo htmlspecialchars($karyawan['nama_lengkap']); ?></h3>
                    <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($karyawan['kode_karyawan']); ?></p>
                    <p><strong>Divisi:</strong> <?php echo htmlspecialchars($karyawan['divisi']); ?></p>
                    <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($karyawan['jabatan']); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($karyawan['role']); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="<?php echo $karyawan['status_aktif'] == 'aktif' ? 'status-aktif' : 'status-non-aktif'; ?>">
                            <?php echo ucfirst($karyawan['status_aktif']); ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="detail-grid">
                <div class="detail-card">
                    <h4>Informasi Akun</h4>
                    <div class="detail-item">
                        <span class="detail-label">Kode Karyawan</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['kode_karyawan']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nama Lengkap</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['nama_lengkap']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Password</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['password']); ?></span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h4>Informasi Pekerjaan</h4>
                    <div class="detail-item">
                        <span class="detail-label">Jabatan</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['jabatan']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Divisi</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['divisi']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Role</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['role']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="detail-value <?php echo $karyawan['status_aktif'] == 'aktif' ? 'status-aktif' : 'status-non-aktif'; ?>">
                            <?php echo ucfirst($karyawan['status_aktif']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h4>Informasi Kontak</h4>
                    <div class="detail-item">
                        <span class="detail-label">No. Telepon</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['no_telp'] ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($karyawan['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal Dibuat</span>
                        <span class="detail-value"><?php echo date('d-m-Y', strtotime($karyawan['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="edit_karyawan.php?id=<?php echo $karyawan['id_karyawan']; ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Edit Data
                </a>
                <a href="data_karyawan.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </main>
</body>
</html>