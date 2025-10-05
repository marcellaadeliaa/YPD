<?php
session_start();
require 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// [PERBAIKAN] Mengambil data langsung dari tabel data_pelamar
$query = $conn->prepare("SELECT * FROM data_pelamar WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$lamaran = $result->fetch_assoc();

// Jika user login tapi belum pernah mengisi form, arahkan ke form pendaftaran
if (!$lamaran) {
    header("Location: formpelamar.php");
    exit;
}

// [PERBAIKAN] Menggunakan kolom yang benar dari tabel data_pelamar
$tanggal_lahir = $lamaran['tanggal_lahir'] ? date('d F Y', strtotime($lamaran['tanggal_lahir'])) : '';
$tanggal_lamaran = $lamaran['created_at'] ? date('d F Y, H:i', strtotime($lamaran['created_at'])) : '';

// [PERBAIKAN] Logika untuk menentukan warna status badge
$status_lamaran = $lamaran['status'];
$status_class = 'status-pending'; // Default untuk 'Menunggu Proses', 'Seleksi ...', dll.
if ($status_lamaran == 'Diterima') {
    $status_class = 'status-approve';
} elseif ($status_lamaran == 'Tidak Lolos') {
    $status_class = 'status-reject';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Lamaran - Yayasan Purba Danarta</title>
  <style>
    /* CSS Anda tidak perlu diubah */
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#fff; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width:140px; height:50px; object-fit:contain; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
    main { max-width:1000px; margin:40px auto; padding:0 20px; }
    .page-header { text-align: center; margin-bottom: 30px; }
    .page-header h1 { font-size: 26px; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    .page-header p { font-size: 16px; opacity: 0.9; }
    .card { background:#fff; color:#2e1f4f; border-radius:20px; padding:30px 40px; margin-bottom:40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .card h2 { margin-top:0; font-size:20px; margin-bottom:25px; color: #4a3f81; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }
    .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .data-item { display: flex; flex-direction: column; gap: 5px; }
    .data-label { font-weight: 600; color: #4a3f81; font-size: 14px; }
    .data-value { padding: 10px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef; min-height: 20px; }
    .status-badge { display:inline-block; padding:8px 16px; border-radius:6px; font-weight:600; font-size:14px; margin: 10px 0; }
    .status-approve { background:#28a745; color:#fff; }
    .status-pending { background:#f0ad4e; color:#fff; }
    .status-reject { background:#dc3545; color:#fff; }
    .file-list a { text-decoration: none; color: #2e1f4f; }
    .file-item { padding: 10px; background: #f8f9fa; border-radius: 6px; margin-bottom: 8px; border: 1px solid #e9ecef; transition: background-color 0.2s; }
    .file-item:hover { background-color: #e9ecef; }
    .btn { display:inline-block; background:#4a3f81; color:#fff; padding:12px 24px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; border: none; cursor: pointer; text-align: center; }
    .btn-secondary { background: #6c757d; }
    .action-buttons { display: flex; gap: 15px; margin-top: 25px; justify-content: center; }
    @media(max-width:768px){ .data-grid { grid-template-columns: 1fr; } }
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
            <li><a href="dashboard_user.php">Beranda</a></li>
            <li><a href="lihat_lamaran.php">Lihat Lamaran</a></li>
            <li><a href="edit_lamaran.php">Edit Lamaran</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
  </header>

  <main>
    <div class="page-header">
      <h1>Detail Lamaran Kerja Anda</h1>
      <p>Informasi lengkap mengenai lamaran yang telah Anda kirimkan.</p>
    </div>

    <div class="card">
      <h2>Status Lamaran</h2>
      <div class="data-item">
        <span class="data-label">Status Terkini</span>
        <span class="status-badge <?= $status_class ?>">
          <?= htmlspecialchars($status_lamaran) ?>
        </span>
      </div>
      <div class="data-item">
        <span class="data-label">Tanggal Mengajukan Lamaran</span>
        <div class="data-value"><?= htmlspecialchars($tanggal_lamaran) ?> WIB</div>
      </div>
    </div>

    <div class="card">
      <h2>Data Pribadi</h2>
      <div class="data-grid">
        <div class="data-item"><span class="data-label">Nama Lengkap</span><div class="data-value"><?= htmlspecialchars($lamaran['nama_lengkap'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Posisi Dilamar</span><div class="data-value"><?= htmlspecialchars($lamaran['posisi_dilamar'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Jenis Kelamin</span><div class="data-value"><?= htmlspecialchars($lamaran['jenis_kelamin'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Tempat, Tanggal Lahir</span><div class="data-value"><?= htmlspecialchars($lamaran['tempat_lahir'] ?? '') ?>, <?= $tanggal_lahir ?></div></div>
        <div class="data-item"><span class="data-label">NIK</span><div class="data-value"><?= htmlspecialchars($lamaran['nik'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Agama</span><div class="data-value"><?= htmlspecialchars($lamaran['agama'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Pendidikan Terakhir</span><div class="data-value"><?= htmlspecialchars($lamaran['pendidikan_terakhir'] ?? '') ?></div></div>
      </div>
    </div>

    <div class="card">
      <h2>Informasi Kontak</h2>
      <div class="data-grid">
        <div class="data-item"><span class="data-label">Alamat Rumah (KTP)</span><div class="data-value"><?= htmlspecialchars($lamaran['alamat_rumah'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Alamat Domisili</span><div class="data-value"><?= htmlspecialchars($lamaran['alamat_domisili'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">No. Telepon/WA</span><div class="data-value"><?= htmlspecialchars($lamaran['no_telp'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Email</span><div class="data-value"><?= htmlspecialchars($lamaran['email'] ?? '') ?></div></div>
        <div class="data-item"><span class="data-label">Kontak Darurat</span><div class="data-value"><?= htmlspecialchars($lamaran['kontak_darurat'] ?? '') ?></div></div>
      </div>
    </div>

     <div class="card">
      <h2>Berkas Lamaran</h2>
      <div class="file-list">
        <?php if (!empty($lamaran['surat_lamaran'])): ?><a href="<?= htmlspecialchars($lamaran['surat_lamaran']) ?>" target="_blank"><div class="file-item">📄 Lihat Surat Lamaran</div></a><?php endif; ?>
        <?php if (!empty($lamaran['cv'])): ?><a href="<?= htmlspecialchars($lamaran['cv']) ?>" target="_blank"><div class="file-item">📄 Lihat Curriculum Vitae (CV)</div></a><?php endif; ?>
        <?php if (!empty($lamaran['photo_formal'])): ?><a href="<?= htmlspecialchars($lamaran['photo_formal']) ?>" target="_blank"><div class="file-item">🖼️ Lihat Pas Foto</div></a><?php endif; ?>
        <?php if (!empty($lamaran['ktp'])): ?><a href="<?= htmlspecialchars($lamaran['ktp']) ?>" target="_blank"><div class="file-item">🆔 Lihat KTP</div></a><?php endif; ?>
        <?php if (!empty($lamaran['ijazah_transkrip'])): ?><a href="<?= htmlspecialchars($lamaran['ijazah_transkrip']) ?>" target="_blank"><div class="file-item">🎓 Lihat Ijazah & Transkrip</div></a><?php endif; ?>
        <?php if (!empty($lamaran['berkas_pendukung'])): ?><a href="<?= htmlspecialchars($lamaran['berkas_pendukung']) ?>" target="_blank"><div class="file-item">📂 Lihat Berkas Pendukung</div></a><?php endif; ?>
      </div>

      <div class="data-item" style="margin-top: 20px;">
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