<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil semua data dari form
    $nik              = $_POST['nik']               ?? '';
    $proyek           = $_POST['proyek']            ?? '';
    $tanggal_khl      = $_POST['tanggal_khl']       ?? '';
    $jam_mulai_kerja  = $_POST['jam_mulai_kerja']   ?? '';
    $jam_akhir_kerja  = $_POST['jam_akhir_kerja']   ?? '';
    $tanggal_cuti_khl = $_POST['tanggal_cuti_khl']  ?? '';
    $jam_mulai_cuti   = $_POST['jam_mulai_cuti_khl']?? '';
    $jam_akhir_cuti   = $_POST['jam_akhir_cuti_khl']?? '';

    // ✅ Simpan ke session supaya dashboard/riwayat bisa menampilkan otomatis
    $_SESSION['last_khl'] = [
    'nik'             => $nik,
    'proyek'          => $proyek,
    'tanggal_khl'     => $tanggal_khl,
    'jam_mulai_kerja' => $jam_mulai_kerja,
    'jam_akhir_kerja' => $jam_akhir_kerja,
    'tanggal_cuti'    => $tanggal_cuti_khl,
    'jam_mulai_cuti'  => $jam_mulai_cuti,
    'jam_akhir_cuti'  => $jam_akhir_cuti,
    // Tambahan untuk dashboard
    'tanggal'         => $tanggal_khl,
    'jenis'           => $proyek,
    'status'          => 'Menunggu Persetujuan'
    ];


    // ⬆️ Kalau mau langsung ke database, lakukan INSERT di sini
} else {
    header("Location: formkhlkaryawan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hasil Pengajuan KHL</title>
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
  .data-khl {text-align:left;font-size:16px;line-height:1.8;}
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
    <h1>Pengajuan KHL Terkirim</h1>
    <div class="data-khl">
      <strong>No. Kode Karyawan:</strong> <?= htmlspecialchars($nik) ?><br>
      <strong>Proyek:</strong> <?= htmlspecialchars($proyek) ?><br>
      <strong>Tanggal KHL:</strong> <?= htmlspecialchars($tanggal_khl) ?><br>
      <strong>Jam Kerja:</strong> <?= htmlspecialchars($jam_mulai_kerja) ?> – <?= htmlspecialchars($jam_akhir_kerja) ?><br>
      <strong>Tanggal Cuti KHL:</strong> <?= htmlspecialchars($tanggal_cuti_khl) ?><br>
      <strong>Jam Cuti KHL:</strong> <?= htmlspecialchars($jam_mulai_cuti) ?> – <?= htmlspecialchars($jam_akhir_cuti) ?><br>
      <strong>Status:</strong> Menunggu Persetujuan
    </div>
    <a href="dashboardkaryawan.php" class="btn">Kembali ke Dashboard</a>
  </main>
</body>
</html>
