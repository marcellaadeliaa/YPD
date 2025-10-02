<?php
// FILE: pengajuan_cuti_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI, SESSION, DAN PREPARASI DATA
// ===========================================

session_start();

// --- Konfigurasi Database ---
$server = "localhost";
$username = "root";
$password = "";
$database = "ypd_ibd";

$conn = new mysqli($server, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// ===========================================
// AMBIL DATA KARYAWAN YANG SEDANG LOGIN (Direktur)
// ===========================================
// *Pastikan SESSION kode_karyawan sudah diset saat login*
$user_kode_session = isset($_SESSION['kode_karyawan']) ? $_SESSION['kode_karyawan'] : 'YPD9999'; 

$nama_karyawan_login = "";
$sisa_cuti_tahunan = 0;
$sisa_cuti_lustrum = 0;
$divisi_karyawan = "";

$stmt_user = $conn->prepare("SELECT nama_lengkap, divisi, sisa_cuti_tahunan, sisa_cuti_lustrum FROM data_karyawan WHERE kode_karyawan = ?");
$stmt_user->bind_param("s", $user_kode_session);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $data_user = $result_user->fetch_assoc();
    $nama_karyawan_login = htmlspecialchars($data_user['nama_lengkap']);
    $divisi_karyawan = htmlspecialchars($data_user['divisi']);
    $sisa_cuti_tahunan = $data_user['sisa_cuti_tahunan'];
    $sisa_cuti_lustrum = $data_user['sisa_cuti_lustrum'];
} else {
    $nama_karyawan_login = "Pengguna Tidak Dikenal";
}
$stmt_user->close();


// ===========================================
// BAGIAN 2: LOGIKA PEMROSESAN PENGAJUAN CUTI (POST)
// ===========================================

