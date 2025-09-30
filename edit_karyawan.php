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
    'nama' => '',
    'divisi' => '',
    'role' => '',
    'telepon' => '',
    'email' => '',
    'alamat' => '',
    'tanggal_masuk' => '',
    'status' => '',
    'tanggal_lahir' => '',
    'jenis_kelamin' => ''
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan</title>
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
        
        .form-header {
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
            cursor: pointer;
        }
        
        .photo-upload {
            text-align: center;
        }
        
        .photo-upload input {
            display: none;
        }
        
        .photo-label {
            display: block;
            margin-top: 10px;
            color: #666;
            cursor: pointer;
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
            <h2>Edit Data Karyawan</h2>
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
                'nama' => '',
                'divisi' => '',
                'role' => '',
                'telepon' => '',
                'email' => '',
                'alamat' => '',
                'tanggal_masuk' => '',
                'status' => '',
                'tanggal_lahir' => '',
                'jenis_kelamin' => ''
            );
            ?>
            
            <form id="editForm" method="POST" action="update_karyawan.php">
                <input type="hidden" name="id_lama" value="<?php echo $karyawan_id; ?>">
                
                <div class="form-header">
                    <div class="photo-upload">
                        <img src="https://via.placeholder.com/150" alt="Foto Karyawan" class="employee-photo" id="photoPreview">
                        <input type="file" id="photoInput" accept="image/*">
                        <label for="photoInput" class="photo-label">Ubah Foto</label>
                    </div>
                    <div>
                        <h3>Edit Data: <?php echo $karyawan['nama']; ?></h3>
                        <p><strong>ID Karyawan Saat Ini:</strong> <?php echo $karyawan_id; ?></p>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Karyawan</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="kode_karyawan">No. Kode Karyawan</label>
                            <input type="text" id="kode_karyawan" name="kode_karyawan" value="<?php echo $karyawan_id; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="<?php echo $karyawan['nama']; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Pribadi</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" <?php echo $karyawan['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo $karyawan['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $karyawan['tanggal_lahir']; ?>" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="3" required><?php echo $karyawan['alamat']; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Kontak</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="telepon">No. Telepon</label>
                            <input type="tel" id="telepon" name="telepon" value="<?php echo $karyawan['telepon']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $karyawan['email']; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Pekerjaan</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="divisi">Divisi</label>
                            <select id="divisi" name="divisi" required>
                                <option value="">Pilih Divisi</option>
                                <option value="Training" <?php echo $karyawan['divisi'] == 'Training' ? 'selected' : ''; ?>>Training</option>
                                <option value="Wisma" <?php echo $karyawan['divisi'] == 'Wisma' ? 'selected' : ''; ?>>Wisma</option>
                                <option value="Konsultasi" <?php echo $karyawan['divisi'] == 'Konsultasi' ? 'selected' : ''; ?>>Konsultasi</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" name="role" value="<?php echo $karyawan['role']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_masuk">Tanggal Masuk</label>
                            <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?php echo $karyawan['tanggal_masuk']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="Aktif" <?php echo $karyawan['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="Tidak Aktif" <?php echo $karyawan['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="detail_karyawan.php?id=<?php echo $karyawan_id; ?>" class="btn btn-cancel">
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
        // Preview foto
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulasi penyimpanan data
            if (confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
                alert('Data berhasil disimpan!');
                // Redirect ke halaman detail dengan ID baru
                const newId = document.getElementById('kode_karyawan').value;
                window.location.href = 'detail_karyawan.php?id=' + newId;
            }
        });
    </script>
</body>
</html>