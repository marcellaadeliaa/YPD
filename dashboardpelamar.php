<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = 'Pengguna';

$queryUser = $conn->prepare("SELECT nama_lengkap, id FROM data_pelamar WHERE user_id = ?");
if ($queryUser) {
    $queryUser->bind_param("i", $user_id);
    $queryUser->execute();
    $resultUser = $queryUser->get_result();
    if ($resultUser && $resultUser->num_rows > 0) {
        $userData = $resultUser->fetch_assoc();
        $nama_user = $userData['nama_lengkap'] ?? 'Pengguna';
        $pelamar_id = $userData['id'] ?? null;
    }
} else {
    error_log("Error prepare: " . $conn->error);
}

$queryCheck = $conn->prepare("SELECT COUNT(*) as count FROM data_pelamar WHERE user_id = ?");
$queryCheck->bind_param("i", $user_id);
$queryCheck->execute();
$resultCheck = $queryCheck->get_result();
$rowCheck = $resultCheck->fetch_assoc();
$hasData = $rowCheck['count'] > 0;

if (!$hasData) {
    header("Location: formpelamar.php");
    exit;
}

$queryStatus = $conn->prepare("SELECT status FROM data_pelamar WHERE user_id = ?");
$queryStatus->bind_param("i", $user_id);
$queryStatus->execute();
$resultStatus = $queryStatus->get_result();
$rowStatus = $resultStatus->fetch_assoc();
$status = $rowStatus['status'] ?? 'Menunggu Proses';

