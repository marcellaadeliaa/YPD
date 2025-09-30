<?php
session_start();
require 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data lamaran
$query = $conn->prepare("SELECT 
    dp.*, 
    l.status as status_lamaran,
    l.created_at as tanggal_lamaran
    FROM data_pelamar dp 
    LEFT JOIN lamaran l ON dp.user_id = l.user_id 
    WHERE dp.user_id = ? 
    ORDER BY l.id DESC 
    LIMIT 1");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$lamaran = $result->fetch_assoc();

if (!$lamaran) {
    header("Location: formpelamar.php");
    exit;
}

// Format tanggal
$tanggal_lahir = $lamaran['tanggal_lahir'] ? date('d/m/Y', strtotime($lamaran['tanggal_lahir'])) : '';
$tanggal_lamaran = $lamaran['tanggal_lamaran'] ? date('d/m/Y H:i', strtotime($lamaran['tanggal_lamaran'])) : '';

// Tentukan class status
$status_class = 'status-pending';
if ($lamaran['status_lamaran'] == 'Diterima') {
    $status_class = 'status-approve';
} elseif ($lamaran['status_lamaran'] == 'Ditolak') {
    $status_class = 'status-reject';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Lamaran - Yayasan Purba Danarta</title>
  <style>
    body {
      margin:0;
      font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
      min-height:100vh;
      color:#fff;
    }

    /* ===== HEADER & NAV ===== */
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
      width:130px;
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
    nav li ul li a:hover {
      color:#4a3f81;
      background:#f8f9fa;
    }

    /* ===== MAIN CONTENT ===== */
    main {
      max-width:1000px;
      margin:40px auto;
      padding:0 20px;
    }
    
    .page-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .page-header h1 {
      font-size: 26px;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .page-header p {
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
    }
    
    .card h2 {
      margin-top:0;
      font-size:20px;
      margin-bottom:25px;
      color: #4a3f81;
      border-bottom: 2px solid #e9ecef;
      padding-bottom: 10px;
    }

    /* ===== DATA STYLES ===== */
    .data-section {
      margin-bottom: 30px;
    }
    
    .data-section:last-child {
      margin-bottom: 0;
    }
    
    .data-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    
    .data-item {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    
    .data-label {
      font-weight: 600;
      color: #4a3f81;
      font-size: 14px;
    }
    
    .data-value {
      padding: 10px;
      background: #f8f9fa;
      border-radius: 6px;
      border: 1px solid #e9ecef;
      min-height: 20px;
    }
    
    .status-badge {
      display:inline-block;
      padding:8px 16px;
      border-radius:6px;
      font-weight:600;
      font-size:14px;
      margin: 10px 0;
    }
    
    .status-approve {
      background:#28a745;
      color:#fff;
    }
    
    .status-pending {
      background:#f0ad4e;
      color:#fff;
    }
    
    .status-reject {
      background:#dc3545;
      color:#fff;
    }
    
    .file-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .file-item {
      padding: 10px;
      background: #f8f9fa;
      border-radius: 6px;
      margin-bottom: 8px;
      border: 1px solid #e9ecef;
    }
    
    .file-item:last-child {
      margin-bottom: 0;
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
      text-align: center;
    }
    
    .btn:hover {
      background:#3a3162;
      color:#fff;
      text-decoration: none;
    }
    
    .btn-secondary {
      background: #6c757d;
    }
    
    .btn-secondary:hover {
      background: #545b62;
    }
    
    .action-buttons {
      display: flex;
      gap: 15px;
      margin-top: 25px;
      justify-content: center;
    }

    /* ===== Responsive ===== */
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
      
      .data-grid {
        grid-template-columns: 1fr;
        gap: 15px;
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
      <img src="img/logo.png" alt="Logo Yayasan Purba Danarta">
      <span>Yayasan Purba Danarta</span>
    </div>
    <nav>
      <ul>
        <li><a href="dashboardpelamar.php">Beranda</a></li>
        <li>
          <a href="#">Profil ▼</a>
          <ul>
            <li><a href="edit_profil.php">Edit Profil</a></li>
            <li><a href="lihat_lamaran.php">Lihat Lamaran</a></li>
          </ul>
        </li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <div class="page-header">
      <h1>Detail Lamaran Kerja</h1>
      <p>Informasi lengkap mengenai lamaran yang Anda kirimkan</p>
    </div>

    <!-- Card Status Lamaran -->
    <div class="card">
      <h2>Status Lamaran</h2>
      <div class="data-item">
        <span class="data-label">Status Terkini</span>
        <span class="status-badge <?= $status_class ?>">
          <?= htmlspecialchars($lamaran['status_lamaran'] ?? 'Menunggu Proses') ?>
        </span>
      </div>
      <div class="data-item">
        <span class="data-label">Tanggal Mengajukan Lamaran</span>
        <div class="data-value"><?= htmlspecialchars($tanggal_lamaran) ?></div>
      </div>
    </div>

    <!-- Card Data Pribadi -->
    <div class="card">
      <h2>Data Pribadi</h2>
      <div class="data-grid">
        <div class="data-item">
          <span class="data-label">Nama Lengkap</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['nama_lengkap'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Posisi Dilamar</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['posisi_dilamar'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Jenis Kelamin</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['jenis_kelamin'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Tempat, Tanggal Lahir</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['tempat_lahir'] ?? '') ?>, <?= $tanggal_lahir ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">NIK</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['nik'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Agama</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['agama'] ?? '') ?></div>
        </div>
      </div>
    </div>

    <!-- Card Kontak -->
    <div class="card">
      <h2>Informasi Kontak</h2>
      <div class="data-grid">
        <div class="data-item">
          <span class="data-label">Alamat Rumah</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['alamat_rumah'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">No. Telepon/WA</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['no_telp'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Email</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['email'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Kontak Darurat</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['kontak_darurat'] ?? '') ?></div>
        </div>
        <div class="data-item">
          <span class="data-label">Pendidikan Terakhir</span>
          <div class="data-value"><?= htmlspecialchars($lamaran['pendidikan_terakhir'] ?? '') ?></div>
        </div>
      </div>
    </div>

    <!-- Card Berkas -->
    <div class="card">
      <h2>Berkas Lamaran</h2>
      <div class="data-section">
        <span class="data-label">Dokumen yang Diupload:</span>
        <ul class="file-list">
          <?php if (!empty($lamaran['surat_lamaran'])): ?>
            <li class="file-item">📄 Surat Lamaran</li>
          <?php endif; ?>
          <?php if (!empty($lamaran['cv'])): ?>
            <li class="file-item">📄 Curriculum Vitae (CV)</li>
          <?php endif; ?>
          <?php if (!empty($lamaran['photo_formal'])): ?>
            <li class="file-item">🖼️ Pas Foto Formal</li>
          <?php endif; ?>
          <?php if (!empty($lamaran['ktp'])): ?>
            <li class="file-item">🆔 KTP</li>
          <?php endif; ?>
          <?php if (!empty($lamaran['ijazah_transkrip'])): ?>
            <li class="file-item">🎓 Ijazah & Transkrip</li>
          <?php endif; ?>
          <?php if (!empty($lamaran['berkas_pendukung'])): ?>
            <li class="file-item">📂 Berkas Pendukung Lainnya</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="data-item">
        <span class="data-label">Catatan:</span>
        <div class="data-value" style="font-style: italic;">
          Semua berkas telah berhasil diupload. Tim HR akan meninjau dokumen Anda dalam proses seleksi.
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <a href="edit_lamaran.php" class="btn">Edit Data Lamaran</a>
      <a href="dashboardpelamar.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
  </main>
</body>
</html>