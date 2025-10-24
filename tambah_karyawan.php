<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

// Fungsi untuk mencari data pelamar berdasarkan nama
function cariDataPelamar($nama, $conn) {
    $sql = "SELECT dp.* FROM data_pelamar dp 
            LEFT JOIN data_karyawan dk ON dp.nama_lengkap = dk.nama_lengkap 
            WHERE dp.nama_lengkap LIKE ? 
            AND dp.status = 'Diterima' 
            AND dk.id_karyawan IS NULL";
    $stmt = $conn->prepare($sql);
    $nama = "%$nama%";
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Fungsi untuk mengecek apakah pelamar sudah menjadi karyawan
function cekPelamarSudahKaryawan($pelamar_id, $conn) {
    $sql = "SELECT dp.nama_lengkap, dk.id_karyawan 
            FROM data_pelamar dp 
            LEFT JOIN data_karyawan dk ON dp.nama_lengkap = dk.nama_lengkap 
            WHERE dp.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pelamar_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['id_karyawan'] !== null;
    }
    
    return false;
}

// Proses pencarian jika ada request AJAX
if (isset($_GET['search']) && isset($_GET['nama'])) {
    $dataPelamar = cariDataPelamar($_GET['nama'], $conn);
    echo json_encode($dataPelamar);
    exit();
}

// Proses jika ada data pelamar yang dipilih
$selectedPelamar = null;
$errorMessage = null;

