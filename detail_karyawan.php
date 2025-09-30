<?php
session_start();

// Data karyawan berdasarkan ID
$karyawan_id = $_GET['id'] ?? '';

// Cari data karyawan dari session
$karyawan_detail = null;
foreach ($_SESSION['karyawan'] as $karyawan) {
    if ($karyawan['kode'] == $karyawan_id) {
        $karyawan_detail = $karyawan;
        break;
    }
}

// Jika tidak ditemukan di data dasar, cari di data lengkap
if (!$karyawan_detail && isset($_SESSION['karyawan_data'][$karyawan_id])) {
    $karyawan_detail = $_SESSION['karyawan_data'][$karyawan_id];
    $karyawan_detail['kode'] = $karyawan_id;
}

// Default data jika ID tidak ditemukan
$karyawan = $karyawan_detail ?: array(
    'nama' => '-',
    'divisi' => '-',
    'role' => '-',
    'telepon' => '-',
    'email' => '-',
    'alamat' => '-',
    'tanggal_masuk' => '-',
    'status' => '-',
    'tanggal_lahir' => '-',
    'jenis_kelamin' => '-'
);
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
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
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
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .employee-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1E105E;
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
                        <li><a href="administrasi_cuti.php">Administrasi Cuti</a></li>
                        <li><a href="riwayat_cuti.php">Riwayat Cuti Pegawai</a></li>
                    </ul>
                </li>
                <li><a href="#">KHL ▾</a>
                    <ul>
                        <li><a href="administrasi_khl.php">Administrasi KHL</a></li>
                        <li><a href="riwayat_khl.php">Riwayat KHL Pegawai</a></li>
                    </ul>
                </li>
                <li><a href="#">Lamaran Kerja ▾</a>
                    <ul>
                        <li><a href="administrasi_pelamar.php">Administrasi Pelamar</a></li>
                        <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
                    </ul>
                </li>
                <li><a href="#">Karyawan ▾</a></li>
                <li><a href="#">Profil ▾</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="welcome-section">
            <h1>Welcome, Cell!</h1>
            <h2>Detail Karyawan</h2>
        </div>
        
        <div class="card">
            <?php
            // Data karyawan berdasarkan ID
            $karyawan_id = $_GET['id'] ?? '';
            
            // Data dummy karyawan lengkap
            $karyawan_data = array(
                '11223386' => array(
                    'nama' => 'Adhitama',
                    'divisi' => 'Training',
                    'role' => 'Penanggung Jawab',
                    'telepon' => '84589625258',
                    'email' => 'adhitama@gmail.com',
                    'alamat' => 'Jl. Merdeka No. 123, Jakarta',
                    'tanggal_masuk' => '2020-03-15',
                    'status' => 'Aktif',
                    'tanggal_lahir' => '1990-05-20',
                    'jenis_kelamin' => 'Laki-laki'
                ),
                '11223344' => array(
                    'nama' => 'Xue',
                    'divisi' => 'Wisma',
                    'role' => 'Staff',
                    'telepon' => '82123456789',
                    'email' => 'xue@company.com',
                    'alamat' => 'Jl. Sudirman No. 45, Jakarta',
                    'tanggal_masuk' => '2021-01-10',
                    'status' => 'Aktif',
                    'tanggal_lahir' => '1992-08-15',
                    'jenis_kelamin' => 'Perempuan'
                ),
                '11223355' => array(
                    'nama' => 'Adel',
                    'divisi' => 'Training',
                    'role' => 'Staff & Admin',
                    'telepon' => '82234567890',
                    'email' => 'adel@company.com',
                    'alamat' => 'Jl. Gatot Subroto No. 78, Jakarta',
                    'tanggal_masuk' => '2021-06-20',
                    'status' => 'Aktif',
                    'tanggal_lahir' => '1993-03-10',
                    'jenis_kelamin' => 'Perempuan'
                ),
                '11223366' => array(
                    'nama' => 'Budi Santoso',
                    'divisi' => 'Wisma',
                    'role' => 'Staff',
                    'telepon' => '82345678901',
                    'email' => 'budi.santoso@company.com',
                    'alamat' => 'Jl. Thamrin No. 56, Jakarta',
                    'tanggal_masuk' => '2019-11-05',
                    'status' => 'Aktif',
                    'tanggal_lahir' => '1988-12-25',
                    'jenis_kelamin' => 'Laki-laki'
                ),
                '11223377' => array(
                    'nama' => 'Siti Rahayu',
                    'divisi' => 'Konsultasi',
                    'role' => 'Penanggung Jawab',
                    'telepon' => '82456789012',
                    'email' => 'siti.rahayu@company.com',
                    'alamat' => 'Jl. Kuningan No. 34, Jakarta',
                    'tanggal_masuk' => '2018-08-12',
                    'status' => 'Aktif',
                    'tanggal_lahir' => '1985-07-18',
                    'jenis_kelamin' => 'Perempuan'
                )
            );
            
            // Default data jika ID tidak ditemukan
            $karyawan = $karyawan_data[$karyawan_id] ?? array(
                'nama' => 'Tidak Ditemukan',
                'divisi' => '-',
                'role' => '-',
                'telepon' => '-',
                'email' => '-',
                'alamat' => '-',
                'tanggal_masuk' => '-',
                'status' => '-',
                'tanggal_lahir' => '-',
                'jenis_kelamin' => '-'
            );
            ?>
            
            <div class="employee-header">
                <img src="https://via.placeholder.com/150" alt="Foto Karyawan" class="employee-photo">
                <div class="employee-info">
                    <h3><?php echo $karyawan['nama']; ?></h3>
                    <p><strong>ID Karyawan:</strong> <?php echo $karyawan_id; ?></p>
                    <p><strong>Divisi:</strong> <?php echo $karyawan['divisi']; ?></p>
                    <p><strong>Posisi:</strong> <?php echo $karyawan['role']; ?></p>
                    <p><strong>Status:</strong> <span style="color: #28a745;"><?php echo $karyawan['status']; ?></span></p>
                </div>
            </div>
            
            <div class="detail-grid">
                <div class="detail-card">
                    <h4>Informasi Pribadi</h4>
                    <div class="detail-item">
                        <span class="detail-label">Nama Lengkap</span>
                        <span class="detail-value"><?php echo $karyawan['nama']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Jenis Kelamin</span>
                        <span class="detail-value"><?php echo $karyawan['jenis_kelamin']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal Lahir</span>
                        <span class="detail-value"><?php echo $karyawan['tanggal_lahir']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Alamat</span>
                        <span class="detail-value"><?php echo $karyawan['alamat']; ?></span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h4>Informasi Kontak</h4>
                    <div class="detail-item">
                        <span class="detail-label">No. Telepon</span>
                        <span class="detail-value"><?php echo $karyawan['telepon']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo $karyawan['email']; ?></span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h4>Informasi Pekerjaan</h4>
                    <div class="detail-item">
                        <span class="detail-label">Divisi</span>
                        <span class="detail-value"><?php echo $karyawan['divisi']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Posisi</span>
                        <span class="detail-value"><?php echo $karyawan['role']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal Masuk</span>
                        <span class="detail-value"><?php echo $karyawan['tanggal_masuk']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><?php echo $karyawan['status']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="edit_karyawan.php?id=<?php echo $karyawan_id; ?>" class="btn btn-edit">
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