<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: formkhlkaryawan.php?status=error&message=Metode request tidak valid");
    exit();
}

$nik = $_POST['nik'];
$proyek = $_POST['proyek'];
$tanggal_khl = $_POST['tanggal_khl'];
$jam_mulai_kerja = $_POST['jam_mulai_kerja'];
$jam_akhir_kerja = $_POST['jam_akhir_kerja'];
$tanggal_cuti_khl = $_POST['tanggal_cuti_khl'];
$jam_mulai_cuti_khl = $_POST['jam_mulai_cuti_khl'];
$jam_akhir_cuti_khl = $_POST['jam_akhir_cuti_khl'];

if (empty($nik) || empty($proyek) || empty($tanggal_khl) || empty($jam_mulai_kerja) || 
    empty($jam_akhir_kerja) || empty($tanggal_cuti_khl) || empty($jam_mulai_cuti_khl) || 
    empty($jam_akhir_cuti_khl)) {
    header("Location: formkhlkaryawan.php?status=error&message=Semua field harus diisi");
    exit();
}

$query_karyawan = "SELECT nama_lengkap, divisi, jabatan, role FROM data_karyawan WHERE kode_karyawan = ?";
$stmt_karyawan = mysqli_prepare($conn, $query_karyawan);
mysqli_stmt_bind_param($stmt_karyawan, "s", $nik);
mysqli_stmt_execute($stmt_karyawan);
$result_karyawan = mysqli_stmt_get_result($stmt_karyawan);
$karyawan = mysqli_fetch_assoc($result_karyawan);

if (!$karyawan) {
    header("Location: formkhlkaryawan.php?status=error&message=Data karyawan tidak ditemukan");
    exit();
}

$nama_lengkap = $karyawan['nama_lengkap'];
$divisi = $karyawan['divisi'];
$jabatan = $karyawan['jabatan'];
$role = $karyawan['role'];

mysqli_stmt_close($stmt_karyawan);

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'data_pengajuan_khl'");
if (mysqli_num_rows($check_table) == 0) {
    $create_table = "CREATE TABLE `data_pengajuan_khl` (
        `id_khl` int(11) NOT NULL AUTO_INCREMENT,
        `kode_karyawan` varchar(20) NOT NULL,
        `divisi` varchar(50) NOT NULL,
        `jabatan` varchar(50) NOT NULL,
        `role` enum('karyawan','direktur','admin','penanggung jawab') NOT NULL DEFAULT 'karyawan',
        `proyek` varchar(100) NOT NULL,
        `tanggal_khl` date NOT NULL,
        `jam_mulai_kerja` time NOT NULL,
        `jam_akhir_kerja` time NOT NULL,
        `tanggal_cuti_khl` date NOT NULL,
        `jam_mulai_cuti_khl` time NOT NULL,
        `jam_akhir_cuti_khl` time NOT NULL,
        `status_khl` enum('pending','disetujui','ditolak') DEFAULT 'pending',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_khl`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!mysqli_query($conn, $create_table)) {
        $error_message = "Gagal membuat tabel database";
        $status = "error";
    }
} else {
    $columns_to_check = [
        'divisi' => "ALTER TABLE data_pengajuan_khl ADD COLUMN divisi VARCHAR(50) NOT NULL AFTER kode_karyawan",
        'jabatan' => "ALTER TABLE data_pengajuan_khl ADD COLUMN jabatan VARCHAR(50) NOT NULL AFTER divisi",
        'role' => "ALTER TABLE data_pengajuan_khl ADD COLUMN role ENUM('karyawan','direktur','admin','penanggung jawab') NOT NULL DEFAULT 'karyawan' AFTER jabatan",
        'proyek' => "ALTER TABLE data_pengajuan_khl ADD COLUMN proyek VARCHAR(100) NOT NULL AFTER role"
    ];
    
    foreach ($columns_to_check as $column_name => $alter_query) {
        $check_column = mysqli_query($conn, "SHOW COLUMNS FROM data_pengajuan_khl LIKE '$column_name'");
        if (mysqli_num_rows($check_column) == 0) {
            if (!mysqli_query($conn, $alter_query)) {
                $error_message = "Gagal menambahkan kolom $column_name";
                $status = "error";
                break;
            }
        }
    }
}

if (!isset($status)) {
    $sql = "INSERT INTO data_pengajuan_khl 
            (kode_karyawan, divisi, jabatan, role, proyek, tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, 
             tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl, status_khl) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssssss", 
            $nik, $divisi, $jabatan, $role, $proyek, $tanggal_khl, $jam_mulai_kerja, $jam_akhir_kerja,
            $tanggal_cuti_khl, $jam_mulai_cuti_khl, $jam_akhir_cuti_khl);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Pengajuan KHL berhasil dikirim!";
            $status = "success";
        } else {
            $error_message = "Gagal menyimpan data: " . mysqli_error($conn);
            $status = "error";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Gagal mempersiapkan statement: " . mysqli_error($conn);
        $status = "error";
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Proses Pengajuan KHL</title>
<style>
  body {
    margin:0;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
    min-height:100vh;
    display:flex;
    flex-direction:column;
  }
  header {
    background:#fff;
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:2px solid #34377c;
  }
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

  main {
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
  }
  .form-container {
    width:100%;
    max-width:500px;
    background:rgba(255,255,255,0.95);
    border-radius:15px;
    padding:30px 40px;
    box-shadow:0 0 15px rgba(0,0,0,0.2);
  }
  h2 {
    text-align:center;
    font-size:22px;
    color:#2e1f4f;
    margin-bottom:20px;
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
  <div class="form-container">
    <h2>Status Pengajuan KHL</h2>
    
    <!-- Tampilkan pesan sukses/error -->
    <?php if (isset($status)): ?>
        <?php if ($status == 'success'): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php elseif ($status == 'error'): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Info Pengguna -->
    <div class="user-info">
      <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($nik); ?></p>
      <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_lengkap); ?></p>
      <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
      <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
      <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $role))); ?></p>
    </div>

    <!-- Detail Pengajuan KHL -->
    <div class="user-info">
      <h3 style="margin-top: 0; color: #4a3f81;">Detail Pengajuan KHL</h3>
      <p><strong>Proyek:</strong> <?php echo htmlspecialchars($proyek); ?></p>
      <p><strong>Tanggal KHL:</strong> <?php echo htmlspecialchars($tanggal_khl); ?></p>
      <p><strong>Jam Mulai Kerja:</strong> <?php echo htmlspecialchars($jam_mulai_kerja); ?></p>
      <p><strong>Jam Akhir Kerja:</strong> <?php echo htmlspecialchars($jam_akhir_kerja); ?></p>
      <p><strong>Tanggal Cuti KHL:</strong> <?php echo htmlspecialchars($tanggal_cuti_khl); ?></p>
      <p><strong>Jam Mulai Cuti KHL:</strong> <?php echo htmlspecialchars($jam_mulai_cuti_khl); ?></p>
      <p><strong>Jam Akhir Cuti KHL:</strong> <?php echo htmlspecialchars($jam_akhir_cuti_khl); ?></p>
      <p><strong>Status:</strong> <span style="color: #ffa500; font-weight: bold;">Pending</span></p>
    </div>

    <!-- Tombol Aksi -->
    <div class="action-buttons">
      <a href="formkhlkaryawan.php" class="btn btn-primary">Ajukan KHL Lain</a>
      <a href="dashboardkaryawan.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
  </div>
</main>
</body>
</html>