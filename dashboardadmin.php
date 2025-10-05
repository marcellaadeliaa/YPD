<?php
session_start();
require 'config.php'; 

$nama_user = 'Cell';

// 1. Menghitung Total Pelamar Aktif
$query_total_pelamar = "SELECT COUNT(id) AS total FROM data_pelamar WHERE status = 'Menunggu Proses'";
$result_total = $conn->query($query_total_pelamar);
$total_pelamar = $result_total->fetch_assoc()['total'] ?? 0;

// 2. Mengambil data untuk tabel
$query_tabel = "SELECT nama_lengkap, posisi_dilamar, created_at FROM data_pelamar ORDER BY created_at DESC LIMIT 5";
$result_tabel = $conn->query($query_tabel);

// Menutup koneksi database setelah selesai mengambil data
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin SDM</title>
<style>
/* === GLOBAL (Diambil dari dashboardkaryawan.css) === */
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
  width: 140px;
  height: 50px;
  object-fit: contain;
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
  min-width:200px;
  z-index:999;
}
nav li:hover > ul { display:block; }
nav li ul li { padding:5px 20px; }
nav li ul li a {
  color:#333;
  font-weight:400;
  white-space:nowrap;
}

/* ===== MAIN CONTENT ===== */
main {
  max-width:1200px;
  margin:40px auto;
  padding:0 20px;
}
h1 {
  text-align:left;
  font-size:40px;
  margin-bottom:10px;
}
p.admin-title {
    font-size: 16px;
    margin-top: 0;
    margin-bottom: 30px;
    font-weight: 400;
    opacity: 0.9;
}

/* ===== DASHBOARD GRID & CARDS (Penyesuaian untuk Admin) ===== */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.card {
  background:#fff;
  color:#2e1f4f;
  border-radius:20px;
  padding:30px 40px;
  box-shadow:0 2px 10px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
}
.card h3 {
    margin-top:0;
    font-size:20px;
    margin-bottom:15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
.card p {
    margin: 4px 0;
    line-height: 1.6;
}

/* Style untuk tombol */
.btn {
  display:inline-block;
  background:#4a3f81;
  color:#fff;
  padding:12px 20px;
  border-radius:8px;
  text-decoration:none;
  font-weight:600;
  font-size:14px;
  margin-top: auto; /* Mendorong tombol ke bawah kartu */
  text-align: center;
  transition: background-color 0.3s;
}
.btn:hover {
    background-color: #352d5c;
}

/* Style untuk ikon kalender */
.calendar-icon {
    font-size: 3rem;
    text-align: center;
    margin: 20px 0;
    color: #1E105E;
}

/* ===== TABEL DATA (Style baru) ===== */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.data-table th, .data-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.data-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.data-table tbody tr:hover {
    background-color: #f1f1f1;
}

/* ===== Responsive ===== */
@media(max-width:768px){
  header{flex-direction:column;align-items:flex-start;}
  nav ul{flex-direction:column;gap:10px;width:100%;margin-top:15px;}
  nav li ul {
    position:static;
    border:none;
    box-shadow:none;
    padding-left: 20px;
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
        <li><a href="dashboardadmin.php">Beranda</a></li>
        <li><a href="#">Cuti ▾</a>
            <ul>
            <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
            <li><a href="kalender_cuti.php">Kalender Cuti</a></li>
            <li><a href="daftar_sisa_cuti.php">Sisa Cuti Karyawan</a></li>
            </ul>
        </li>
        <li><a href="#">KHL ▾</a>
            <ul>
                <li><a href="riwayat_khl.php">Riwayat KHL</a></li>
                <li><a href="kalender_khl.php">Kalender KHL</a></li>
            </ul>
        </li>
        <li><a href="#">Lamaran Kerja ▾</a>
            <ul>
                <li><a href="administrasi_pelamar.php">Administrasi Pelamar</a></li>
                <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
            </ul>
        </li>
        <li><a href="#">Karyawan ▾</a>
            <ul>
                <li><a href="data_karyawan.php">Data Karyawan</a></li>
            </ul>
        </li>
          <ul>
                <li><a href="logout2.php">Logout</a></li>
            </ul>
        </li>
        </ul>
    </nav>
</header>

<main>
    <div class="welcome-section">
      <h1>Welcome, <?= htmlspecialchars($nama_user) ?>!</h1>
      <h2>Adminsitrator</h2>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3>Lamaran Kerja</h3>
            <p><strong>Total Pelamar Aktif: <?php echo $total_pelamar; ?> orang</strong></p>
            <a href="administrasi_pelamar.php" class="btn">Lihat Rincian</a>
        </div>

        <div class="card">
            <h3>Kalender Cuti</h3>
            <div class="calendar-icon">📅</div>
            <p>Akses kalender cuti karyawan untuk melihat jadwal cuti yang telah direncanakan.</p>
            <a href="kalender_cuti.php" class="btn">Lihat Kalender Cuti</a>
        </div>

        <div class="card">
            <h3>Kalender KHL</h3>
            <div class="calendar-icon">📅</div>
            <p>Akses kalender Kerja Hari Libur (KHL) untuk melihat jadwal kerja di hari libur.</p>
            <a href="kalender_khl.php" class="btn">Lihat Kalender KHL</a>
        </div>
    </div>

    <div class="card">
        <h3>Daftar Pelamar Terbaru</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Pelamar</th>
                    <th>Posisi Dilamar</th>
                    <th>Tanggal Melamar</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_tabel->num_rows > 0): ?>
                    <?php while($row = $result_tabel->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                            <td><?= date('d F Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Tidak ada data pelamar saat ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>