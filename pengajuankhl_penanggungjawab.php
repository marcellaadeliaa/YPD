<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];
$divisi = $user['divisi'];
$jabatan = $user['jabatan'];
$role = $user['role'];

$stmt_cuti = $conn->prepare("SELECT COUNT(id) as total FROM pengajuan_cuti WHERE divisi = ? AND status = 'Menunggu'");
$stmt_cuti->bind_param("s", $divisi);
$stmt_cuti->execute();
$cuti_menunggu = $stmt_cuti->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_cuti->close();

$stmt_khl = $conn->prepare("SELECT COUNT(id) as total FROM pengajuan_khl WHERE divisi = ? AND status = 'Menunggu'");
$stmt_khl->bind_param("s", $divisi);
$stmt_khl->execute();
$khl_menunggu = $stmt_khl->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_khl->close();

$stmt_karyawan = $conn->prepare("SELECT COUNT(id_karyawan) as total FROM data_karyawan WHERE divisi = ? AND status_aktif = 'aktif'");
$stmt_karyawan->bind_param("s", $divisi);
$stmt_karyawan->execute();
$total_karyawan_divisi = $stmt_karyawan->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_karyawan->close();

if (empty($divisi) || empty($jabatan)) {
    $query_karyawan = "SELECT divisi, jabatan FROM data_karyawan WHERE kode_karyawan = ?";
    $stmt = mysqli_prepare($conn, $query_karyawan);
    mysqli_stmt_bind_param($stmt, "s", $nik);
    mysqli_stmt_execute($stmt);
    $result_karyawan = mysqli_stmt_get_result($stmt);
    $karyawan_detail = mysqli_fetch_assoc($result_karyawan);
    
    if ($karyawan_detail) {
        $divisi = $karyawan_detail['divisi'];
        $jabatan = $karyawan_detail['jabatan'];
        $_SESSION['user']['divisi'] = $divisi;
        $_SESSION['user']['jabatan'] = $jabatan;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan KHL - Penanggung Jawab</title>
    <style>
        :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; color: var(--text-color-light); padding-bottom: 40px; }
        
        header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; padding: 5px 20px; }
        
        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .heading-section h1 { font-size: 2.5rem; margin: 0; color: #fff;}
        .heading-section p { font-size: 1.1rem; margin-top: 5px; opacity: 0.9; margin-bottom: 30px; color: #fff;}
        
        .form-container {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 5px 20px var(--shadow-light);
            margin-bottom: 30px;
        }
        
        h2 {
            text-align: center;
            font-size: 22px;
            color: #2e1f4f;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin: 16px 0 6px;
            color: #222;
        }
        
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }
        
        button {
            display: block;
            margin-top: 25px;
            padding: 12px;
            background-color: #4a3f81;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            width: 100%;
        }
        
        button:hover {
            background-color: #3a3162;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
        
        .user-info {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4a3f81;
        }
        
        .user-info p {
            margin: 8px 0;
            font-size: 14px;
            color: #333;
        }
        
        .user-info strong {
            color: #4a3f81;
        }

        .info-message {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            text-align: center;
        }

        .form-note {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #ffeaa7;
            font-size: 14px;
        }

        .required::after {
            content: " *";
            color: #dc3545;
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
        <div class="heading-section">
    <h1>Pengajuan Kerja Hari Libur (KHL) Pribadi</h1>
    <p>Formulir ini digunakan untuk mengajukan KHL atas nama Anda sendiri.</p>
</div>

        <div class="form-container">
            <h2>Pengajuan KHL - Penanggung Jawab</h2>
            
            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'success') {
                    $success_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Pengajuan KHL berhasil dikirim!';
                    echo '<div class="success-message">' . $success_message . '</div>';
                } elseif ($_GET['status'] == 'error') {
                    $error_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Terjadi kesalahan. Silakan coba lagi.';
                    echo '<div class="error-message">' . $error_message . '</div>';
                } elseif ($_GET['status'] == 'info') {
                    $info_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Informasi';
                    echo '<div class="info-message">' . $info_message . '</div>';
                }
            }
            ?>

            <div class="user-info">
                <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($nik); ?></p>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_lengkap); ?></p>
                <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
                <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $role))); ?></p>
            </div>

            <div class="form-note">
                <strong>Catatan:</strong> Sebagai Penanggung Jawab, pengajuan KHL Anda akan masuk dengan status <strong>Pending</strong> dan membutuhkan persetujuan dari atasan.
            </div>
            
            <form method="post" action="proseskhl_penanggungjawab.php">

                <label>No. Kode Karyawan</label>
                <input type="text" name="nik" value="<?php echo htmlspecialchars($nik); ?>" readonly>

                <label>Nama Karyawan</label>
                <input type="text" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>

                <label>Divisi</label>
                <input type="text" value="<?php echo htmlspecialchars($divisi); ?>" readonly>

                <label class="required">Proyek</label>
                <input type="text" name="proyek" placeholder="Masukkan nama proyek" required>

                <label class="required">Tanggal KHL</label>
                <input type="date" name="tanggal_khl" required>

                <label class="required">Jam Mulai Kerja</label>
                <select name="jam_mulai_kerja" required>
                    <option value="">Pilih Jam Mulai Kerja</option>
                    <option value="08:00">08:00</option>
                    <option value="09:00">09:00</option>
                    <option value="10:00">10:00</option>
                </select>

                <label class="required">Jam Akhir Kerja</label>
                <select name="jam_akhir_kerja" required>
                    <option value="">Pilih Jam Akhir Kerja</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                    <option value="18:00">18:00</option>
                </select>

                <label class="required">Tanggal Cuti KHL</label>
                <input type="date" name="tanggal_cuti_khl" required>

                <label class="required">Jam Mulai Cuti KHL</label>
                <select name="jam_mulai_cuti_khl" required>
                    <option value="">Pilih Jam Mulai Cuti</option>
                    <option value="08:00">08:00</option>
                    <option value="09:00">09:00</option>
                </select>

                <label class="required">Jam Akhir Cuti KHL</label>
                <select name="jam_akhir_cuti_khl" required>
                    <option value="">Pilih Jam Akhir Cuti</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                </select>

                <button type="submit">Masukkan</button>
            </form>
        </div>
    </main>

    <?php
    mysqli_close($conn);
    ?>
</body>
</html>