<?php
session_start();

// --- Contoh data login / profil ---
$nama               = "Adel";
$sisa_cuti_tahunan  = 12;
$sisa_cuti_lustrum  = 5;

$lastCuti         = $_SESSION['last_cuti'] ?? null;
$tanggal_cuti     = $lastCuti['tanggal'] ?? "-";
$jenis_cuti       = $lastCuti['jenis']   ?? "-";
$status_cuti      = $lastCuti['status']  ?? null;   // null kalau belum ada

$lastKhl          = $_SESSION['last_khl'] ?? null;
$tanggal_khl      = $lastKhl['tanggal'] ?? "-";
$jenis_khl        = $lastKhl['jenis']   ?? "-";
$status_khl       = $lastKhl['status']  ?? null;
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

/* ===== MAIN CONTENT ===== */
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
.status-approve {
  display:inline-block;
  background:#28a745;
  color:#fff;
  padding:6px 12px;
  border-radius:6px;
  font-weight:600;
  font-size:14px;
}
.status-pending {
  display:inline-block;
  background:#f0ad4e;
  color:#fff;
  padding:6px 12px;
  border-radius:6px;
  font-weight:600;
  font-size:14px;
}

/* ===== Responsive ===== */
@media(max-width:768px){
  header{flex-direction:column;align-items:flex-start;}
  nav ul{flex-direction:column;gap:10px;}
  nav li ul {
    position:relative;
    border:none;
    box-shadow:none;
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
    <h3>Data Pribadi</h3>
    <p>Lengkapi Data Pribadi Anda.</p>
    <a href="data_pribadi.php" class="btn">Lihat</a>
  </div>

  <div class="card">
    <h3>Sisa Cuti</h3>
    <p>Cuti Tahunan : <?= $sisa_cuti_tahunan ?> hari</p>
    <p>Cuti Lustrum : <?= $sisa_cuti_lustrum ?> hari</p>
  </div>

  <div class="card">
  <h3>Status Pengajuan Cuti</h3>
  <p>Tanggal Cuti : <?= htmlspecialchars($tanggal_cuti) ?></p>
  <p>Jenis Cuti   : <?= htmlspecialchars($jenis_cuti) ?></p>

    <?php if ($status_cuti): ?>
      <span class="<?= $status_cuti==='Menunggu Persetujuan' ? 'status-pending' : 'status-approve' ?>">
        <?= htmlspecialchars($status_cuti) ?>
      </span>
    <?php else: ?>
      <p>Belum ada pengajuan</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>Status Pengajuan KHL</h3>
    <p>Tanggal KHL : <?= htmlspecialchars($tanggal_khl) ?></p>
    <p>Proyek      : <?= htmlspecialchars($jenis_khl) ?></p>

    <?php if ($status_khl): ?>
      <span class="<?= $status_khl==='Menunggu Persetujuan' ? 'status-pending' : 'status-approve' ?>">
        <?= htmlspecialchars($status_khl) ?>
      </span>
    <?php else: ?>
      <p>Belum ada pengajuan</p>
    <?php endif; ?>
  </div>

</main>

</body>
</html>
