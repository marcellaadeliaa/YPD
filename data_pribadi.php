<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['user']['id_karyawan'])) {
    $user_id = $_SESSION['user']['id_karyawan'];
} else {
    header("Location: login_karyawan.php");
    exit;
}

$sql = "SELECT * FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();

if (!$karyawan) {
    header("Location: login_karyawan.php");
    exit;
}

$stmt->close();

$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update data pribadi yang diizinkan
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $nik = $_POST['nik'] ?? '';
    $alamat_rumah = $_POST['alamat_rumah'] ?? '';
    $alamat_domisili = $_POST['alamat_domisili'] ?? '';
    $agama = $_POST['agama'] ?? '';
    $kontak_darurat = $_POST['kontak_darurat'] ?? '';
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    
    $update_sql = "UPDATE data_karyawan SET 
                    jenis_kelamin = ?, 
                    tempat_lahir = ?, 
                    tanggal_lahir = ?, 
                    nik = ?, 
                    alamat_rumah = ?, 
                    alamat_domisili = ?, 
                    agama = ?, 
                    kontak_darurat = ?, 
                    pendidikan_terakhir = ?, 
                    no_telp = ? 
                   WHERE id_karyawan = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    
    // Handle tanggal lahir yang kosong
    if (empty($tanggal_lahir)) {
        $tanggal_lahir = null;
    }
    
    $update_stmt->bind_param("ssssssssssi", 
        $jenis_kelamin,
        $tempat_lahir,
        $tanggal_lahir,
        $nik,
        $alamat_rumah,
        $alamat_domisili,
        $agama,
        $kontak_darurat,
        $pendidikan_terakhir,
        $no_telp,
        $user_id
    );
    
    if ($update_stmt->execute()) {
        $success_message = "Data pribadi berhasil diupdate!";
        // Update data di session
        $karyawan['jenis_kelamin'] = $jenis_kelamin;
        $karyawan['tempat_lahir'] = $tempat_lahir;
        $karyawan['tanggal_lahir'] = $tanggal_lahir;
        $karyawan['nik'] = $nik;
        $karyawan['alamat_rumah'] = $alamat_rumah;
        $karyawan['alamat_domisili'] = $alamat_domisili;
        $karyawan['agama'] = $agama;
        $karyawan['kontak_darurat'] = $kontak_darurat;
        $karyawan['pendidikan_terakhir'] = $pendidikan_terakhir;
        $karyawan['no_telp'] = $no_telp;
        
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['no_telp'] = $no_telp;
        }
    } else {
        $success_message = "Gagal mengupdate data pribadi!";
    }
    $update_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Pribadi</title>
