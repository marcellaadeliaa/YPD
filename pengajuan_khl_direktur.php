<?php
// FILE: pengajuan_khl_direktur.php
session_start();

// ===========================================
// 1. KONFIGURASI DAN KONEKSI DATABASE
// ===========================================
$server = "localhost";
$username = "root";
$password = "";
$database = "ypd_ibd"; // Database Anda

$conn = new mysqli($server, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Inisialisasi variabel untuk pesan flash
$error_message = $_SESSION['error_message'] ?? "";
$success_message = $_SESSION['success_message'] ?? "";
unset($_SESSION['error_message'], $_SESSION['success_message']);

// ===========================================
// 2. DATA AWAL DIREKTUR LOGIN
// ===========================================
// Cek jika session 'kode_karyawan' ada, jika tidak gunakan placeholder
$user_kode_session = $_SESSION['kode_karyawan'] ?? 'YPD0001'; 
$nama_direktur = "Pengguna";
$divisi_direktur = "Direktur";

// Ambil data Direktur dari database
$stmt_direktur = $conn->prepare("SELECT nama_lengkap, divisi FROM data_karyawan WHERE kode_karyawan = ?");
$stmt_direktur->bind_param("s", $user_kode_session);
$stmt_direktur->execute();
$result_direktur = $stmt_direktur->get_result();

if ($result_direktur->num_rows > 0) {
    $data_direktur = $result_direktur->fetch_assoc();
    $nama_direktur = htmlspecialchars($data_direktur['nama_lengkap']);
    $divisi_direktur = htmlspecialchars($data_direktur['divisi']);
}
$stmt_direktur->close();

// Data proyek (dropdown)
$proyek_list = ["Proyek A (Jakarta)", "Proyek B (Bandung)", "Proyek C (Surabaya)", "Proyek Internal"];

// Untuk menjaga nilai input kode_karyawan
$kode_karyawan_input_value = $_POST['kode_karyawan'] ?? ""; 

// ===========================================
// 3. LOGIKA SIMPAN PENGAJUAN KHL
// ===========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_karyawan_input = trim($_POST['kode_karyawan'] ?? ""); 
    $proyek = $_POST['proyek'] ?? "";
    $tanggal_khl = $_POST['tanggal_khl'] ?? "";

    $is_valid = true;

    // --- Validasi Mandatory Field ---
    if (empty($kode_karyawan_input) || empty($proyek) || empty($tanggal_khl)) {
        $is_valid = false;
        $_SESSION['error_message'] = "Gagal: Semua field wajib diisi.";
    }

    // --- Cek karyawan ---
    if ($is_valid) {
        $stmt_check = $conn->prepare("SELECT nama_lengkap, divisi FROM data_karyawan WHERE kode_karyawan = ?");
        $stmt_check->bind_param("s", $kode_karyawan_input);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            $is_valid = false;
            $_SESSION['error_message'] = "Kode karyawan $kode_karyawan_input tidak ditemukan.";
        } else {
            $data_check = $result_check->fetch_assoc();
            $nama_karyawan_post = $data_check['nama_lengkap'];
            $divisi_karyawan_post = $data_check['divisi'];
        }
        $stmt_check->close();
    }

    // --- Simpan ke database ---
    if ($is_valid) {
        $status = "Menunggu Persetujuan Direktur";
        // Query disesuaikan dengan kolom yang Anda berikan
        $sql = "INSERT INTO pengajuan_khl (kode_karyawan, nama_karyawan, divisi, tanggal_khl, nama_proyek, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql);
        $stmt_insert->bind_param("ssssss", 
            $kode_karyawan_input, 
            $nama_karyawan_post, 
            $divisi_karyawan_post, 
            $tanggal_khl, 
            $proyek,
            $status
        );

        if ($stmt_insert->execute()) { 
            $_SESSION['success_message'] = "Pengajuan KHL untuk $nama_karyawan_post berhasil diajukan.";
            header("Location: riwayat_khl_direktur.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan pengajuan: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }

    if (!$is_valid) {
        header("Location: pengajuan_khl_direktur.php");
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan KHL - Yayasan Purba Danarta</title>
    <style>
        /* ======================================= */
        /* CSS DIAMBIL DARI dashboard_direktur.php */
        /* ======================================= */
        :root {
            --primary-color: #1E105E; 
            --secondary-color: #8897AE;
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF;
            --text-color-light: #fff;
            --text-color-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh;
            color: var(--text-color-light);
            padding-bottom: 50px;
        }

        header {
            background: var(--card-bg);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent-color);
            flex-wrap: wrap;
            box-shadow: 0 4px 15px var(--shadow-light);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 500;
            font-size: 20px;
            color: var(--text-color-dark);
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
            gap: 30px;
        }
        nav a {
            text-decoration: none;
            color: var(--text-color-dark);
            font-weight: 600;
            padding: 8px 4px;
            display: block;
            transition: color 0.3s ease;
        }
        nav a:hover {
            color: var(--accent-color);
        }
        nav li { position: relative; }
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
            color: var(--text-color-dark); 
            font-weight: 400; 
            white-space: nowrap; 
        }
        
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .welcome-section {
            text-align: left;
            margin-bottom: 30px;
        }
        .welcome-section h1 {
            font-size: 2.5rem;
            margin: 0;
        }
        
        .form-card {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 40px 60px;
            box-shadow: 0 5px 20px var(--shadow-light);
            max-width: 500px; 
            margin: 0 auto;
            text-align: left;
        }

        .form-card h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color-dark);
            font-size: 1rem;
        }
        
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f0f0f5;
            box-sizing: border-box;
            font-size: 1rem;
            color: var(--text-color-dark);
        }
        
        .select-wrapper {
            position: relative;
        }
        .select-wrapper::after {
            content: '⌄'; 
            position: absolute;
            top: 55%;
            right: 20px;
            transform: translateY(-50%);
            color: var(--secondary-color);
            pointer-events: none;
            font-size: 1.2rem;
        }
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .submit-btn {
            background: var(--accent-color);
            color: var(--text-color-light);
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
            display: block; 
            width: 100%;
            margin-top: 40px;
        }
        .submit-btn:hover {
            background-color: #352d5c;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            color: white; /* Diatur agar konsisten dengan warna background */
        }

        .alert-success {
            background-color: var(--success-color);
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.4);
        }

        .alert-error {
            background-color: var(--error-color);
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.4);
        }

        @media(max-width: 768px){
            header{
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }
            nav ul{
                flex-direction: column;
                gap: 10px;
                width: 100%;
                margin-top: 15px;
            }
            nav li ul { 
                position: static; 
                border: none; 
                box-shadow: none; 
                padding-left: 20px; 
            }
            .form-card{
                padding: 30px 20px;
                max-width: 100%; 
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
            <li><a href="dashboarddirektur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li>
                </ul>
            </li>
            <li style="border-bottom: 2px solid var(--accent-color);"><a href="#">KHL ▾</a> 
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li>
                    <li><a href="pengajuan_khl_direktur.php" style="color: var(--accent-color); font-weight: 700;">Pengajuan KHL</a></li>
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

<div class="container">
    <div class="welcome-section">
        <h1>Welcome, <?= $nama_direktur ?>!</h1>
        <p>Anda login sebagai <?= $divisi_direktur ?></p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><?= $error_message ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h2>Pengajuan KHL</h2>
        <form action="pengajuan_khl_direktur.php" method="POST">
            <div class="form-group">
                <label for="kode_karyawan">Kode Karyawan</label>
                <input type="text" id="kode_karyawan" name="kode_karyawan" 
                        value="<?= htmlspecialchars($kode_karyawan_input_value) ?>" 
                        placeholder="Contoh: YPD0001" required>
            </div>
            
            <div class="form-group select-wrapper">
                <label for="proyek">Proyek</label>
                <select id="proyek" name="proyek" required>
                    <option value="" disabled selected>Pilih Proyek</option>
                    <?php 
                    $selected_proyek = $_POST['proyek'] ?? '';
                    foreach ($proyek_list as $proyek_item): ?>
                        <option value="<?= htmlspecialchars($proyek_item) ?>" 
                            <?= $selected_proyek == $proyek_item ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proyek_item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="tanggal_khl">Tanggal KHL</label>
                <input type="date" id="tanggal_khl" name="tanggal_khl" 
                        value="<?= $_POST['tanggal_khl'] ?? '' ?>" required>
            </div>
            
            <button type="submit" class="submit-btn">Ajukan KHL</button>
        </form>
    </div>
</div>
</body>
</html>