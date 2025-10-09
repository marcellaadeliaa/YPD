<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit;
}

// Ambil data dari session
$user = $_SESSION['user'];
$user_id = $user['id_karyawan'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];

// Ambil data karyawan dari database
$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = mysqli_prepare($conn, $sql);

// Cek jika prepared statement gagal
if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$karyawan = $result->fetch_assoc();

// Jika data tidak ditemukan, hapus session dan redirect ke login
if (!$karyawan) {
    session_destroy();
    header("Location: login_karyawan.php");
    exit;
}

mysqli_stmt_close($stmt);

// Ambil data dari database
$nama = $karyawan['nama_lengkap'];
$sisa_cuti_tahunan = $karyawan['sisa_cuti_tahunan'];
$sisa_cuti_lustrum = $karyawan['sisa_cuti_lustrum'];
$kode_karyawan = $karyawan['kode_karyawan']; // Ambil kode_karyawan

// Set nilai default untuk cuti dan KHL
$tanggal_cuti = "-";
$jenis_cuti = "-";
$status_cuti = null;
$tanggal_khl = "-";
$jenis_khl = "-";
$status_khl = null;

// --- MODIFIKASI DI SINI ---
// Query untuk mendapatkan pengajuan cuti terakhir dari tabel 'data_pengajuan_cuti'
$sql_cuti = "SELECT * FROM data_pengajuan_cuti WHERE kode_karyawan = ? ORDER BY created_at DESC LIMIT 1";
$stmt_cuti = mysqli_prepare($conn, $sql_cuti);

if ($stmt_cuti) {
    mysqli_stmt_bind_param($stmt_cuti, "s", $kode_karyawan); // Gunakan kode_karyawan
    mysqli_stmt_execute($stmt_cuti);
    $result_cuti = mysqli_stmt_get_result($stmt_cuti);
    if ($cuti = $result_cuti->fetch_assoc()) {
        // Menampilkan rentang tanggal jika tanggal akhir berbeda, atau satu tanggal jika sama
        if ($cuti['tanggal_mulai'] == $cuti['tanggal_akhir']) {
            $tanggal_cuti = date('d/m/Y', strtotime($cuti['tanggal_mulai']));
        } else {
            $tanggal_cuti = date('d/m/Y', strtotime($cuti['tanggal_mulai'])) . " - " . date('d/m/Y', strtotime($cuti['tanggal_akhir']));
        }
        $jenis_cuti = $cuti['jenis_cuti'];
        $status_cuti = $cuti['status'];
    }
    mysqli_stmt_close($stmt_cuti);
}
// --- AKHIR MODIFIKASI ---


// Query untuk mendapatkan pengajuan KHL terakhir
$sql_khl = "SELECT * FROM data_pengajuan_khl WHERE kode_karyawan = ? ORDER BY created_at DESC LIMIT 1";
$stmt_khl = mysqli_prepare($conn, $sql_khl);

