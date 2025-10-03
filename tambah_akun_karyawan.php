<?php
session_start();

// Cek jika form telah di-submit (metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form
    $new_karyawan = array(
        'kode'    => $_POST['kode_karyawan'],
        'nama'    => $_POST['nama_lengkap'],
        'divisi'  => $_POST['divisi'],
        'role'    => $_POST['role'],
        'telepon' => $_POST['no_telp'],
        'email'   => $_POST['email']
        // Password tidak disimpan ke session untuk ditampilkan di tabel,
        // tapi di aplikasi nyata, ini akan di-hash dan disimpan ke database.
    );

    // 2. Tambahkan data karyawan baru ke dalam array di session
    // Inisialisasi session jika belum ada
    if (!isset($_SESSION['karyawan'])) {
        $_SESSION['karyawan'] = [];
    }
    $_SESSION['karyawan'][] = $new_karyawan;

    // 3. Set pesan sukses untuk ditampilkan di halaman daftar
    $_SESSION['success_message'] = "Akun karyawan baru untuk " . htmlspecialchars($_POST['nama_lengkap']) . " berhasil ditambahkan!";

    // 4. Redirect kembali ke halaman daftar akun
    header("Location: daftar_akun_karyawan.php");
    exit();
}

// Jika bukan metode POST, maka tampilkan halaman HTML di bawah ini.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
        header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
        .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
        .logo img { width: 50px; height: 50px; object-fit: contain; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
        nav li { position:relative; }
        nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
        nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
        main { max-width:1000px; margin:40px auto; padding:0 20px; }
        .welcome-section { margin-bottom: 20px; }
        .welcome-section h1 { color: #fff; font-size: 28px; margin-bottom: 5px; }
        .welcome-section h2 { color: #fff; opacity: 0.9; font-size: 20px; margin-bottom: 20px; font-weight: 400; }
        .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
        .form-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .form-header h3 { color: #1E105E; font-size: 22px; margin: 0; }
        .form-header p { margin: 5px 0 0; color: #666; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1E105E; }
        .form-group.full-width { grid-column: 1 / -1; }
        .action-buttons { display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; }
        .btn { padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; text-decoration: none; font-size: 16px; }
        .btn-save { background-color: #2ecc71; color: white; }
        .btn-cancel { background-color: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="image/namayayasan.png" alt="Logo Yayasan"> <span>Yayasan Purba Danarta</span>
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
                        <li><a href="daftar_akun_karyawan.php">Data Karyawan</a></li>
                    </ul>
                </li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="welcome-section">
            <h1>Welcome, Cell!</h1> <h2>Tambah Akun Karyawan Baru</h2>
        </div>
        
        <div class="card">
            <form id="tambahForm" method="POST" action="tambah_akun_karyawan.php">
                <div class="form-header">
                    <h3>Formulir Akun Karyawan</h3>
                    <p>Isi semua informasi yang diperlukan untuk membuat akun baru.</p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="kode_karyawan">Kode Karyawan *</label>
                        <input type="text" id="kode_karyawan" name="kode_karyawan" placeholder="Contoh: YPD010" required>
                    </div>
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap *</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" placeholder="contoh@ypd.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" placeholder="Buat password untuk login" required>
                    </div>
                     <div class="form-group">
                        <label for="no_telp">No. Telepon *</label>
                        <input type="tel" id="no_telp" name="no_telp" placeholder="Contoh: 08123456789" required>
                    </div>
                    <div class="form-group">
                        <label for="divisi">Divisi / Posisi *</label>
                        <select id="divisi" name="divisi" required>
                            <option value="" disabled selected>Pilih Divisi / Posisi</option>
                            <option value="Direksi">Direksi</option>
                            <option value="SDM">SDM</option>
                            <option value="Training">Training</option>
                            <option value="Keuangan">Keuangan</option>
                            <option value="Konsultasi">Konsultasi</option>
                            <option value="Wisma">Wisma</option>
                            <option value="Sekretariat">Sekretariat</option>
                        </select>
                    </div>
                     <div class="form-group full-width">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Pilih Role</option>
                            <option value="direktur">Direktur</option>
                            <option value="admin">Admin</option>
                            <option value="penanggung jawab">Penanggung Jawab</option>
                            <option value="karyawan">Karyawan / Staff</option>
                        </select>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="daftar_akun_karyawan.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>