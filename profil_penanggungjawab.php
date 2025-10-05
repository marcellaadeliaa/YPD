<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan perannya adalah penanggung jawab
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit;
}

// Ambil id_karyawan dari session
$user_id = $_SESSION['user']['id_karyawan'];

// Ambil data karyawan dari database
$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();
$stmt->close();

// Jika data tidak ditemukan, redirect ke login
if (!$karyawan) {
    header("Location: login_karyawan.php");
    exit;
}

// Proses update no telepon
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_telepon'])) {
    $no_telp_baru = $_POST['no_telp'] ?? '';
    
    // Validasi sederhana: pastikan tidak kosong dan numerik
    if (!empty($no_telp_baru) && is_numeric($no_telp_baru)) {
        $update_sql = "UPDATE data_karyawan SET no_telp = ? WHERE id_karyawan = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $no_telp_baru, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Nomor telepon berhasil diupdate!";
            // Update data di variabel lokal untuk ditampilkan langsung
            $karyawan['no_telp'] = $no_telp_baru;
            // Update juga data di session jika perlu
            $_SESSION['user']['no_telp'] = $no_telp_baru;
        } else {
            $error_message = "Gagal mengupdate nomor telepon. Silakan coba lagi.";
        }
        $update_stmt->close();
    } else {
        $error_message = "Format nomor telepon tidak valid.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Pribadi Penanggung Jawab</title>
<style>
    :root { 
        --primary-color: #1E105E; 
        --secondary-color: #8897AE; 
        --accent-color: #4a3f81; 
        --card-bg: #FFFFFF; 
        --text-color-light: #fff; 
        --text-color-dark: #2e1f4f; 
        --shadow-light: rgba(0,0,0,0.15); 
        --success-bg: #d4edda;
        --success-text: #155724;
        --success-border: #28a745;
        --error-bg: #f8d7da;
        --error-text: #721c24;
        --error-border: #dc3545;
    }
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        color: var(--text-color-dark);
    }
    header {
        background: var(--card-bg);
        padding: 20px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px var(--shadow-light);
        flex-wrap: wrap;
    }
    .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
    nav li { position: relative; }
    nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; transition: color 0.3s ease; }
    nav a:hover { color: var(--accent-color); }
    nav li ul {
        display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0;
        border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999;
    }
    nav li:hover > ul { display: block; }
    nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; padding: 5px 20px; }
    main {
        flex: 1; display: flex; justify-content: center; align-items: flex-start; padding: 40px 20px;
    }
    .card {
        width: 100%; max-width: 600px; background: var(--card-bg); border-radius: 15px;
        padding: 30px 40px; box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    h2 {
        text-align: center; font-size: 24px; margin-bottom: 25px; color: var(--text-color-dark);
        border-bottom: 2px solid #eee; padding-bottom: 10px;
    }
    .info-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;
    }
    .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary-color); }
    .info-label { font-weight: 600; color: #555; display: block; margin-bottom: 5px; font-size: 14px; }
    .info-value { color: #333; font-size: 16px; }
    .edit-form { background: #f0f2f5; padding: 20px; border-radius: 10px; margin-top: 20px; }
    .edit-form h3 { margin-top: 0; color: var(--primary-color); font-size: 18px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #555; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
    .btn {
        display: inline-block; padding: 10px 20px; background: var(--accent-color); color: #fff; border: none;
        border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; text-decoration: none; text-align: center;
    }
    .btn:hover { background: #3a3162; }
    .btn-back { background: #6c757d; margin-left: 10px; }
    .btn-back:hover { background: #545b62; }
    .message { padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left-width: 4px; border-left-style: solid; }
    .success-message { background: var(--success-bg); color: var(--success-text); border-color: var(--success-border); }
    .error-message { background: var(--error-bg); color: var(--error-text); border-color: var(--error-border); }
    @media(max-width:768px) {
        header { flex-direction: column; align-items: flex-start; }
        nav ul { flex-direction: column; gap: 10px; width: 100%; margin-top: 15px; }
        nav li ul { position: static; border: none; box-shadow: none; padding-left: 20px; }
        .info-grid { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboard_penanggungjawab.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Divisi</a></li>
                    <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Divisi</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <div class="card">
        <h2>Data Pribadi</h2>
        
        <?php if ($success_message): ?>
            <div class="message success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Kode Karyawan</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['kode_karyawan']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Nama Lengkap</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['nama_lengkap']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Jabatan</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['jabatan']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Divisi</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['divisi']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Role</span>
                <span class="info-value"><?= htmlspecialchars(ucfirst($karyawan['role'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value" style="color: <?= $karyawan['status_aktif'] == 'aktif' ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                    <?= ucfirst($karyawan['status_aktif']) ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Bergabung</span>
                <span class="info-value"><?= htmlspecialchars(date('d F Y', strtotime($karyawan['created_at']))) ?></span>
            </div>
        </div>

        <div class="edit-form">
            <h3>Edit Nomor Telepon</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="no_telp">Nomor Telepon</label>
                    <input type="tel" id="no_telp" name="no_telp" 
                           value="<?= htmlspecialchars($karyawan['no_telp']) ?>" 
                           placeholder="Masukkan nomor telepon baru" required>
                </div>
                <button type="submit" name="update_telepon" class="btn">Update Nomor Telepon</button>
                <a href="dashboard_penanggungjawab.php" class="btn btn-back">Kembali</a>
            </form>
        </div>
    </div>
</main>

</body>
</html>