if (isset($_GET['pelamar_id'])) {
    $pelamar_id = $_GET['pelamar_id'];
    
    // Cek apakah pelamar sudah menjadi karyawan
    if (cekPelamarSudahKaryawan($pelamar_id, $conn)) {
        $errorMessage = "Pelamar ini sudah terdaftar sebagai karyawan dan tidak dapat ditambahkan lagi.";
    } else {
        $sql = "SELECT * FROM data_pelamar WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pelamar_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $selectedPelamar = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            color: #ffffffff;
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
        
        .section-note {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .section-note p {
            margin: 0;
            color: #555;
            font-size: 14px;
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
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Styles untuk pencarian pelamar */
        .search-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-btn {
            padding: 10px 20px;
            background-color: #1E105E;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .search-results {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        
        .search-result-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .search-result-item:hover {
            background-color: #f0f0f0;
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .pelamar-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            border-left: 4px solid #2ecc71;
        }
        
        .pelamar-detail {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .detail-item {
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
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
            
            .pelamar-detail {
                grid-template-columns: 1fr;
            }
            .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
         .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .error-message i {
            margin-right: 8px;
        }
        
        .disabled-section {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .pelamar-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        
        .detail-item-full {
            grid-column: 1 / -1;
        }
        
        .field-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
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
            <h2>Tambah Karyawan Baru</h2>
        </div>
        
        <div class="card">
            <form id="tambahForm" method="POST" action="simpan_karyawan.php">
                <div class="form-header">
                    <div>
                        <h3>Data Karyawan Baru</h3>
                        <p>Buat akun untuk karyawan baru</p>
                    </div>
                </div>
                
                <?php if ($errorMessage): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $errorMessage; ?>
                </div>
                <?php endif; ?>
                
                <div class="section-note">
                    <p><strong>Informasi:</strong> Field bertanda * wajib diisi. Field lainnya dapat diisi nanti oleh karyawan.</p>
                </div>
                
                <!-- Bagian Pencarian Data Pelamar -->
                <div class="form-section">
                    <h3>Pencarian Data Pelamar</h3>
                    <div class="search-section">
                        <p>Cari data pelamar yang sudah diterima untuk mengisi informasi karyawan secara otomatis:</p>
                        <div class="search-container">
                            <input type="text" id="searchNama" class="search-input" placeholder="Masukkan nama pelamar...">
                            <button type="button" id="searchBtn" class="search-btn">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                        <div id="searchResults" class="search-results" style="display: none;"></div>
                        
                        <?php if ($selectedPelamar && !$errorMessage): ?>
                        <div class="pelamar-info">
                            <h4>Data Pelamar Terpilih:</h4>
                            <div class="pelamar-detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Nama:</span> <?php echo htmlspecialchars($selectedPelamar['nama_lengkap']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Jenis Kelamin:</span> <?php echo htmlspecialchars($selectedPelamar['jenis_kelamin']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tempat Lahir:</span> <?php echo htmlspecialchars($selectedPelamar['tempat_lahir']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tanggal Lahir:</span> <?php echo htmlspecialchars($selectedPelamar['tanggal_lahir']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">NIK:</span> <?php echo htmlspecialchars($selectedPelamar['nik']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Agama:</span> <?php echo htmlspecialchars($selectedPelamar['agama']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Pendidikan Terakhir:</span> <?php echo htmlspecialchars($selectedPelamar['pendidikan_terakhir']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email (Pelamar):</span> <?php echo htmlspecialchars($selectedPelamar['email']); ?>
                                </div>
                                <div class="detail-item-full">
                                    <span class="detail-label">Alamat Rumah:</span> <?php echo htmlspecialchars($selectedPelamar['alamat_rumah']); ?>
                                </div>
                                <div class="detail-item-full">
                                    <span class="detail-label">Alamat Domisili:</span> <?php echo htmlspecialchars($selectedPelamar['alamat_domisili']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Kontak Darurat:</span> <?php echo htmlspecialchars($selectedPelamar['kontak_darurat']); ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Posisi Dilamar:</span> <?php echo htmlspecialchars($selectedPelamar['posisi_dilamar']); ?>
                                </div>
                            </div>
                            <input type="hidden" name="pelamar_id" value="<?php echo $selectedPelamar['id']; ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-section <?php echo $errorMessage ? 'disabled-section' : ''; ?>">
                    <h3>Informasi Akun (Wajib)</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="kode_karyawan">Kode Karyawan</label>
                            <input type="text" id="kode_karyawan" name="kode_karyawan" placeholder="Contoh: YPD026 (Harus unik)" required 
                                value="">
                        </div>
                        <div class="form-group required">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required 
                                value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['nama_lengkap']) : ''; ?>"
                                <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group required">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Contoh: nama@ypd.com" required
                                value="<?php echo $selectedPelamar && !$errorMessage ? '' : ''; ?>">
                            <div class="field-note">
                                Email dapat diubah oleh admin, tidak harus sama dengan email pelamar
                            </div>
                        </div>
                        <div class="form-group required">
                            <label for="password">Password</label>
                            <div style="position: relative;">
                                <input type="text" id="password" name="password" placeholder="Masukkan password" required style="padding-right: 40px;"
                                    <?php echo $errorMessage ? 'readonly' : ''; ?>>
                                <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;"
                                        <?php echo $errorMessage ? 'disabled' : ''; ?>>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section <?php echo $errorMessage ? 'disabled-section' : ''; ?>">
                    <h3>Informasi Pekerjaan (Wajib)</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" placeholder="Contoh: Staf Training" required
                                   value="<?php echo $selectedPelamar && !$errorMessage ? 'Staf ' . htmlspecialchars($selectedPelamar['posisi_dilamar']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group required">
                            <label for="divisi">Divisi</label>
                            <select id="divisi" name="divisi" required <?php echo $errorMessage ? 'disabled' : ''; ?>>
                                <option value="">Pilih Divisi</option>
                                <option value="Training" <?php echo ($selectedPelamar && !$errorMessage && strpos($selectedPelamar['posisi_dilamar'], 'Training') !== false) ? 'selected' : ''; ?>>Training</option>
                                <option value="Wisma" <?php echo ($selectedPelamar && !$errorMessage && strpos($selectedPelamar['posisi_dilamar'], 'Wisma') !== false) ? 'selected' : ''; ?>>Wisma</option>
                                <option value="Konsultasi" <?php echo ($selectedPelamar && !$errorMessage && strpos($selectedPelamar['posisi_dilamar'], 'Konsultasi') !== false) ? 'selected' : ''; ?>>Konsultasi</option>
                                <option value="SDM" <?php echo ($selectedPelamar && !$errorMessage && strpos($selectedPelamar['posisi_dilamar'], 'SDM') !== false) ? 'selected' : ''; ?>>SDM</option>
                                <option value="Keuangan" <?php echo ($selectedPelamar && !$errorMessage && strpos($selectedPelamar['posisi_dilamar'], 'Keuangan') !== false) ? 'selected' : ''; ?>>Keuangan</option>
                                <option value="Sekretariat" <?php echo ($selectedPelamar && !$errorMessage && strpos($selectedPelamar['posisi_dilamar'], 'Sekretariat') !== false) ? 'selected' : ''; ?>>Sekretariat</option>
                                <option value="Direksi">Direksi</option>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="role">Role</label>
                            <select id="role" name="role" required <?php echo $errorMessage ? 'disabled' : ''; ?>>
                                <option value="">Pilih Role</option>
                                <option value="karyawan" selected>Karyawan</option>
                                <option value="admin">Admin</option>
                                <option value="penanggung jawab">Penanggung Jawab</option>
                                <option value="direktur">Direktur</option>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="no_telp">No. Telepon</label>
                            <input type="tel" id="no_telp" name="no_telp" placeholder="Contoh: 081234567890"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['no_telp']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>
                
                <div class="form-section <?php echo $errorMessage ? 'disabled-section' : ''; ?>">
                    <h3>Informasi Personal (Opsional)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <input type="text" id="jenis_kelamin" name="jenis_kelamin" placeholder="Jenis Kelamin"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['jenis_kelamin']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="tempat_lahir">Tempat Lahir</label>
                            <input type="text" id="tempat_lahir" name="tempat_lahir" placeholder="Tempat Lahir"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['tempat_lahir']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['tanggal_lahir']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="nik">NIK</label>
                            <input type="text" id="nik" name="nik" placeholder="NIK"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['nik']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group full-width">
                            <label for="alamat_rumah">Alamat Rumah</label>
                            <textarea id="alamat_rumah" name="alamat_rumah" placeholder="Alamat Rumah" rows="3"
                                      <?php echo $errorMessage ? 'readonly' : ''; ?>><?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['alamat_rumah']) : ''; ?></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="alamat_domisili">Alamat Domisili</label>
                            <textarea id="alamat_domisili" name="alamat_domisili" placeholder="Alamat Domisili" rows="3"
                                      <?php echo $errorMessage ? 'readonly' : ''; ?>><?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['alamat_domisili']) : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="agama">Agama</label>
                            <input type="text" id="agama" name="agama" placeholder="Agama"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['agama']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="kontak_darurat">Kontak Darurat</label>
                            <input type="text" id="kontak_darurat" name="kontak_darurat" placeholder="Kontak Darurat"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['kontak_darurat']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
                            <input type="text" id="pendidikan_terakhir" name="pendidikan_terakhir" placeholder="Pendidikan Terakhir"
                                   value="<?php echo $selectedPelamar && !$errorMessage ? htmlspecialchars($selectedPelamar['pendidikan_terakhir']) : ''; ?>"
                                   <?php echo $errorMessage ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>
                
                <div class="form-section <?php echo $errorMessage ? 'disabled-section' : ''; ?>">
                    <h3>Informasi Tambahan (Opsional)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="status_aktif">Status Aktif</label>
                            <select id="status_aktif" name="status_aktif" <?php echo $errorMessage ? 'disabled' : ''; ?>>
                                <option value="aktif" selected>Aktif</option>
                                <option value="non_aktif">Non Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-save" <?php echo $errorMessage ? 'disabled' : ''; ?>>
                        <i class="fas fa-save"></i> Buat Akun Karyawan
                    </button>
                    <a href="data_karyawan.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Fungsi untuk mencari pelamar
        document.getElementById('searchBtn').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchNama').value.trim();
            if (searchTerm === '') {
                alert('Masukkan nama pelamar untuk dicari');
                return;
            }
            
            const resultsDiv = document.getElementById('searchResults');
            resultsDiv.innerHTML = 'Loading...';
            resultsDiv.style.display = 'block';
            
            fetch(`?search=true&nama=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultsDiv.innerHTML = '<div class="search-result-item">Tidak ada data pelamar ditemukan atau pelamar sudah menjadi karyawan</div>';
                        return;
                    }
                    
                    data.forEach(pelamar => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <strong>${pelamar.nama_lengkap}</strong><br>
                            <small>Posisi: ${pelamar.posisi_dilamar} | Email: ${pelamar.email}</small>
                        `;
                        item.addEventListener('click', function() {
                            // Redirect ke halaman ini dengan parameter pelamar_id
                            window.location.href = `?pelamar_id=${pelamar.id}`;
                        });
                        resultsDiv.appendChild(item);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsDiv.innerHTML = '<div class="search-result-item">Terjadi kesalahan saat mencari data</div>';
                });
        });

        // Fungsi untuk generate kode karyawan otomatis
       // Fungsi untuk generate email otomatis (opsional)
        document.getElementById('nama_lengkap').addEventListener('blur', function() {
            const emailInput = document.getElementById('email');
            const namaInput = document.getElementById('nama_lengkap');
            
            // Hanya generate email otomatis jika email masih kosong
            if (emailInput.value === '' && namaInput.value !== '' && !namaInput.readOnly) {
                const nama = namaInput.value.toLowerCase().replace(/\s+/g, '.');
                emailInput.value = nama + '@ypd.com';
            }
        });

        // Fungsi untuk generate email otomatis (opsional)
        document.getElementById('nama_lengkap').addEventListener('blur', function() {
            const emailInput = document.getElementById('email');
            const namaInput = document.getElementById('nama_lengkap');
            
            // Hanya generate email otomatis jika email masih kosong
            if (emailInput.value === '' && namaInput.value !== '' && !namaInput.readOnly) {
                const nama = namaInput.value.toLowerCase().replace(/\s+/g, '.');
                emailInput.value = nama + '@ypd.com';
            }
        });

        // Generate password default
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            if (!passwordInput.readOnly) {
                const defaultPassword = Math.random().toString(36).slice(-8);
                passwordInput.value = defaultPassword;
            }
        });

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            if (!this.disabled) {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });

        // Pencarian dengan enter
        document.getElementById('searchNama').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchBtn').click();
            }
        });

        // Validasi form sebelum submit
        document.getElementById('tambahForm').addEventListener('submit', function(e) {
            const namaInput = document.getElementById('nama_lengkap');
            if (namaInput.readOnly) {
                e.preventDefault();
                alert('Tidak dapat menambahkan karyawan karena pelamar sudah terdaftar. Silakan pilih pelamar lain atau tambah karyawan manual.');
                return false;
            }
        });
    </script>
</body>
</html>