if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['no_karyawan'])) {
        
    // Ambil data POST
    $kode_karyawan_input = trim($_POST['no_karyawan']); 
    $jenis_cuti = $_POST['jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai']; 
    $jumlah_hari = (int)$_POST['jumlah_hari']; 
    $alasan = $_POST['alasan']; 
    
    $is_valid = true;
    $error_message = "";
    
    // 1. Validasi & Ambil Data Karyawan yang diajukan
    $stmt_check = $conn->prepare("SELECT nama_lengkap, divisi, sisa_cuti_tahunan, sisa_cuti_lustrum FROM data_karyawan WHERE kode_karyawan = ?");
    $stmt_check->bind_param("s", $kode_karyawan_input);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        $is_valid = false;
        $error_message = "Gagal: Kode Karyawan ($kode_karyawan_input) tidak ditemukan.";
    } else {
        $data_check = $result_check->fetch_assoc();
        $nama_karyawan_post = $data_check['nama_lengkap'];
        $divisi = $data_check['divisi'];
        $sisa_cuti_tahunan_db = $data_check['sisa_cuti_tahunan'];
        $sisa_cuti_lustrum_db = $data_check['sisa_cuti_lustrum'];
        
        // Hitung tanggal akhir cuti
        $start_date_dt = new DateTime($tanggal_mulai);
        $end_date_dt = clone $start_date_dt;
        if ($jumlah_hari > 0) {
             $end_date_dt->modify('+'.($jumlah_hari - 1).' day');
        }
        $tanggal_akhir = $end_date_dt->format('Y-m-d');
        
        // Validasi Sisa Cuti
        if ($jenis_cuti == 'Tahunan') {
            if ($jumlah_hari > $sisa_cuti_tahunan_db) {
                $is_valid = false;
                $error_message = "Gagal: Jumlah hari cuti tahunan melebihi sisa cuti ($sisa_cuti_tahunan_db hari).";
            }
        } elseif ($jenis_cuti == 'Lustrum') {
            if ($jumlah_hari > $sisa_cuti_lustrum_db) {
                $is_valid = false;
                $error_message = "Gagal: Jumlah hari cuti lustrum melebihi sisa cuti ($sisa_cuti_lustrum_db hari).";
            }
        }
    }
    $stmt_check->close();

    // 4. Proses Insert ke Database
    if ($is_valid) {
        
        $waktu_persetujuan = NULL; 
        
        // --- LOGIKA AUTO-APPROVE DIREKTUR ---
        if ($kode_karyawan_input === $user_kode_session) {
            $status = "Disetujui Direktur"; 
            $waktu_persetujuan = date('Y-m-d H:i:s'); 
            $success_message = "Pengajuan Cuti Anda berhasil diajukan dan telah disetujui otomatis. Status: Disetujui Direktur.";
        } else {
            $status = "Menunggu Persetujuan Direktur"; 
            $success_message = "Pengajuan Cuti berhasil diajukan dan menunggu persetujuan Direktur.";
        }
        
        $created_at = date('Y-m-d H:i:s');
        
        // Query INSERT 
        $stmt_insert = $conn->prepare("INSERT INTO pengajuan_cuti (kode_karyawan, nama_karyawan, divisi, jenis_cuti, tanggal_mulai, tanggal_akhir, alasan, status, created_at, waktu_persetujuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt_insert->bind_param("ssssssssss", $kode_karyawan_input, $nama_karyawan_post, $divisi, $jenis_cuti, $tanggal_mulai, $tanggal_akhir, $alasan, $status, $created_at, $waktu_persetujuan);
        
        if ($stmt_insert->execute()) { 
            
            // Logika Update Sisa Cuti hanya untuk cuti yang mengurangi jatah
            if (($jenis_cuti == 'Tahunan' && $status == 'Disetujui Direktur') || ($jenis_cuti == 'Lustrum' && $status == 'Disetujui Direktur')) {
                $cuti_kolom = ($jenis_cuti == 'Tahunan') ? 'sisa_cuti_tahunan' : 'sisa_cuti_lustrum';
                
                // Catatan: Jika tidak auto-approve (yaitu status: Menunggu Persetujuan Direktur), 
                // pengurangan sisa cuti idealnya dilakukan di halaman persetujuan.
                
                $stmt_update = $conn->prepare("UPDATE data_karyawan SET $cuti_kolom = $cuti_kolom - ? WHERE kode_karyawan = ?");
                $stmt_update->bind_param("is", $jumlah_hari, $kode_karyawan_input);
                $stmt_update->execute();
                $stmt_update->close();
            }
            
            // Redirect ke Riwayat Cuti Direktur
            echo "<script>alert('$success_message'); window.location.href='riwayat_cuti_direktur.php';</script>";
            exit();
        } else {
            $error_message = "Gagal menyimpan pengajuan cuti: " . $conn->error;
        }
        $stmt_insert->close();
    }
    
    if (!empty($error_message)) {
          echo "<script>alert('$error_message');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Cuti - Yayasan Purba Danarta</title>
    <style>
        :root {
            --primary-color: #1E105E;
            --secondary-color: #a29bb8;
            --card-bg: #FFFFFF;
            --text-dark: #2e1f4f;
            --text-light: #fff;
            --input-bg: #F0F0F0;
            --button-color: #4A3F81;
            --shadow-light: rgba(0,0,0,0.15);
            --header-bg: #FFFFFF; 
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 80%);
            min-height: 100vh;
            color: var(--text-light);
        }

        header {
            background: var(--header-bg);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px var(--shadow-light);
            border-bottom: 2px solid var(--button-color);
            flex-wrap: wrap;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 500;
            font-size: 20px;
            color: var(--text-dark);
        }
        .logo img {
            width: 50px; 
            height: 50px;
            object-fit: contain;
            border-radius: 50%;
        }
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center; 
            gap: 30px;
        }
        nav li { position: relative; }
        nav a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            padding: 8px 4px;
            display: block; 
        }
        nav li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0; 
            background: var(--card-bg);
            padding: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-light);
            min-width: 200px;
            z-index: 999;
        }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 20px; }
        nav li ul li a {
            color: var(--text-dark);
            font-weight: 400;
            white-space: nowrap;
        }

        main {
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .welcome {
            max-width: 800px;
            width: 100%;
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-card {
            background: var(--card-bg);
            color: var(--text-dark);
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 4px 20px var(--shadow-light);
        }

        .form-card h2 {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 30px;
            text-align: center;
            color: var(--primary-color);
        }
        .form-card p {
            font-size: 16px;
            margin-bottom: 30px;
            text-align: center;
            opacity: 0.8;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        
        .input-text, .input-select, .input-date {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: var(--input-bg);
            font-size: 16px;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            color: var(--text-dark);
        }
        .input-text:disabled {
            background: #e9e9e9;
            cursor: not-allowed;
            font-style: italic;
        }
        
        .input-select {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
        }
        
        .action-group {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
        }
        
        .btn-submit {
            background: var(--button-color);
            color: var(--text-light);
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #352d5c;
        }

        .sisa-cuti {
            text-align: right;
            line-height: 1.6;
            color: var(--text-dark);
        }
        .sisa-cuti strong {
            display: block;
            margin-bottom: 5px;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            header { 
                padding: 15px 20px; 
            }
            nav ul { 
                display: none; 
            }
            .welcome { 
                font-size: 1.8rem; 
            }
            .form-card { 
                padding: 30px 20px; 
            }
            .action-group {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            .btn-submit { 
                width: 100%; 
            }
            .sisa-cuti { 
                text-align: center; 
                width: 100%;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="image/namayayasan.png" alt="Logo YPD">
        <span>Yayasan Purba Danarta</span>
    </div>
    
    <nav>
        <ul>
            <li><a href="dashboarddirektur.php">Beranda</a></li>
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
    <div class="welcome">Welcome, <?= htmlspecialchars($nama_karyawan_login) ?>!</div>

    <div class="form-card">
        <h2>Pengajuan Cuti Pribadi/Karyawan</h2>
        <p>Silakan isi kode karyawan yang akan mengajukan cuti.</p>
        
        <form action="" method="POST">
            
            <div class="form-group">
                <label for="no_karyawan">No. Kode Karyawan</label>
                <input type="text" id="no_karyawan" name="no_karyawan" class="input-text" 
                        placeholder="Contoh: YPD0001" required> 
            </div>
            
            <div class="form-group">
                <label for="jenis_cuti">Jenis Cuti</label>
                <select id="jenis_cuti" name="jenis_cuti" class="input-select" required>
                    <option value="" disabled selected>Pilih Jenis Cuti</option>
                    <option value="Tahunan">Cuti Tahunan</option>
                    <option value="Sakit">Cuti Sakit</option>
                    <option value="Melahirkan">Cuti Melahirkan</option>
                    <option value="Lustrum">Cuti Lustrum</option>
                    <option value="Lainnya">Cuti Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_mulai">Tanggal Mulai Cuti</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="input-date" required>
            </div>
            
            <div class="form-group">
                <label for="jumlah_hari">Jumlah Hari Cuti (Termasuk hari libur/akhir pekan jika dicantumkan)</label>
                <input type="number" id="jumlah_hari" name="jumlah_hari" class="input-text" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="alasan">Alasan/Keterangan Cuti</label>
                <textarea id="alasan" name="alasan" class="input-text" rows="4" required></textarea>
            </div>
            
            
            <div class="action-group">
                <button type="submit" class="btn-submit">Ajukan Cuti</button>
                
                <div class="sisa-cuti">
                    <strong>Sisa Cuti <?= $nama_karyawan_login ?> (Anda)</strong>
                    Cuti Tahunan: **<?= $sisa_cuti_tahunan ?>** hari<br>
                    Cuti Lustrum: **<?= $sisa_cuti_lustrum ?>** hari
                </div>
            </div>
        </form>
    </div>
</main>
<script>
    // Opsional: Anda bisa menambahkan logika JS untuk menampilkan sisa cuti karyawan yang di-input (jika data tersedia melalui AJAX)
</script>
</body>
</html>