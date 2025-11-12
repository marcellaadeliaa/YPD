<?php
session_start();
require_once 'config.php';

$display_data = false;
$error_msg = '';

// Fungsi validasi weekend dan holiday
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

// Fungsi konversi waktu ke menit
function convertTimeToMinutes($timeString) {
    list($hours, $minutes) = explode(':', $timeString);
    return ($hours * 60) + $minutes;
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_SESSION['user'];
    $kode_karyawan = $user['kode_karyawan'];
    $nama_karyawan = $user['nama_lengkap'];
    $divisi = $user['divisi'];
    $jabatan = $user['jabatan'];
    $role = 'direktur';

    // Sanitize input data
    $proyek = trim($_POST['proyek'] ?? '');
    $tanggal_khl = trim($_POST['tanggal_khl'] ?? '');
    $jam_mulai_kerja = trim($_POST['jam_mulai_kerja'] ?? '');
    $jam_akhir_kerja = trim($_POST['jam_akhir_kerja'] ?? '');
    $tanggal_cuti_khl = trim($_POST['tanggal_cuti_khl'] ?? '');
    $jam_mulai_cuti_khl = trim($_POST['jam_mulai_cuti_khl'] ?? '');
    $jam_akhir_cuti_khl = trim($_POST['jam_akhir_cuti_khl'] ?? '');

    // Validasi field wajib
    if (empty($proyek) || empty($tanggal_khl) || empty($jam_mulai_kerja) || empty($jam_akhir_kerja) || 
        empty($tanggal_cuti_khl) || empty($jam_mulai_cuti_khl) || empty($jam_akhir_cuti_khl)) {
        $error_msg = "Semua field harus diisi.";
    }

    // Validasi format tanggal
    if (empty($error_msg)) {
        if (!DateTime::createFromFormat('Y-m-d', $tanggal_khl)) {
            $error_msg = "Format tanggal KHL tidak valid.";
        }
        
        if (!DateTime::createFromFormat('Y-m-d', $tanggal_cuti_khl)) {
            $error_msg = "Format tanggal cuti KHL tidak valid.";
        }
    }

    // Validasi format waktu
    if (empty($error_msg)) {
        if (!DateTime::createFromFormat('H:i', $jam_mulai_kerja) || 
            !DateTime::createFromFormat('H:i', $jam_akhir_kerja) ||
            !DateTime::createFromFormat('H:i', $jam_mulai_cuti_khl) || 
            !DateTime::createFromFormat('H:i', $jam_akhir_cuti_khl)) {
            $error_msg = "Format jam tidak valid.";
        }
    }

    // HILANGKAN VALIDASI WEEKEND DAN HOLIDAY UNTUK TANGGAL KHL
    // Validasi untuk tanggal KHL dihapus (boleh weekend dan libur)

    // TETAPKAN VALIDASI UNTUK TANGGAL CUTI KHL
    if (empty($error_msg) && (isWeekend($tanggal_cuti_khl) || isHoliday($tanggal_cuti_khl))) {
        $error_msg = "Tanggal Cuti KHL tidak boleh pada hari weekend atau hari libur nasional.";
    }

    // Validasi tanggal tidak sama
    if (empty($error_msg) && $tanggal_khl === $tanggal_cuti_khl) {
        $error_msg = "Tanggal KHL dan Tanggal Cuti KHL tidak boleh sama.";
    }

    // Validasi jam kerja dengan fungsi convertTimeToMinutes
    if (empty($error_msg)) {
        $jam_mulai_kerja_minutes = convertTimeToMinutes($jam_mulai_kerja);
        $jam_akhir_kerja_minutes = convertTimeToMinutes($jam_akhir_kerja);
        if ($jam_akhir_kerja_minutes <= $jam_mulai_kerja_minutes) {
            $error_msg = "Jam akhir kerja harus setelah jam mulai kerja.";
        }
        
        // Validasi durasi kerja minimal 1 jam
        $durasi_kerja = $jam_akhir_kerja_minutes - $jam_mulai_kerja_minutes;
        if ($durasi_kerja < 60) {
            $error_msg = "Durasi kerja minimal 1 jam.";
        }
    }

    // Validasi jam cuti dengan fungsi convertTimeToMinutes
    if (empty($error_msg)) {
        $jam_mulai_cuti_minutes = convertTimeToMinutes($jam_mulai_cuti_khl);
        $jam_akhir_cuti_minutes = convertTimeToMinutes($jam_akhir_cuti_khl);
        if ($jam_akhir_cuti_minutes <= $jam_mulai_cuti_minutes) {
            $error_msg = "Jam akhir cuti harus setelah jam mulai cuti.";
        }
        
        // Validasi durasi cuti minimal 1 jam
        $durasi_cuti = $jam_akhir_cuti_minutes - $jam_mulai_cuti_minutes;
        if ($durasi_cuti < 60) {
            $error_msg = "Durasi cuti minimal 1 jam.";
        }
    }

    // Validasi kode karyawan
    if (empty($error_msg)) {
        $check_karyawan = "SELECT kode_karyawan FROM data_karyawan WHERE kode_karyawan = ?";
        $stmt_check = mysqli_prepare($conn, $check_karyawan);
        mysqli_stmt_bind_param($stmt_check, "s", $kode_karyawan);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) == 0) {
            $error_msg = "Kode karyawan tidak valid.";
        }
        mysqli_stmt_close($stmt_check);
    }

    if (empty($error_msg)) {
        $status_khl = "disetujui"; // Status otomatis disetujui untuk direktur
        
        $query_insert = "INSERT INTO data_pengajuan_khl (
            kode_karyawan, divisi, jabatan, role, proyek, 
            tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, 
            tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl, 
            status_khl
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        
        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, "ssssssssssss", 
                $kode_karyawan, $divisi, $jabatan, $role, $proyek,
                $tanggal_khl, $jam_mulai_kerja, $jam_akhir_kerja,
                $tanggal_cuti_khl, $jam_mulai_cuti_khl, $jam_akhir_cuti_khl,
                $status_khl
            );
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $id_khl = mysqli_insert_id($conn);
                $display_data = true;
            } else {
                $error_msg = "Gagal menyimpan data: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt_insert);
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
<title>Status Pengajuan KHL - Direktur</title>
<style>
    :root { 
        --primary-color: #1E105E; 
        --secondary-color: #8897AE; 
        --accent-color: #4a3f81; 
        --card-bg: #FFFFFF; 
        --text-color-light: #fff; 
        --text-color-dark: #2e1f4f; 
        --shadow-light: rgba(0,0,0,0.15); 
    }
    
    body { 
        margin: 0; 
        font-family: 'Segoe UI', sans-serif; 
        background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
        min-height: 100vh; 
        color: var(--text-color-light); 
        padding-bottom: 40px; 
    }
    
    header { 
        background: var(--card-bg); 
        padding: 20px 40px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
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
    
    nav li { 
        position: relative; 
    }
    
    nav a { 
        text-decoration: none; 
        color: var(--text-color-dark); 
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
    
    nav li:hover > ul { 
        display: block; 
    }
    
    nav li ul li a { 
        color: var(--text-color-dark); 
        font-weight: 400; 
        white-space: nowrap; 
        padding: 5px 20px; 
    }
    
    main { 
        max-width: 1200px; 
        margin: 40px auto; 
        padding: 0 20px; 
    }
    
    .container { 
        width: 100%; 
        max-width: 600px; 
        background: var(--card-bg); 
        color: var(--text-color-dark);
        border-radius: 20px; 
        padding: 30px 40px; 
        box-shadow: 0 5px 20px var(--shadow-light); 
        margin: 0 auto;
    }
    
    h2 { 
        text-align: center; 
        font-size: 22px; 
        color: #2e1f4f; 
        margin-bottom: 20px; 
    }
    
    .message { 
        padding: 12px; 
        border-radius: 5px; 
        margin-bottom: 20px; 
        border: 1px solid transparent; 
        text-align: center; 
        font-weight: 600; 
    }
    
    .success-message { 
        background-color: #d4edda; 
        color: #155724; 
        border-color: #c3e6cb; 
    }
    
    .error-message { 
        background-color: #f8d7da; 
        color: #721c24; 
        border-color: #f5c6cb; 
    }
    
    .info-box { 
        background-color: #f0f0f0; 
        padding: 15px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        border-left: 4px solid #4a3f81; 
        text-align: left; 
    }
    
    .info-box p { 
        margin: 8px 0; 
        font-size: 14px; 
        color: #333; 
    }
    
    .info-box strong { 
        color: #4a3f81; 
    }
    
    .action-buttons { 
        display: flex; 
        gap: 10px; 
        margin-top: 25px; 
    }
    
    .btn { 
        flex: 1; 
        padding: 12px; 
        border: none; 
        border-radius: 8px; 
        font-weight: 700; 
        font-size: 15px; 
        cursor: pointer; 
        text-align: center; 
        text-decoration: none; 
        display: inline-block; 
    }
    
    .btn-primary { 
        background-color: #4a3f81; 
        color: #fff; 
    }
    
    .btn-primary:hover { 
        background-color: #3a3162; 
    }
    
    .btn-secondary { 
        background-color: #6c757d; 
        color: #fff; 
    }
    
    .btn-secondary:hover { 
        background-color: #545b62; 
    }
    
    .status-approved { 
        color: #28a745; 
        font-weight: bold; 
    }
    
    .note-box { 
        background-color: #d4edda; 
        color: #155724; 
        padding: 12px; 
        border-radius: 5px; 
        margin: 15px 0; 
        border: 1px solid #c3e6cb; 
        font-size: 14px; 
    }
    
    .khl-id { 
        background-color: #e7f3ff; 
        color: #0c5460; 
        padding: 10px;  
        border-radius: 5px; 
        margin: 10px 0; 
        border: 1px solid #b8daff; 
        text-align: center; 
        font-weight: bold; 
    }
    
    .auto-approved-badge {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border-radius: 20px;
        font-weight: bold;
        text-align: center;
        margin: 15px 0;
        font-size: 16px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
            margin: 20px 10px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        header {
            padding: 15px 20px;
            flex-direction: column;
            gap: 15px;
        }
        
        nav ul {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>
</head>
<body>
    <header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboarddirektur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Semua Cuti</a></li>
                    <li><a href="riwayat_cuti_pribadi_direktur.php">Riwayat Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_direktur.php">Kalender Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat Semua KHL</a></li>
                    <li><a href="riwayat_khl_pribadi_direktur.php">Riwayat KHL Pribadi</a></li>
                    <li><a href="kalender_khl_direktur.php">Kalender KHL</a></li>
                </ul>
            </li>
            <li><a href="#">Karyawan ▾</a>
                <ul>
                    <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
                </ul>
            </li>
            <li><a href="#">Pelamar ▾</a>
                <ul>
                    <li><a href="riwayat_pelamar_direktur.php">Riwayat Pelamar</a></li>
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
        <div class="container">
            <h2>Status Pengajuan KHL - Direktur</h2>

            <?php if ($display_data): ?>
                <div class="auto-approved-badge">
                    ✅ PENGAJUAN OTOMATIS DISETUJUI
                </div>
                
                <div class="message success-message">Pengajuan KHL berhasil dikirim!</div>
                
                <div class="khl-id">
                    ID Pengajuan: KHL-<?php echo htmlspecialchars($id_khl); ?>
                </div>
                
                <div class="note-box">
                    <strong>Status Otomatis Disetujui:</strong> Sebagai Direktur, pengajuan KHL Anda langsung disetujui tanpa perlu menunggu persetujuan.
                </div>
                
                <div class="info-box">
                    <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($kode_karyawan); ?></p>
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_karyawan); ?></p>
                    <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
                    <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $role))); ?></p>
                </div>

                <div class="info-box">
                    <h3 style="margin-top: 0; color: #4a3f81;">Detail Pengajuan KHL</h3>
                    <p><strong>Proyek:</strong> <?php echo htmlspecialchars($proyek); ?></p>
                    <p><strong>Tanggal KHL:</strong> <?php echo htmlspecialchars($tanggal_khl); ?></p>
                    <p><strong>Jam Kerja:</strong> <?php echo htmlspecialchars($jam_mulai_kerja); ?> - <?php echo htmlspecialchars($jam_akhir_kerja); ?></p>
                    <p><strong>Tanggal Cuti KHL:</strong> <?php echo htmlspecialchars($tanggal_cuti_khl); ?></p>
                    <p><strong>Jam Cuti KHL:</strong> <?php echo htmlspecialchars($jam_mulai_cuti_khl); ?> - <?php echo htmlspecialchars($jam_akhir_cuti_khl); ?></p>
                    <p><strong>Status:</strong> <span class="status-approved">Disetujui</span></p>
                </div>

            <?php else: ?>
                <div class="message error-message">
                    <?php echo !empty($error_msg) ? htmlspecialchars($error_msg) : 'Terjadi kesalahan saat memproses pengajuan Anda.'; ?>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="pengajuan_khl_direktur.php" class="btn btn-primary">Ajukan KHL Lain</a>
                <a href="dashboarddirektur.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                <a href="riwayat_khl_pribadi_direktur.php" class="btn btn-secondary">Lihat Riwayat KHL</a>
            </div>
        </div>
    </main>
</body>
</html>