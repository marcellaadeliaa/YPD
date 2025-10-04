<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit;
}

// Tentukan user_id berdasarkan session yang ada
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['user']['id_karyawan'])) {
    $user_id = $_SESSION['user']['id_karyawan'];
} else {
    header("Location: login_karyawan.php");
    exit;
}

// Ambil data karyawan dari database
$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();

// Jika data tidak ditemukan, redirect ke login
if (!$karyawan) {
    header("Location: login_karyawan.php");
    exit;
}

$stmt->close();

// Proses update no telepon
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_telepon'])) {
    $no_telp_baru = $_POST['no_telp'] ?? '';
    
    // Validasi no telepon
    if (!empty($no_telp_baru)) {
        $update_sql = "UPDATE data_karyawan SET no_telp = ? WHERE id_karyawan = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $no_telp_baru, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Nomor telepon berhasil diupdate!";
            // Update data di session dan variabel
            $karyawan['no_telp'] = $no_telp_baru;
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['no_telp'] = $no_telp_baru;
            }
        } else {
            $success_message = "Gagal mengupdate nomor telepon!";
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Pribadi</title>
<style>
/* ===== Global ===== */
body{
  margin:0;
  font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
  background:linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  color:#2e1f4f;
}
/* ===== Header/Navbar ===== */
header{
  background:#fff;
  padding:20px 40px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:2px solid #34377c;
  flex-wrap:wrap;
}
.logo{display:flex;align-items:center;gap:16px;font-weight:500;font-size:20px;color:#2e1f4f;}
.logo img{width:130px;height:50px;object-fit:contain;}
nav ul{list-style:none;margin:0;padding:0;display:flex;gap:30px;}
nav li{position:relative;}
nav a{text-decoration:none;color:#333;font-weight:600;}
nav li ul{
  display:none;position:absolute;background:#fff;padding:10px 0;
  border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.15);min-width:150px;
}
nav li:hover ul{display:block;}
nav li ul li{padding:5px 20px;}
nav li ul li a{color:#333;font-weight:400;}
/* ===== Main ===== */
main{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:flex-start;
  padding:40px 20px;
}
.card{
  width:100%;
  max-width:600px;
  background:#fff;
  border-radius:15px;
  padding:30px 40px;
  box-shadow:0 0 15px rgba(0,0,0,0.2);
}
h2{
  text-align:center;
  font-size:24px;
  margin-bottom:25px;
  color:#2e1f4f;
  border-bottom:2px solid #eee;
  padding-bottom:10px;
}
.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 25px;
}
.info-item {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #1E105E;
}
.info-label {
  font-weight: 600;
  color: #555;
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
}
.info-value {
  color: #333;
  font-size: 16px;
}
.edit-form {
  background: #f0f2f5;
  padding: 20px;
  border-radius: 10px;
  margin-top: 20px;
}
.edit-form h3 {
  margin-top: 0;
  color: #1E105E;
  font-size: 18px;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #555;
}
.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 16px;
}
.btn {
  display: inline-block;
  padding: 10px 20px;
  background: #4a3f81;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
}
.btn:hover {
  background: #3a3162;
}
.btn-back {
  background: #6c757d;
  margin-left: 10px;
}
.btn-back:hover {
  background: #545b62;
}
.success-message {
  background: #d4edda;
  color: #155724;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  border-left: 4px solid #28a745;
}
.readonly-field {
  background-color: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
}
/* Responsive */
@media(max-width:768px){
  header{flex-direction:column;align-items:flex-start;}
  nav ul{flex-direction:column;gap:10px;}
  nav li ul{position:relative;border:none;box-shadow:none;}
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
          <li><a href="formcutikaryawan.php">Ajukan Cuti</a></li>
          <li><a href="riwayat_cuti_pribadi.php">Riwayat Cuti</a></li>
        </ul>
      </li>
      <li><a href="#">KHL ▾</a>
        <ul>
          <li><a href="formkhlkaryawan.php">Ajukan KHL</a></li>
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
  <div class="card">
    <h2>Data Pribadi</h2>
    
    <?php if ($success_message): ?>
      <div class="success-message">
        <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>

    <div class="info-grid">
      <div class="info-item">
        <span class="info-label">Kode Karyawan</span>
        <span class="info-value"><?= htmlspecialchars($karyawan['kode_karyawan']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Nama Lengkap</span>
        <span class="info-value"><?= htmlspecialchars($karyawan['nama_lengkap']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Email</span>
        <span class="info-value"><?= htmlspecialchars($karyawan['email']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Jabatan</span>
        <span class="info-value"><?= htmlspecialchars($karyawan['jabatan']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Divisi</span>
        <span class="info-value"><?= htmlspecialchars($karyawan['divisi']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Role</span>
        <span class="info-value"><?= htmlspecialchars($karyawan['role']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Status</span>
        <span class="info-value" style="color: <?= $karyawan['status_aktif'] == 'aktif' ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
          <?= ucfirst($karyawan['status_aktif']) ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Tanggal Bergabung</span>
        <span class="info-value"><?= date('d-m-Y', strtotime($karyawan['created_at'])) ?></span>
      </div>
    </div>

    <div class="edit-form">
      <h3>Edit Nomor Telepon</h3>
      <form method="POST">
        <div class="form-group">
          <label for="no_telp">Nomor Telepon</label>
          <input type="tel" id="no_telp" name="no_telp" 
                 value="<?= htmlspecialchars($karyawan['no_telp']) ?>" 
                 placeholder="Masukkan nomor telepon baru" required>
        </div>
        <button type="submit" name="update_telepon" class="btn">Update Nomor Telepon</button>
        <a href="dashboardkaryawan.php" class="btn btn-back">Kembali ke Dashboard</a>
      </form>
    </div>
  </div>
</main>

</body>
</html>