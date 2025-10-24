<?php
session_start();
require_once 'config.php'; 

$display_data = false;
$error_msg = '';
$file_surat_path = null;

function handleFileUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $uploadDir = 'uploads/surat_sakit/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    } else {
        return false;
    }
}

function isHoliday($dateString) {
    $fixedHolidays = [
        '01-01', // 1 Januari
        '08-17', // 17 Agustus
        '12-25'  // 25 Desember
    ];
    
    $monthDay = date('m-d', strtotime($dateString));
    return in_array($monthDay, $fixedHolidays);
}

function isWeekend($dateString) {
    $dayOfWeek = date('w', strtotime($dateString));
    return $dayOfWeek == 0 || $dayOfWeek == 6; // 0 = Minggu, 6 = Sabtu
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_SESSION['user'];
    $kode_karyawan = $user['kode_karyawan'];
    $nama_karyawan = $user['nama_lengkap'];
    $divisi = $user['divisi'];
    $jabatan = $user['jabatan'];
    $role = $user['role'];

    $jenis_cuti_raw = $_POST['jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $alasan = $_POST['alasan_cuti'];
    $jenis_cuti = $jenis_cuti_raw;

    // Validasi weekend dan holiday
    if (isWeekend($tanggal_mulai) || isHoliday($tanggal_mulai)) {
        $error_msg = "Tanggal mulai tidak boleh pada hari weekend atau hari libur nasional.";
    }
    
    if (isWeekend($tanggal_akhir) || isHoliday($tanggal_akhir)) {
        $error_msg = "Tanggal akhir tidak boleh pada hari weekend atau hari libur nasional.";
    }

    if ($jenis_cuti_raw === 'Khusus' && !empty($_POST['jenis_cuti_khusus'])) {
        $jenis_cuti = 'Khusus - ' . $_POST['jenis_cuti_khusus'];
    }

    if ($jenis_cuti_raw === 'Sakit') {
        if (!isset($_FILES['bukti_surat_dokter']) || $_FILES['bukti_surat_dokter']['error'] === UPLOAD_ERR_NO_FILE) {
            $error_msg = "Untuk cuti sakit, wajib mengunggah bukti surat keterangan dokter.";
        } else {
            $uploadResult = handleFileUpload($_FILES['bukti_surat_dokter']);
            if ($uploadResult === false) {
                $error_msg = "Gagal mengunggah file surat dokter.";
            } else {
                $file_surat_path = $uploadResult;
            }
        }
    }

    if (empty($kode_karyawan) || empty($jenis_cuti) || empty($tanggal_mulai) || empty($tanggal_akhir) || empty($alasan)) {
        $error_msg = "Semua field wajib diisi.";
    }

    // Validasi tanggal
    if ($tanggal_akhir < $tanggal_mulai) {
        $error_msg = "Tanggal akhir tidak boleh lebih awal dari tanggal mulai.";
    }

    if (empty($error_msg)) {
        $sql = "INSERT INTO data_pengajuan_cuti (kode_karyawan, nama_karyawan, divisi, jabatan, role, jenis_cuti, tanggal_mulai, tanggal_akhir, alasan, file_surat_dokter, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Persetujuan')";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssssss", $kode_karyawan, $nama_karyawan, $divisi, $jabatan, $role, $jenis_cuti, $tanggal_mulai, $tanggal_akhir, $alasan, $file_surat_path);
            if (mysqli_stmt_execute($stmt)) {
                $display_data = true; 
            } else {
                $error_msg = "Gagal menyimpan data: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Gagal mempersiapkan statement: " . mysqli_error($conn);
        }
    }
} else {
    $display_data = false;
    $error_msg = "Metode request tidak valid.";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Status Pengajuan Cuti</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; display:flex; flex-direction:column; }
    header { background:#fff; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo {display:flex;align-items:center;gap:16px;font-weight:500;font-size:20px;color:#2e1f4f;}
    .logo img {width:140px;height:50px;object-fit:contain;}
    nav ul {list-style:none;margin:0;padding:0;display:flex;gap:30px;}
    nav li {position:relative;}
    nav a {text-decoration:none;color:#333;font-weight:600;}
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
    main { flex:1; display:flex; justify-content:center; align-items:center; padding:40px 20px; }
    .container { width:100%; max-width:600px; background:rgba(255,255,255,0.95); border-radius:15px; padding:30px 40px; box-shadow:0 0 15px rgba(0,0,0,0.2); }
    h2 { text-align:center; font-size:22px; color:#2e1f4f; margin-bottom:20px; }
    .message { padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid transparent; text-align: center; font-weight: 600; }
    .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    .info-box { background-color: #f0f0f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4a3f81; text-align: left; }
    .info-box p { margin: 8px 0; font-size: 14px; color: #333; }
    .info-box strong { color: #4a3f81; }
    .action-buttons { display: flex; gap: 10px; margin-top: 25px; }
    .btn { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; text-align: center; text-decoration: none; display: inline-block; }
    .btn-primary { background-color: #4a3f81; color: #fff; }
    .btn-primary:hover { background-color: #3a3162; }
    .btn-secondary { background-color: #6c757d; color: #fff; }
    .btn-secondary:hover { background-color: #545b62; }
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

<main>
    <div class="container">
        <h2>Status Pengajuan Cuti</h2>

        <?php if ($display_data): ?>
            <div class="message success-message">Pengajuan Cuti berhasil dikirim!</div>
            
            <div class="info-box">
                <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($kode_karyawan); ?></p>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_karyawan); ?></p>
                <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
                <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($role)); ?></p>
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #4a3f81;">Detail Pengajuan Cuti</h3>
                <p><strong>Jenis Cuti:</strong> <?php echo htmlspecialchars($jenis_cuti); ?></p>
                <p><strong>Tanggal Mulai:</strong> <?php echo htmlspecialchars($tanggal_mulai); ?></p>
                <p><strong>Tanggal Akhir:</strong> <?php echo htmlspecialchars($tanggal_akhir); ?></p>
                <p><strong>Alasan:</strong> <?php echo htmlspecialchars($alasan); ?></p>
                <?php if ($file_surat_path): ?>
                    <p><strong>Bukti Surat Dokter:</strong> Berkas Terlampir</p>
                <?php endif; ?>
                <p><strong>Status:</strong> <span style="color: #f39c12; font-weight: bold;">Menunggu Persetujuan</span></p>
            </div>

        <?php else: ?>
            <div class="message error-message">
                <?php echo !empty($error_msg) ? htmlspecialchars($error_msg) : 'Terjadi kesalahan saat memproses pengajuan Anda.'; ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="formcutikaryawan.php" class="btn btn-primary">Ajukan Cuti Lain</a>
            <a href="dashboardkaryawan.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
</main>
</body>
</html>