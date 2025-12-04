<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit;
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['user']['id_karyawan'])) {
    $user_id = $_SESSION['user']['id_karyawan'];
} elseif (isset($_SESSION['user']['kode_karyawan'])) {
    $sql = "SELECT id_karyawan FROM data_karyawan WHERE kode_karyawan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['user']['kode_karyawan']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $user_id = $data['id_karyawan'] ?? null;
    $stmt->close();
} else {
    header("Location: login_karyawan.php");
    exit;
}

$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();

if (!$karyawan) {
    header("Location: login_karyawan.php");
    exit;
}

$stmt->close();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $nik = $_POST['nik'] ?? '';
    $alamat_rumah = $_POST['alamat_rumah'] ?? '';
    $alamat_domisili = $_POST['alamat_domisili'] ?? '';
    $agama = $_POST['agama'] ?? '';
    $kontak_darurat = $_POST['kontak_darurat'] ?? '';
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    
    if (!empty($no_telp) && !is_numeric($no_telp)) {
        $error_message = "Format nomor telepon tidak valid.";
    } else {
        $update_sql = "UPDATE data_karyawan SET 
                        jenis_kelamin = ?, 
                        tempat_lahir = ?, 
                        tanggal_lahir = ?, 
                        nik = ?, 
                        alamat_rumah = ?, 
                        alamat_domisili = ?, 
                        agama = ?, 
                        kontak_darurat = ?, 
                        pendidikan_terakhir = ?, 
                        no_telp = ? 
                       WHERE id_karyawan = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        
        if (empty($tanggal_lahir)) {
            $tanggal_lahir = null;
        }
        
        $update_stmt->bind_param("ssssssssssi", 
            $jenis_kelamin,
            $tempat_lahir,
            $tanggal_lahir,
            $nik,
            $alamat_rumah,
            $alamat_domisili,
            $agama,
            $kontak_darurat,
            $pendidikan_terakhir,
            $no_telp,
            $user_id
        );
        
        if ($update_stmt->execute()) {
            $success_message = "Data pribadi berhasil diupdate!";
            $karyawan['jenis_kelamin'] = $jenis_kelamin;
            $karyawan['tempat_lahir'] = $tempat_lahir;
            $karyawan['tanggal_lahir'] = $tanggal_lahir;
            $karyawan['nik'] = $nik;
            $karyawan['alamat_rumah'] = $alamat_rumah;
            $karyawan['alamat_domisili'] = $alamat_domisili;
            $karyawan['agama'] = $agama;
            $karyawan['kontak_darurat'] = $kontak_darurat;
            $karyawan['pendidikan_terakhir'] = $pendidikan_terakhir;
            $karyawan['no_telp'] = $no_telp;
            
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['no_telp'] = $no_telp;
            }
        } else {
            $error_message = "Gagal mengupdate data pribadi. Silakan coba lagi.";
        }
        $update_stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil Saya - Direktur</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        color: var(--text-color-dark);
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
        gap: 40px; 
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
        padding: 15px 0;
        border-radius: 8px; 
        box-shadow: 0 2px 10px var(--shadow-light); 
        min-width: 220px; 
        z-index: 999; 
    }
    
    nav li:hover > ul { 
        display: block; 
    }
    
    nav li ul li { 
        margin-bottom: 7px; 
        padding: 0; 
    }

    nav li ul li:last-child {
        margin-bottom: 0; 
    }
    
    nav li ul li a { 
        color: var(--text-color-dark); 
        font-weight: 400; 
        white-space: nowrap; 
        padding: 10px 25px; 
    }
    
    main {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .heading-section {
        margin-bottom: 30px;
    }
    .heading-section h1 {
        font-size: 2.5rem;
        margin: 0;
        color: #fff;
    }
    .heading-section p {
        font-size: 1.1rem;
        margin-top: 5px;
        opacity: 0.9;
        color: #fff;
    }
    
    .card {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        background: var(--card-bg);
        border-radius: 15px;
        padding: 30px 40px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        box-sizing: border-box;
    }
    
    h2 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 25px;
        color: var(--text-color-dark);
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }
    .info-item { 
        background: #f8f9fa; 
        padding: 15px; 
        border-radius: 8px; 
        border-left: 4px solid var(--primary-color); 
    }
    .info-label { 
        font-weight: 600; 
        color: #555; 
        display: block; 
        margin-bottom: 5px; 
        font-size: 14px; 
    }
    .info-value { 
        color: #333; 
        font-size: 16px; 
    }
    .edit-form { 
        background: #f0f2f5; 
        padding: 25px; 
        border-radius: 10px; 
        margin-top: 20px; 
    }
    .edit-form h3 { 
        margin-top: 0; 
        color: var(--primary-color); 
        font-size: 18px; 
        margin-bottom: 20px; 
        text-align: center;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    .form-group label { 
        display: block; 
        font-weight: 600; 
        margin-bottom: 8px; 
        color: #555; 
    }
    .form-group input, 
    .form-group select, 
    .form-group textarea { 
        width: 100%; 
        padding: 10px; 
        border: 1px solid #ccc; 
        border-radius: 6px; 
        font-size: 16px; 
        box-sizing: border-box; 
    }
    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }
    .readonly-field {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }
    .btn {
        display: inline-block; 
        padding: 10px 20px; 
        background: var(--accent-color); 
        color: #fff; 
        border: none;
        border-radius: 6px; 
        font-weight: 600; 
        font-size: 14px; 
        cursor: pointer; 
        text-decoration: none; 
        text-align: center;
    }
    .btn:hover { 
        background: #3a3162; 
    }
    .btn-back { 
        background: #6c757d; 
        margin-left: 10px; 
    }
    .btn-back:hover { 
        background: #545b62; 
    }
    .message { 
        padding: 12px; 
        border-radius: 6px; 
        margin-bottom: 20px; 
        border-left-width: 4px; 
        border-left-style: solid; 
    }
    .success-message { 
        background: var(--success-bg); 
        color: var(--success-text); 
        border-color: var(--success-border); 
    }
    .error-message { 
        background: var(--error-bg); 
        color: var(--error-text); 
        border-color: var(--error-border); 
    }
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    @media(max-width:768px) {
        header { 
            flex-direction: column; 
            padding: 15px 20px; 
            gap: 15px; 
        }
    
        nav ul { 
            flex-direction: column; 
            gap: 10px; 
            width: 100%; 
        }
    
        nav li ul { 
            position: static; 
            box-shadow: none; 
            border: 1px solid #e0e0e0; 
            padding: 5px 0; 
        }
        
        nav li ul li a {
            padding: 8px 25px;
        }
        
        .info-grid, 
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
        <img src="image/namayayasan.png" alt="Logo Yayasan">
        <span>Yayasan Purba Danarta</span>
    </div>
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
    <div class="heading-section">
        <h1>Profil Saya</h1>
        <p>Lihat dan perbarui data pribadi Anda di halaman ini.</p>
    </div>
    
    <div class="card">
        <h2>Profil Direktur</h2>
        
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
                <span class="info-label">Jenis Kelamin</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['jenis_kelamin'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tempat/Tanggal Lahir</span>
                <span class="info-value">
                    <?= htmlspecialchars($karyawan['tempat_lahir'] ?? '') ?> / 
                    <?= !empty($karyawan['tanggal_lahir']) ? date('d-m-Y', strtotime($karyawan['tanggal_lahir'])) : 'Belum diatur' ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">NIK</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['nik'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Agama</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['agama'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Pendidikan Terakhir</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['pendidikan_terakhir'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Nomor Telepon</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['no_telp'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Kontak Darurat</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['kontak_darurat'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item full-width" style="grid-column: 1 / -1;">
                <span class="info-label">Alamat Rumah</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['alamat_rumah'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item full-width" style="grid-column: 1 / -1;">
                <span class="info-label">Alamat Domisili</span>
                <span class="info-value"><?= htmlspecialchars($karyawan['alamat_domisili'] ?? 'Belum diatur') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value" style="color: <?= $karyawan['status_aktif'] == 'aktif' ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                    <?= ucfirst($karyawan['status_aktif']) ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Bergabung</span>
                <span class="info-value"><?= date('d F Y', strtotime($karyawan['created_at'])) ?></span>
            </div>
        </div>

        <div class="edit-form">
            <h3>Edit Data Pribadi</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?= ($karyawan['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($karyawan['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tempat_lahir">Tempat Lahir</label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" 
                               value="<?= htmlspecialchars($karyawan['tempat_lahir'] ?? '') ?>" 
                               placeholder="Tempat lahir">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                               value="<?= htmlspecialchars($karyawan['tanggal_lahir'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="nik">NIK</label>
                        <input type="text" id="nik" name="nik" 
                               value="<?= htmlspecialchars($karyawan['nik'] ?? '') ?>" 
                               placeholder="Nomor Induk Kependudukan">
                    </div>
                    
                    <div class="form-group">
                        <label for="agama">Agama</label>
                        <select id="agama" name="agama">
                            <option value="">Pilih Agama</option>
                            <option value="Islam" <?= ($karyawan['agama'] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                            <option value="Kristen" <?= ($karyawan['agama'] ?? '') == 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                            <option value="Katholik" <?= ($karyawan['agama'] ?? '') == 'Katholik' ? 'selected' : '' ?>>Katholik</option>
                            <option value="Hindu" <?= ($karyawan['agama'] ?? '') == 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                            <option value="Buddha" <?= ($karyawan['agama'] ?? '') == 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                            <option value="Khonghucu" <?= ($karyawan['agama'] ?? '') == 'Khonghucu' ? 'selected' : '' ?>>Khonghucu</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
                        <select id="pendidikan_terakhir" name="pendidikan_terakhir">
                            <option value="">Pilih Pendidikan</option>
                            <option value="SD" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'SD' ? 'selected' : '' ?>>SD</option>
                            <option value="SMP" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'SMP' ? 'selected' : '' ?>>SMP</option>
                            <option value="SMA" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'SMA' ? 'selected' : '' ?>>SMA</option>
                            <option value="SMK" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'SMK' ? 'selected' : '' ?>>SMK</option>
                            <option value="Diploma" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                            <option value="Sarjana" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'Sarjana' ? 'selected' : '' ?>>Sarjana</option>
                            <option value="Magister" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'Magister' ? 'selected' : '' ?>>Magister</option>
                            <option value="Doktor" <?= ($karyawan['pendidikan_terakhir'] ?? '') == 'Doktor' ? 'selected' : '' ?>>Doktor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_telp">No. Telepon</label>
                        <input type="tel" id="no_telp" name="no_telp" 
                               value="<?= htmlspecialchars($karyawan['no_telp'] ?? '') ?>" 
                               placeholder="Nomor telepon aktif" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kontak_darurat">Kontak Darurat</label>
                        <input type="text" id="kontak_darurat" name="kontak_darurat" 
                               value="<?= htmlspecialchars($karyawan['kontak_darurat'] ?? '') ?>" 
                               placeholder="Nomor kontak darurat">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="alamat_rumah">Alamat Rumah</label>
                        <textarea id="alamat_rumah" name="alamat_rumah" 
                                  placeholder="Alamat rumah lengkap"><?= htmlspecialchars($karyawan['alamat_rumah'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="alamat_domisili">Alamat Domisili</label>
                        <textarea id="alamat_domisili" name="alamat_domisili" 
                                  placeholder="Alamat domisili saat ini"><?= htmlspecialchars($karyawan['alamat_domisili'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn">Update Data Pribadi</button>
                    <a href="dashboarddirektur.php" class="btn btn-back">Kembali ke Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</main>

</body>
</html>