$pengumumanPelamar = null;
if (isset($pelamar_id)) {
    $queryPengumumanPelamar = $conn->prepare("
        SELECT p.pesan, p.tanggal, p.tahap 
        FROM pengumuman_pelamar p 
        WHERE p.pelamar_id = ? 
        ORDER BY p.id DESC 
        LIMIT 1
    ");
    $queryPengumumanPelamar->bind_param("i", $pelamar_id);
    $queryPengumumanPelamar->execute();
    $resultPengumuman = $queryPengumumanPelamar->get_result();
    if ($resultPengumuman && $resultPengumuman->num_rows > 0) {
        $pengumumanPelamar = $resultPengumuman->fetch_assoc();
    }
}


$show_pengumuman = false;
$judul_pengumuman = '';
$isi_pengumuman = '';
$tanggal_pengumuman = '';

if ($status == 'Tidak Lolos') {
    $judul_pengumuman = "Hasil Seleksi";
    $isi_pengumuman = "Terima kasih telah berpartisipasi dalam proses seleksi di Yayasan Purba Danarta. Setelah melalui tahap penilaian yang teliti, kami menyampaikan bahwa Anda belum dapat melanjutkan ke tahap selanjutnya pada kesempatan ini.\n\nKami sangat menghargai waktu dan usaha yang telah Anda berikan. Jangan berkecil hati, karena setiap proses adalah pembelajaran berharga untuk kesempatan di masa depan.\n\nKami berharap Anda terus bersemangat dalam mengembangkan potensi diri dan mencari kesempatan lainnya yang sesuai dengan kemampuan Anda.";
    $tanggal_pengumuman = date('d/m/Y');
    $show_pengumuman = true;
} elseif ($pengumumanPelamar) {
    $judul_pengumuman = "Update Status - " . ($pengumumanPelamar['tahap'] ?? '');
    $isi_pengumuman = $pengumumanPelamar['pesan'] ?? '';
    $tanggal_pengumuman = !empty($pengumumanPelamar['tanggal']) ? date('d/m/Y', strtotime($pengumumanPelamar['tanggal'])) : '';
    $show_pengumuman = true;
} else {
    $show_pengumuman = false;
    $judul_pengumuman = 'Tidak ada pengumuman';
    $isi_pengumuman = 'Belum ada pengumuman terbaru untuk Anda saat ini.';
    $tanggal_pengumuman = '';
}

$status_class = 'status-pending';
if ($status == 'Diterima') {
    $status_class = 'status-approve';
} elseif ($status == 'Tidak Lolos') {
    $status_class = 'status-reject';
} else {
    $status_class = 'status-pending';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Pelamar</title>
  <style>
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
      transition: color 0.3s ease;
    }
    nav a:hover {
      color:#4a3f81;
    }

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
    nav li ul li a:hover {
      color:#4a3f81;
      background:#f8f9fa;
    }

    main {
      max-width:1000px;
      margin:40px auto;
      padding:0 20px;
    }
    h1 {
      text-align:center;
      font-size:26px;
      margin-bottom:30px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .welcome-section {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .welcome-section h2 {
      font-size: 24px;
      margin-bottom: 10px;
      color: #fff;
    }
    
    .welcome-section p {
      font-size: 16px;
      opacity: 0.9;
    }

    .card {
      background:#fff;
      color:#2e1f4f;
      border-radius:20px;
      padding:30px 40px;
      margin-bottom:40px;
      box-shadow:0 2px 10px rgba(0,0,0,0.15);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .card h3 {
      margin-top:0;
      font-size:20px;
      margin-bottom:15px;
      color: #4a3f81;
      border-bottom: 2px solid #e9ecef;
      padding-bottom: 10px;
    }
    
    .card-content {
      line-height: 1.6;
    }
    
    .status-info {
      display: flex;
      align-items: center;
      gap: 15px;
      margin: 15px 0;
    }
    
    .pengumuman-date {
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 15px;
      font-style: italic;
    }
    
    .btn {
      display:inline-block;
      background:#4a3f81;
      color:#fff;
      padding:12px 24px;
      border-radius:8px;
      text-decoration:none;
      font-weight:600;
      font-size:14px;
      transition: background 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .btn:hover {
      background:#3a3162;
      color:#fff;
      text-decoration: none;
    }
    
    .status-approve {
      display:inline-block;
      background:#28a745;
      color:#fff;
      padding:8px 16px;
      border-radius:6px;
      font-weight:600;
      font-size:14px;
    }
    
    .status-pending {
      display:inline-block;
      background:#f0ad4e;
      color:#fff;
      padding:8px 16px;
      border-radius:6px;
      font-weight:600;
      font-size:14px;
    }
    
    .status-reject {
      display:inline-block;
      background:#dc3545;
      color:#fff;
      padding:8px 16px;
      border-radius:6px;
      font-weight:600;
      font-size:14px;
    }
    
    .action-buttons {
      display: flex;
      gap: 15px;
      margin-top: 25px;
      flex-wrap: wrap;
    }
    
    .btn-secondary {
      background: #6c757d;
    }
    
    .btn-secondary:hover {
      background: #545b62;
    }

    .selamat-box {
      background: #d4edda;
      color: #155724;
      padding: 20px;
      border-radius: 8px;
      margin: 20px 0;
      border-left: 4px solid #28a745;
    }
    
    .selamat-box h4 {
      margin: 0 0 15px 0;
      color: #155724;
      font-size: 18px;
    }
    
    .selamat-box p {
      margin: 10px 0;
      font-weight: 500;
    }
    
    .link-karyawan {
      display: inline-block;
      background: #28a745;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 600;
      margin-top: 10px;
    }
    
    .link-karyawan:hover {
      background: #218838;
      color: white;
      text-decoration: none;
    }
    
    .no-pengumuman {
      color: #6c757d;
      font-style: italic;
      text-align: center;
      padding: 20px 0;
    }
    
    .pesan-tidak-lolos {
      background: #f8d7da;
      color: #721c24;
      padding: 20px;
      border-radius: 8px;
      margin: 20px 0;
      border-left: 4px solid #dc3545;
    }
    
    .pesan-tidak-lolos h4 {
      margin: 0 0 15px 0;
      color: #721c24;
      font-size: 18px;
    }
    
    .pesan-tidak-lolos p {
      margin: 10px 0;
      font-weight: 500;
    }

    @media(max-width:768px){
      header{
        flex-direction:column;
        align-items:flex-start;
        padding: 15px 20px;
      }
      
      nav ul{
        flex-direction:column;
        gap:10px;
        margin-top: 15px;
      }
      
      nav li ul {
        position:relative;
        border:none;
        box-shadow:none;
        margin-left: 15px;
      }
      
      .card {
        padding: 20px;
        margin-bottom: 30px;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .btn {
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="image/namayayasan.png" alt="Logo Yayasan Purba Danarta">
      <span>Yayasan Purba Danarta</span>
    </div>
    <nav>
      <ul>
        <li><a href="dashboardpelamar.php">Beranda</a></li>
        <li>
          <a href="#">Profil ▼</a>
          <ul>
            <li><a href="lihat_lamaran.php">Lihat Lamaran</a></li>
          </ul>
        </li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <div class="welcome-section">
      <h2>Selamat Datang, <?= htmlspecialchars($nama_user) ?>!</h2>
      <p>Pantau status lamaran dan pengumuman terbaru di sini</p>
    </div>

    <div class="card">
      <h3>Status Lamaran Anda</h3>
      <div class="card-content">
        <div class="status-info">
          <strong>Status:</strong>
          <span class="<?= $status_class ?>"><?= htmlspecialchars($status) ?></span>
        </div>
        
        <?php if ($status == 'Diterima'): ?>
          <div class="selamat-box">
            <h4>🎉 Selamat!</h4>
            <p><strong>Anda dinyatakan lolos sebagai karyawan Yayasan Purba Danarta.</strong></p>
            <p>Kami akan segera menghubungi Anda untuk proses selanjutnya. Silakan klik link di bawah ini untuk mengakses dashboard karyawan:</p>
            <a href="dashboardlogin.php" class="link-karyawan">Akses Dashboard Karyawan, pilih Login sebagai Karyawan</a>
          </div>
        <?php elseif ($status == 'Tidak Lolos'): ?>
          <div class="pesan-tidak-lolos">
            <h4>📝 Informasi Hasil Seleksi</h4>
            <p><strong>Terima kasih telah berpartisipasi dalam proses seleksi.</strong></p>
            <p>Setelah melalui tahap penilaian yang teliti, kami menyampaikan bahwa Anda belum dapat melanjutkan ke tahap selanjutnya pada kesempatan ini.</p>
            <p>Kami sangat menghargai waktu dan usaha yang telah Anda berikan. Jangan berkecil hati, karena setiap proses adalah pembelajaran berharga untuk kesempatan di masa depan.</p>
          </div>
        <?php else: ?>
          <p>Status lamaran Anda akan diperbarui secara berkala. Silakan pantau halaman ini untuk informasi terbaru.</p>
        <?php endif; ?>
        </div>
    </div>

    <div class="card">
      <h3>Pengumuman Terbaru</h3>
      <div class="card-content">
        <?php if ($show_pengumuman): ?>
          <?php if ($tanggal_pengumuman): ?>
            <div class="pengumuman-date">Tanggal: <?= htmlspecialchars($tanggal_pengumuman) ?></div>
          <?php endif; ?>
          <h4 style="color: #4a3f81; margin-bottom: 15px;"><?= htmlspecialchars($judul_pengumuman) ?></h4>
          <p style="white-space: pre-line;"><?= htmlspecialchars($isi_pengumuman) ?></p>
        <?php else: ?>
          <div class="no-pengumuman">
            Belum ada pengumuman terbaru untuk Anda saat ini.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="action-buttons">
      <a href="edit_lamaran.php" class="btn">Edit Data Lamaran</a>
      <a href="lihat_lamaran.php" class="btn btn-secondary">Lihat Detail Lamaran</a>
    </div>
  </main>
</body>
</html>