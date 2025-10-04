<?php
session_start();
require_once 'config.php';

// Data karyawan berdasarkan ID
$karyawan_id = $_GET['id'] ?? '';

// Ambil data dari database
$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $karyawan_id);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();

// Jika tidak ditemukan, redirect
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
    <title>Edit Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS sama seperti sebelumnya */
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
        
        .form-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group.required label::after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1E105E;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #1E105E;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
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
        
        .btn-save {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-cancel {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-back {
            background-color: #3498db;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .form-header {
                flex-direction: column;
                text-align: center;
            }
            
            .form-grid {
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
            <h1>Welcome, Cell!</h1>
            <h2>Edit Data Karyawan</h2>
        </div>
        
        <div class="card">
            <form id="editForm" method="POST" action="update_karyawan.php">
                <input type="hidden" name="id_karyawan" value="<?php echo $karyawan['id_karyawan']; ?>">
                
                <div class="form-header">
                    <div>
                        <h3>Edit Data: <?php echo htmlspecialchars($karyawan['nama_lengkap']); ?></h3>
                        <p><strong>Kode Karyawan Saat Ini:</strong> <?php echo htmlspecialchars($karyawan['kode_karyawan']); ?></p>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Akun</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="kode_karyawan">Kode Karyawan</label>
                            <input type="text" id="kode_karyawan" name="kode_karyawan" value="<?php echo htmlspecialchars($karyawan['kode_karyawan']); ?>" required>
                        </div>
                        <div class="form-group required">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($karyawan['nama_lengkap']); ?>" required>
                        </div>
                        <div class="form-group required">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($karyawan['email']); ?>" required>
                        </div>
                        <div class="form-group required">
                            <label for="password">Password</label>
                            <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($karyawan['password']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Pekerjaan</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" value="<?php echo htmlspecialchars($karyawan['jabatan']); ?>" required>
                        </div>
                        <div class="form-group required">
                            <label for="divisi">Divisi</label>
                            <select id="divisi" name="divisi" required>
                                <option value="">Pilih Divisi</option>
                                <option value="Training" <?php echo $karyawan['divisi'] == 'Training' ? 'selected' : ''; ?>>Training</option>
                                <option value="Wisma" <?php echo $karyawan['divisi'] == 'Wisma' ? 'selected' : ''; ?>>Wisma</option>
                                <option value="Konsultasi" <?php echo $karyawan['divisi'] == 'Konsultasi' ? 'selected' : ''; ?>>Konsultasi</option>
                                <option value="SDM" <?php echo $karyawan['divisi'] == 'SDM' ? 'selected' : ''; ?>>SDM</option>
                                <option value="Keuangan" <?php echo $karyawan['divisi'] == 'Keuangan' ? 'selected' : ''; ?>>Keuangan</option>
                                <option value="Sekretariat" <?php echo $karyawan['divisi'] == 'Sekretariat' ? 'selected' : ''; ?>>Sekretariat</option>
                                <option value="Direksi" <?php echo $karyawan['divisi'] == 'Direksi' ? 'selected' : ''; ?>>Direksi</option>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="karyawan" <?php echo $karyawan['role'] == 'karyawan' ? 'selected' : ''; ?>>Karyawan</option>
                                <option value="admin" <?php echo $karyawan['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="penanggung jawab" <?php echo $karyawan['role'] == 'penanggung jawab' ? 'selected' : ''; ?>>Penanggung Jawab</option>
                                <option value="direktur" <?php echo $karyawan['role'] == 'direktur' ? 'selected' : ''; ?>>Direktur</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="no_telp">No. Telepon</label>
                            <input type="tel" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($karyawan['no_telp']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Status</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="status_aktif">Status Aktif</label>
                            <select id="status_aktif" name="status_aktif">
                                <option value="aktif" <?php echo $karyawan['status_aktif'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="non_aktif" <?php echo $karyawan['status_aktif'] == 'non_aktif' ? 'selected' : ''; ?>>Non Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="detail_karyawan.php?id=<?php echo $karyawan['id_karyawan']; ?>" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <a href="data_karyawan.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali ke Data
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
    </script>
</body>
</html>