<style>
body{
  margin:0;
  font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
  background:linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  color:#2e1f4f;
}
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
.logo img{width:140px;height:50px;object-fit:contain;}
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
main{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:flex-start;
  padding:40px 20px;
}
.card{
  width:100%;
  max-width:800px;
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
  margin-bottom: 20px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 20px;
}
.form-group {
  margin-bottom: 15px;
}
.form-group.full-width {
  grid-column: 1 / -1;
}
.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #555;
}
.form-group input, .form-group select, .form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 16px;
  box-sizing: border-box;
}
.form-group textarea {
  resize: vertical;
  min-height: 80px;
}
.readonly-field {
  background-color: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
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
.action-buttons {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}
@media(max-width:768px){
  header{flex-direction:column;align-items:flex-start;}
  nav ul{flex-direction:column;gap:10px;}
  nav li ul{position:relative;border:none;box-shadow:none;}
  .info-grid {
    grid-template-columns: 1fr;
  }
  .form-grid {
    grid-template-columns: 1fr;
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

    <!-- Informasi yang tidak bisa diedit -->
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

    <!-- Form edit data pribadi yang diizinkan -->
    <div class="edit-form">
      <h3>Edit Data Pribadi</h3>
      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin">
              <option value="">Pilih Jenis Kelamin</option>
              <option value="Laki-laki" <?= $karyawan['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
              <option value="Perempuan" <?= $karyawan['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="tempat_lahir">Tempat Lahir</label>
            <input type="text" id="tempat_lahir" name="tempat_lahir" 
                   value="<?= htmlspecialchars($karyawan['tempat_lahir'] ?? '') ?>" 
                   placeholder="Tempat lahir">
          </div>
          
          <div class="form-group">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                   value="<?= htmlspecialchars($karyawan['tanggal_lahir'] ?? '') ?>">
          </div>
          
          <div class="form-group">
            <label for="nik">NIK</label>
            <input type="text" id="nik" name="nik" 
                   value="<?= htmlspecialchars($karyawan['nik'] ?? '') ?>" 
                   placeholder="Nomor Induk Kependudukan">
          </div>
          
          <div class="form-group">
            <label for="agama">Agama</label>
            <select id="agama" name="agama">
              <option value="">Pilih Agama</option>
              <option value="Islam" <?= $karyawan['agama'] == 'Islam' ? 'selected' : '' ?>>Islam</option>
              <option value="Kristen" <?= $karyawan['agama'] == 'Kristen' ? 'selected' : '' ?>>Kristen</option>
              <option value="Katholik" <?= $karyawan['agama'] == 'Katholik' ? 'selected' : '' ?>>Katholik</option>
              <option value="Hindu" <?= $karyawan['agama'] == 'Hindu' ? 'selected' : '' ?>>Hindu</option>
              <option value="Buddha" <?= $karyawan['agama'] == 'Buddha' ? 'selected' : '' ?>>Buddha</option>
              <option value="Khonghucu" <?= $karyawan['agama'] == 'Khonghucu' ? 'selected' : '' ?>>Khonghucu</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
            <select id="pendidikan_terakhir" name="pendidikan_terakhir">
              <option value="">Pilih Pendidikan</option>
              <option value="SD" <?= $karyawan['pendidikan_terakhir'] == 'SD' ? 'selected' : '' ?>>SD</option>
              <option value="SMP" <?= $karyawan['pendidikan_terakhir'] == 'SMP' ? 'selected' : '' ?>>SMP</option>
              <option value="SMA" <?= $karyawan['pendidikan_terakhir'] == 'SMA' ? 'selected' : '' ?>>SMA</option>
              <option value="SMK" <?= $karyawan['pendidikan_terakhir'] == 'SMK' ? 'selected' : '' ?>>SMK</option>
              <option value="Diploma" <?= $karyawan['pendidikan_terakhir'] == 'Diploma' ? 'selected' : '' ?>>Diploma</option>
              <option value="Sarjana" <?= $karyawan['pendidikan_terakhir'] == 'Sarjana' ? 'selected' : '' ?>>Sarjana</option>
              <option value="Magister" <?= $karyawan['pendidikan_terakhir'] == 'Magister' ? 'selected' : '' ?>>Magister</option>
              <option value="Doktor" <?= $karyawan['pendidikan_terakhir'] == 'Doktor' ? 'selected' : '' ?>>Doktor</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="no_telp">No. Telepon</label>
            <input type="tel" id="no_telp" name="no_telp" 
                   value="<?= htmlspecialchars($karyawan['no_telp'] ?? '') ?>" 
                   placeholder="Nomor telepon aktif" required>
          </div>
          
          <div class="form-group">
            <label for="kontak_darurat">Kontak Darurat</label>
            <input type="text" id="kontak_darurat" name="kontak_darurat" 
                   value="<?= htmlspecialchars($karyawan['kontak_darurat'] ?? '') ?>" 
                   placeholder="Nomor kontak darurat">
          </div>
          
          <div class="form-group full-width">
            <label for="alamat_rumah">Alamat Rumah</label>
            <textarea id="alamat_rumah" name="alamat_rumah" 
                      placeholder="Alamat rumah lengkap"><?= htmlspecialchars($karyawan['alamat_rumah'] ?? '') ?></textarea>
          </div>
          
          <div class="form-group full-width">
            <label for="alamat_domisili">Alamat Domisili</label>
            <textarea id="alamat_domisili" name="alamat_domisili" 
                      placeholder="Alamat domisili saat ini"><?= htmlspecialchars($karyawan['alamat_domisili'] ?? '') ?></textarea>
          </div>
        </div>
        
        <div class="action-buttons">
          <button type="submit" class="btn">Update Data Pribadi</button>
          <a href="dashboardkaryawan.php" class="btn btn-back">Kembali ke Dashboard</a>
        </div>
      </form>
    </div>
  </div>
</main>

</body>
</html>