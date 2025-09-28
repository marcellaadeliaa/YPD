<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik     = $_POST['nik'] ?? '';
    $jenis   = $_POST['jenis_cuti'] ?? '';
    $tanggal = $_POST['tanggal_cuti'] ?? '';

    // ✅ Simpan ke session agar dashboard bisa menampilkan otomatis
    $_SESSION['last_cuti'] = [
        'tanggal' => $tanggal,
        'jenis'   => $jenis,
        'status'  => 'Menunggu Persetujuan'
    ];

} else {
    header("Location: formcutikaryawan.php");
    exit;
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
      margin:0;
      font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(180deg, #1E105E 0%, #8897AE 100%);
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
    .logo img {width:130px;height:50px;object-fit:contain;}
    main {
      max-width:600px;
      margin:60px auto;
      background:#fff;
      border-radius:15px;
      padding:30px 40px;
      box-shadow:0 0 10px rgba(72,54,120,0.2);
      text-align:center;
    }
    h1 {font-size:24px;color:#2e1f4f;margin-bottom:20px;}
    .data-cuti {text-align:left;font-size:16px;line-height:1.8;}
    a.btn {
      display:inline-block;margin-top:30px;padding:10px 20px;
      background-color:#4a3f81;color:#fff;text-decoration:none;
      border-radius:8px;font-weight:600;
    }
    a.btn:hover { background-color:#3a3162; }
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
    <h1>Pengajuan Cuti Terkirim</h1>
    <div class="data-cuti">
      <strong>No. Induk Karyawan:</strong> <?= htmlspecialchars($nik) ?><br>
      <strong>Jenis Cuti:</strong> <?= htmlspecialchars($jenis) ?><br>
      <strong>Tanggal Cuti:</strong> <?= htmlspecialchars($tanggal) ?><br>
    </div>
    <a href="dashboardkaryawan.php" class="btn">Kembali ke Dashboard</a>
  </main>
</body>
</html>