if ($stmt_khl) {
    mysqli_stmt_bind_param($stmt_khl, "s", $kode_karyawan);
    mysqli_stmt_execute($stmt_khl);
    $result_khl = mysqli_stmt_get_result($stmt_khl);
    if ($khl = $result_khl->fetch_assoc()) {
        $tanggal_khl = date('d/m/Y', strtotime($khl['tanggal_khl']));
        $jenis_khl = $khl['proyek'];
        $status_khl = $khl['status_khl'];
    }
    mysqli_stmt_close($stmt_khl);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Karyawan</title>
<style>
/* === GLOBAL === */
body {
  margin:0;
  font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
  min-height:100vh;
  color:#fff;
}

header {
  background:rgba(255,255,255,1);
  padding:20px 40px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:2px solid #34377c;
  backdrop-filter:blur(5px);
  flex-wrap:wrap;
}
.logo {
  display:flex;
  align-items:center;
  gap:16px;
  font-weight:500;
  font-size:20px;
  color:#2e1f4f;
}
.logo img {
  width:140px;
  height:50px;
  object-fit:contain;
}

nav ul {
  list-style:none;
  margin:0;
  padding:0;
  display:flex;
  gap:30px;
}
nav li {
  position:relative;
}
nav a {
  text-decoration:none;
  color:#333;
  font-weight:600;
  padding:8px 4px;
  display:block;
}

/* ===== DROPDOWN ===== */
nav li ul {
  display:none;
  position:absolute;
  top:100%;
  left:0;
  background:#fff;
  padding:10px 0;
  border-radius:8px;
  box-shadow:0 2px 8px rgba(0,0,0,.15);
  min-width:150px;
  z-index:999;
}
nav li:hover ul {display:block;}
nav li ul li {padding:5px 20px;}
nav li ul li a {
  color:#333;
  font-weight:400;
  white-space:nowrap;
}

main {
  max-width:1000px;
  margin:40px auto;
  padding:0 20px;
}
h1 {text-align:center;font-size:26px;margin-bottom:30px;}
.card {
  background:#fff;
  color:#2e1f4f;
  border-radius:20px;
  padding:30px 40px;
  margin-bottom:40px;
  box-shadow:0 2px 10px rgba(0,0,0,0.15);
}
.card h3 {margin-top:0;font-size:20px;margin-bottom:15px;}
.btn {
  display:inline-block;
  background:#4a3f81;
  color:#fff;
  padding:8px 16px;
  border-radius:8px;
  text-decoration:none;
  font-weight:600;
  font-size:14px;
}
.status-approve, .status-Diterima {
  display:inline-block;
  background:#28a745;
  color:#fff;
  padding:6px 12px;
  border-radius:6px;
  font-weight:600;
  font-size:14px;
}
.status-pending, .status-Menunggu-Persetujuan {
  display:inline-block;
  background:#f0ad4e;
  color:#fff;
  padding:6px 12px;
  border-radius:6px;
  font-weight:600;
  font-size:14px;
}
.status-rejected, .status-Ditolak {
  display:inline-block;
  background:#dc3545;
  color:#fff;
  padding:6px 12px;
  border-radius:6px;
  font-weight:600;
  font-size:14px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-top: 20px;
}

.info-item {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #1E105E;
}

.info-item strong {
  color: #1E105E;
}

@media(max-width:768px){
  header{flex-direction:column;align-items:flex-start;}
  nav ul{flex-direction:column;gap:10px;}
  nav li ul {
    position:relative;
    border:none;
    box-shadow:none;
  }
  .info-grid {
    grid-template-columns: 1fr;
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
  <h1>Welcome, <?= htmlspecialchars($nama) ?>!</h1>

  <div class="card">
    <h3>Informasi Pribadi</h3>
    <div class="info-grid">
      <div class="info-item">
        <strong>Kode Karyawan:</strong><br>
        <?= htmlspecialchars($karyawan['kode_karyawan']) ?>
      </div>
      <div class="info-item">
        <strong>Nama:</strong><br>
        <?= htmlspecialchars($karyawan['nama_lengkap']) ?>
      </div>
      <div class="info-item">
        <strong>Jabatan:</strong><br>
        <?= htmlspecialchars($karyawan['jabatan']) ?>
      </div>
      <div class="info-item">
        <strong>Divisi:</strong><br>
        <?= htmlspecialchars($karyawan['divisi']) ?>
      </div>
      <div class="info-item">
        <strong>Role:</strong><br>
        <?= htmlspecialchars($karyawan['role']) ?>
      </div>
      <div class="info-item">
        <strong>Email:</strong><br>
        <?= htmlspecialchars($karyawan['email']) ?>
      </div>
    </div>
    <a href="data_pribadi.php" class="btn" style="margin-top: 15px;">Lihat Detail Lengkap</a>
  </div>

  <div class="card">
    <h3>Sisa Cuti</h3>
    <div class="info-grid">
      <div class="info-item">
        <strong>Cuti Tahunan:</strong><br>
        <?= $sisa_cuti_tahunan ?> hari
      </div>
      <div class="info-item">
        <strong>Cuti Lustrum:</strong><br>
        <?= $sisa_cuti_lustrum ?> hari
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Status Pengajuan Cuti Terakhir</h3>
    <div class="info-grid">
      <div class="info-item">
        <strong>Tanggal Cuti:</strong><br>
        <?= htmlspecialchars($tanggal_cuti) ?>
      </div>
      <div class="info-item">
        <strong>Jenis Cuti:</strong><br>
        <?= htmlspecialchars($jenis_cuti) ?>
      </div>
    </div>
    
    <?php if ($status_cuti): ?>
      <div style="margin-top: 15px;">
        <?php
            // Mengganti spasi dengan strip agar cocok dengan nama class di CSS
            $status_class = str_replace(' ', '-', $status_cuti);
        ?>
        <span class="status-<?= htmlspecialchars($status_class) ?>">
          <?= htmlspecialchars($status_cuti) ?>
        </span>
      </div>
    <?php else: ?>
      <p style="margin-top: 15px; color: #666;">Belum ada pengajuan cuti</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>Status Pengajuan KHL Terakhir</h3>
    <div class="info-grid">
      <div class="info-item">
        <strong>Tanggal KHL:</strong><br>
        <?= htmlspecialchars($tanggal_khl) ?>
      </div>
      <div class="info-item">
        <strong>Proyek:</strong><br>
        <?= htmlspecialchars($jenis_khl) ?>
      </div>
    </div>

    <?php if ($status_khl): ?>
      <div style="margin-top: 15px;">
        <span class="
          <?= $status_khl == 'disetujui' ? 'status-approve' : 
             ($status_khl == 'ditolak' ? 'status-rejected' : 'status-pending') ?>
        ">
          <?= htmlspecialchars(ucfirst($status_khl)) ?>
        </span>
      </div>
    <?php else: ?>
      <p style="margin-top: 15px; color: #666;">Belum ada pengajuan KHL</p>
    <?php endif; ?>
  </div>

</main>

</body>
</html>