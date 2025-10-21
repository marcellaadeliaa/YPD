<?php
session_start();
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$display_data = false;
$error_msg = '';
$file_surat_path = null;
$insert_success = false;

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

    $jenis_cuti_raw = isset($_POST['jenis_cuti']) ? $_POST['jenis_cuti'] : '';
    $tanggal_mulai = isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : '';
    $tanggal_akhir = isset($_POST['tanggal_akhir']) ? $_POST['tanggal_akhir'] : '';
    $alasan = isset($_POST['alasan_cuti']) ? $_POST['alasan_cuti'] : '';
    $jenis_cuti = $jenis_cuti_raw;

    if ($jenis_cuti_raw === 'Khusus' && !empty($_POST['jenis_cuti_khusus'])) {
        $jenis_cuti = 'Khusus - ' . $_POST['jenis_cuti_khusus'];
    }

    if (empty($kode_karyawan) || empty($jenis_cuti) || empty($tanggal_mulai) || empty($tanggal_akhir) || empty($alasan)) {
        $error_msg = "Semua field wajib diisi.";
    }

    if ($tanggal_akhir < $tanggal_mulai) {
        $error_msg = "Tanggal akhir tidak boleh lebih awal dari tanggal mulai.";
    }

    if ($jenis_cuti_raw === 'Khusus' && !empty($_POST['jenis_cuti_khusus'])) {
        $max_days = 0;
        switch($_POST['jenis_cuti_khusus']) {
            case 'Menikah':
                $max_days = 3;
                break;
            case 'Pernikahan Anak/Pembatisan Anak/Pengkhitanan Anak':
            case 'Istri Melahirkan/Keguguran':
            case 'Suami istri, anak/menantu, orangtua/mertua meninggal':
                $max_days = 2;
                break;
            case 'Anggota keluarga dalam satu rumah meninggal':
            case 'Pemeriksaan Kesehatan/Pindah Rumah':
                $max_days = 1;
                break;
        }
        
        $start_date = new DateTime($tanggal_mulai);
        $end_date = new DateTime($tanggal_akhir);
        $interval = $start_date->diff($end_date);
        $jumlah_hari = $interval->days + 1;
        
        if ($jumlah_hari > $max_days) {
            $error_msg = "Jumlah hari cuti untuk " . $_POST['jenis_cuti_khusus'] . " maksimal $max_days hari. Anda mengajukan $jumlah_hari hari.";
        }
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

    if (empty($error_msg)) {
        $status = 'Diterima';
        
        $sql = "INSERT INTO data_pengajuan_cuti 
                (kode_karyawan, nama_karyawan, divisi, jabatan, role, jenis_cuti, tanggal_mulai, tanggal_akhir, alasan, file_surat_dokter, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssssss", 
                $kode_karyawan, $nama_karyawan, $divisi, $jabatan, $role, 
                $jenis_cuti, $tanggal_mulai, $tanggal_akhir, $alasan, $file_surat_path, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $insert_success = true;
                $display_data = true;
                
                if ($jenis_cuti_raw === 'Tahunan' || $jenis_cuti_raw === 'Lustrum') {
                    updateSisaCuti($conn, $kode_karyawan, $jenis_cuti_raw, $tanggal_mulai, $tanggal_akhir);
                }
                
            } else {
                $error_msg = "Gagal execute statement: " . mysqli_error($conn);
                
                if (!empty($file_surat_path)) {
                    @unlink($file_surat_path);
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Gagal mempersiapkan statement: " . mysqli_error($conn);
            
            if (!empty($file_surat_path)) {
                @unlink($file_surat_path);
            }
        }
    }
} else {
    $error_msg = "Metode request tidak valid.";
}

function updateSisaCuti($conn, $kode_karyawan, $jenis_cuti, $tanggal_mulai, $tanggal_akhir) {
    $start_date = new DateTime($tanggal_mulai);
    $end_date = new DateTime($tanggal_akhir);
    $working_days = 0;
    $current = clone $start_date;
    
    while ($current <= $end_date) {
        $day_of_week = $current->format('w');
        if ($day_of_week != 0 && $day_of_week != 6) { 
            $working_days++;
        }
        $current->modify('+1 day');
    }
    
    if ($jenis_cuti === 'Tahunan') {
        $update_sql = "UPDATE data_karyawan SET sisa_cuti_tahunan = sisa_cuti_tahunan - ? WHERE kode_karyawan = ?";
    } else if ($jenis_cuti === 'Lustrum') {
        $update_sql = "UPDATE data_karyawan SET sisa_cuti_lustrum = sisa_cuti_lustrum - ? WHERE kode_karyawan = ?";
    } else {
        return;
    }
    
    $stmt_update = mysqli_prepare($conn, $update_sql);
    if ($stmt_update) {
        mysqli_stmt_bind_param($stmt_update, "is", $working_days, $kode_karyawan);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Status Pengajuan Cuti - Direktur</title>
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
        font-size: 18px;
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
    
    .debug-info {
        background-color: #fff3cd;
        color: #856404;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        border: 1px solid #ffeaa7;
        font-size: 12px;
        font-family: monospace;
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
            <h2>Status Pengajuan Cuti - Direktur</h2>

            <?php if ($insert_success && $display_data): ?>
                <div class="auto-approved-badge">
                    ✅ PENGAJUAN OTOMATIS DISETUJUI
                </div>
                
                <div class="message success-message">Pengajuan Cuti berhasil dikirim dan otomatis disetujui!</div>
                
                <div class="note-box">
                    <strong>Status Khusus Direktur:</strong> Sebagai Direktur, pengajuan cuti Anda <strong>otomatis disetujui</strong> dan langsung tercatat dalam sistem.
                </div>
                
                <div class="info-box">
                    <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($kode_karyawan); ?></p>
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_karyawan); ?></p>
                    <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
                    <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $role))); ?></p>
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
                    <p><strong>Status:</strong> <span class="status-approved">DISETUJUI (Auto Approved)</span></p>
                </div>

            <?php else: ?>
                <div class="message error-message">
                    <?php echo !empty($error_msg) ? htmlspecialchars($error_msg) : 'Terjadi kesalahan saat memproses pengajuan Anda.'; ?>
                </div>
                
                <?php if (isset($kode_karyawan)): ?>
                <div class="debug-info">
                    <strong>Debug Info:</strong><br>
                    Kode: <?php echo htmlspecialchars($kode_karyawan); ?><br>
                    Jenis Cuti: <?php echo htmlspecialchars($jenis_cuti); ?><br>
                    Tanggal: <?php echo htmlspecialchars($tanggal_mulai); ?> s/d <?php echo htmlspecialchars($tanggal_akhir); ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="pengajuan_cuti_direktur.php" class="btn btn-primary">Ajukan Cuti Lain</a>
                <a href="dashboarddirektur.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                <a href="riwayat_cuti_pribadi_direktur.php" class="btn btn-primary">Lihat Riwayat Cuti</a>
            </div>
        </div>
    </main>
</body>
</html>
<?php
if(isset($conn)) {
    mysqli_close($conn);
}
?>