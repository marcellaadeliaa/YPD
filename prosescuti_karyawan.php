<?php
session_start();
require_once 'config.php'; // Memanggil file koneksi database

// Inisialisasi variabel untuk menampilkan data nanti
$display_data = false;
$error_msg = '';
$file_surat_path = null; // Variabel untuk menyimpan path file

// Fungsi untuk menangani upload file
function handleFileUpload($file) {
    // Cek jika tidak ada file atau ada error saat upload
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = 'uploads/surat_sakit/';
    // Buat direktori jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Buat nama file yang unik untuk menghindari penimpaan
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    // Pindahkan file dari temporary location ke direktori tujuan
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath; // Kembalikan path file jika berhasil
    } else {
        return false; // Kembalikan false jika gagal
    }
}

// Cek apakah user sudah login dan form dikirim dengan metode POST
if (!isset($_SESSION['user']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login_karyawan.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari session dan form
    $user = $_SESSION['user'];
    $kode_karyawan = $user['kode_karyawan'];
    $nama_karyawan = $user['nama_lengkap'];
    $divisi = $user['divisi'];
    $jabatan = $user['jabatan'];
    $role = $user['role'];

    $jenis_cuti_raw = $_POST['jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $alasan = $_POST['alasan_cuti'];
    $jenis_cuti = $jenis_cuti_raw;

    // Handle jika jenis cuti adalah 'Khusus'
    if ($jenis_cuti_raw === 'Khusus' && !empty($_POST['jenis_cuti_khusus'])) {
        $jenis_cuti = 'Khusus - ' . $_POST['jenis_cuti_khusus'];
    }

    // Validasi khusus untuk cuti sakit - wajib ada file
    if ($jenis_cuti_raw === 'Sakit') {
        if (!isset($_FILES['bukti_surat_dokter']) || $_FILES['bukti_surat_dokter']['error'] === UPLOAD_ERR_NO_FILE) {
            header("Location: formcutikaryawan.php?status=error&message=Untuk cuti sakit, wajib mengunggah bukti surat keterangan dokter.");
            exit();
        }
        
        $uploadResult = handleFileUpload($_FILES['bukti_surat_dokter']);
        if ($uploadResult === false) {
            header("Location: formcutikaryawan.php?status=error&message=Gagal mengunggah file surat dokter.");
            exit();
        } elseif ($uploadResult === null) {
            header("Location: formcutikaryawan.php?status=error&message=Terjadi kesalahan saat upload file.");
            exit();
        }
        $file_surat_path = $uploadResult;
    }

    // Validasi dasar
    if (empty($kode_karyawan) || empty($jenis_cuti) || empty($tanggal_mulai) || empty($tanggal_akhir) || empty($alasan)) {
        header("Location: formcutikaryawan.php?status=error&message=Semua field wajib diisi.");
        exit();
        }
    } else {
    header("Location: formcutikaryawan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hasil Pengajuan Cuti</title>
<style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(180deg, #1E105E 0%, #8897AE 100%);
      color: #333;
      min-height: 100vh;
    }
    
    header {
      background: #fff;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #34377c;
    }
    
    .logo {
      display: flex;
      align-items: center;
      gap: 16px;
      font-weight: 500;
      font-size: 20px;
      color: #2e1f4f;
    }
    
    .logo img {
      width: 140px;
      height: 50px;
      object-fit: contain;
    }
    
    nav a {
      text-decoration: none;
      color: #333;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 5px;
      transition: background-color 0.3s;
    }
    
    nav a:hover {
      background-color: #f0f0f0;
    }
    
    main {
      max-width: 600px;
      margin: 60px auto;
      background: #fff;
      border-radius: 15px;
      padding: 30px 40px;
      box-shadow: 0 0 10px rgba(72, 54, 120, 0.2);
      text-align: center;
    }
    
    h1 {
      font-size: 24px;
      color: #2e1f4f;
      margin-bottom: 10px;
    }
    
    .success-icon {
      font-size: 48px;
      color: #28a745;
      margin-bottom: 15px;
    }
    
    .data-cuti {
      text-align: left;
      font-size: 16px;
      line-height: 1.8;
      border-top: 1px solid #eee;
      margin-top: 20px;
      padding-top: 20px;
    }
    
    .data-cuti p {
      margin: 8px 0;
      display: grid;
      grid-template-columns: 180px 1fr;
    }
    
    .data-cuti strong {
      color: #4a3f81;
    }
    
    a.btn {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 25px;
      background-color: #4a3f81;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: background-color 0.3s;
      border: none;
      cursor: pointer;
    }
    
    a.btn:hover {
      background-color: #3a3162;
    }
    
    .error-message {
      color: #dc3545;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      padding: 15px;
      border-radius: 8px;
      margin: 20px 0;
    }
    
    .success-message {
      color: #155724;
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      padding: 15px;
      border-radius: 8px;
      margin: 20px 0;
    }
    
    @media (max-width: 768px) {
      header {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
      }
      
      main {
        margin: 30px 20px;
        padding: 20px;
      }
      
      .data-cuti p {
        grid-template-columns: 1fr;
        gap: 5px;
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
      <a href="dashboardkaryawan.php">Beranda</a>
    </nav>
  </header>

  <main>
    <?php if ($display_data): ?>
        <div class="success-icon">✓</div>
        <h1>Pengajuan Cuti Berhasil Terkirim</h1>
        <p>Data pengajuan Anda telah berhasil disimpan dan akan segera diproses. Berikut adalah rinciannya:</p>
        <div class="data-cuti">
            <p><strong>Kode Karyawan:</strong> <?= htmlspecialchars($kode_karyawan) ?></p>
            <p><strong>Nama Karyawan:</strong> <?= htmlspecialchars($nama_karyawan) ?></p>
            <p><strong>Jenis Cuti:</strong> <?= htmlspecialchars($jenis_cuti) ?></p>
            <p><strong>Tanggal Mulai:</strong> <?= date('d F Y', strtotime($tanggal_mulai)) ?></p>
            <p><strong>Tanggal Akhir:</strong> <?= date('d F Y', strtotime($tanggal_akhir)) ?></p>
            <p><strong>Alasan:</strong> <?= htmlspecialchars($alasan) ?></p>
            <?php if ($file_surat_path): ?>
                <p><strong>Surat Dokter:</strong> Berkas telah diunggah.</p>
            <?php endif; ?>
            <p><strong>Status:</strong> <span style="font-weight:bold; color: #f39c12;">Menunggu Persetujuan</span></p>
        </div>
        <a href="riwayat_cuti_pribadi.php" class="btn">Lihat Riwayat Cuti</a>
    <?php else: ?>
        <h1>Terjadi Kesalahan</h1>
        <p>Maaf, terjadi kesalahan saat memproses pengajuan Anda. Silakan coba lagi.</p>
        <a href="formcutikaryawan.php" class="btn">Kembali ke Formulir</a>
    <?php endif; ?>
  </main>
</body>
</html>