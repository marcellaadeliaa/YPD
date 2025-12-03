<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$query = $conn->prepare("SELECT * FROM data_pelamar WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();
$query->close();

if (!$data) {
    echo "<script>alert('Anda belum mengisi data lamaran. Silakan isi terlebih dahulu.'); window.location.href='formpelamar.php';</script>";
    exit;
}

$isEditable = ($data['status'] === 'Menunggu Proses');
$readonlyAttribute = $isEditable ? '' : 'disabled'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!$isEditable) {
        $error = "Maaf, data tidak dapat diubah karena lamaran Anda sedang diproses (Status: " . $data['status'] . ").";
    } else {
        $namaLengkap = $_POST['namaLengkap'];
        $posisiDilamar = $_POST['posisiDilamar'];
        $jenisKelamin = $_POST['jenisKelamin'];
        $tempatLahir = $_POST['tempatLahir'];
        $tanggalLahir = $_POST['tanggalLahir'];
        $nik = $_POST['nomorIndukKeluarga'];
        $alamatRumah = $_POST['alamatRumah'];
        $alamatDomisili = $_POST['alamatDomisili']; 
        $noTelp = $_POST['noTelp'];
        $email = $_POST['email'];
        $agama = $_POST['agama'];
        $kontakDarurat = $_POST['kontakDarurat'];
        $pendidikanTerakhir = $_POST['pendidikanTerakhir'];

        try {
            $sql = "UPDATE data_pelamar SET 
                        nama_lengkap = ?, posisi_dilamar = ?, jenis_kelamin = ?, 
                        tempat_lahir = ?, tanggal_lahir = ?, nik = ?, 
                        alamat_rumah = ?, alamat_domisili = ?, no_telp = ?, 
                        email = ?, agama = ?, kontak_darurat = ?, 
                        pendidikan_terakhir = ? 
                    WHERE user_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssssssi",
                $namaLengkap, $posisiDilamar, $jenisKelamin, $tempatLahir, $tanggalLahir,
                $nik, $alamatRumah, $alamatDomisili, $noTelp, $email, $agama, 
                $kontakDarurat, $pendidikanTerakhir, $user_id
            );
            
            if ($stmt->execute()) {
                $success = "Profil berhasil diperbarui!";
                $data['nama_lengkap'] = $namaLengkap;
                $data['posisi_dilamar'] = $posisiDilamar;
            } else {
                $error = "Gagal memperbarui profil: " . $stmt->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Profil - Yayasan Purba Danarta</title>
  <style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#fff; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; backdrop-filter:blur(5px); flex-wrap:wrap; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width:140px; height:50px; object-fit:contain; }
    
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; position: relative; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; transition: color 0.3s ease; }
    nav a:hover { color:#4a3f81; }
    
    nav li ul { display: none; position: absolute; top: 100%; left: 0; background: #fff; min-width: 180px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; padding: 8px 0; z-index: 1000; border: 1px solid #e9ecef; }
    nav li:hover ul { display: block; }
    nav li ul li { margin: 0; display: block; }
    nav li ul a { padding: 10px 16px; color: #333; font-weight: 500; transition: background-color 0.3s ease; white-space: nowrap; }
    nav li ul a:hover { background-color: #f8f9fa; color: #4a3f81; }
    nav li > a::after { content: ''; display: inline-block; margin-left: 6px; transition: transform 0.3s ease; }
    nav li:hover > a::after { transform: rotate(180deg); }
    
    main { max-width:1000px; margin:40px auto; padding:0 20px; }
    .page-header { text-align: center; margin-bottom: 30px; }
    .page-header h1 { font-size: 26px; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    .page-header p { font-size: 16px; opacity: 0.9; }
    .card { background:#fff; color:#2e1f4f; border-radius:20px; padding:30px 40px; margin-bottom:40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .card h2 { margin-top:0; font-size:20px; margin-bottom:25px; color: #4a3f81; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }
    .form-container { display: flex; gap: 40px; flex-wrap: nowrap; }
    .form-left, .form-right { flex: 1; display: flex; flex-direction: column; gap: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-weight: 600; font-size: 14px; color: #2e1f4f; }
    
    .form-group input:disabled, .form-group select:disabled { 
        background-color: #e9ecef; 
        color: #6c757d;
        border-color: #ced4da;
        cursor: not-allowed;
    }
    
    .form-group input, .form-group select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-size: 14px; transition: border-color 0.3s ease; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #4a3f81; background-color: #fff; }
    
    .btn { display:inline-block; background:#4a3f81; color:#fff; padding:12px 24px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; transition: background 0.3s ease; border: none; cursor: pointer; text-align: center; }
    .btn:hover { background:#3a3162; color:#fff; text-decoration: none; }
    .btn-secondary { background: #6c757d; }
    .btn-secondary:hover { background: #545b62; }
    
    .btn:disabled, .btn-disabled {
        background-color: #cccccc !important;
        color: #666666 !important;
        cursor: not-allowed;
        pointer-events: none; 
    }

    .tooltip-container {
        position: relative;
        display: inline-block;
        cursor: not-allowed; 
    }

    .tooltip-container .tooltip-text {
        visibility: hidden;
        width: 240px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 8px 10px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -120px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 12px;
        font-weight: normal;
        pointer-events: none;
    }

    .tooltip-container .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }

    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .action-buttons { display: flex; gap: 15px; margin-top: 25px; justify-content: center; align-items: center; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    @media(max-width:768px){ 
        .form-container { flex-direction: column; gap: 20px; } 
        header { padding: 15px 20px; flex-direction: column; gap: 15px; }
        nav ul { gap: 15px; }
        nav li ul { left: -50%; }
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
    <div class="page-header">
      <h1>Edit Profil Pelamar</h1>
      <?php if($isEditable): ?>
        <p>Perbarui data pribadi dan informasi lamaran Anda</p>
      <?php else: ?>
        <p>Data pelamar <strong style="color: #ffcccc;">DILOCK</strong> (Status: <?= htmlspecialchars($data['status']) ?>)</p>
      <?php endif; ?>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
      
      <?php if(!$isEditable): ?>
      <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        <span><strong>Mode Lihat Saja:</strong> Data Anda sudah masuk tahap seleksi (<?= htmlspecialchars($data['status']) ?>) dan tidak dapat diubah lagi.</span>
      </div>
      <?php endif; ?>

      <h2>Data Pribadi</h2>
      <form action="edit_lamaran.php" method="POST">
        <div class="form-container">
          <div class="form-left">
            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="namaLengkap" value="<?= htmlspecialchars($data['nama_lengkap'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Posisi yang Dilamar</label>
              <input type="text" name="posisiDilamar" value="<?= htmlspecialchars($data['posisi_dilamar'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Jenis Kelamin</label>
              <select name="jenisKelamin" required <?= $readonlyAttribute ?>>
                <option value="">Pilih</option>
                <option value="Laki-laki" <?= ($data['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="Perempuan" <?= ($data['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Tempat Lahir</label>
              <input type="text" name="tempatLahir" value="<?= htmlspecialchars($data['tempat_lahir'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Tanggal Lahir</label>
              <input type="date" name="tanggalLahir" value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Nomor Induk Keluarga</label>
              <input type="text" name="nomorIndukKeluarga" value="<?= htmlspecialchars($data['nik'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
          </div>

          <div class="form-right">
            <div class="form-group">
              <label>Alamat Rumah (Sesuai KTP)</label>
              <input type="text" name="alamatRumah" value="<?= htmlspecialchars($data['alamat_rumah'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>

            <div class="form-group">
              <label>Alamat Domisili</label>
              <input type="text" name="alamatDomisili" value="<?= htmlspecialchars($data['alamat_domisili'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>No. Telepon (WA) Aktif</label>
              <input type="text" name="noTelp" value="<?= htmlspecialchars($data['no_telp'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Agama</label>
              <select name="agama" required <?= $readonlyAttribute ?>>
                <option value="">Pilih</option>
                <option value="Islam" <?= ($data['agama'] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                <option value="Kristen" <?= ($data['agama'] ?? '') == 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                <option value="Katholik" <?= ($data['agama'] ?? '') == 'Katholik' ? 'selected' : '' ?>>Katholik</option>
                <option value="Buddha" <?= ($data['agama'] ?? '') == 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                <option value="Hindu" <?= ($data['agama'] ?? '') == 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                <option value="Khonghucu" <?= ($data['agama'] ?? '') == 'Khonghucu' ? 'selected' : '' ?>>Khonghucu</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Kontak Darurat</label>
              <input type="text" name="kontakDarurat" value="<?= htmlspecialchars($data['kontak_darurat'] ?? '') ?>" required <?= $readonlyAttribute ?>>
            </div>
            
            <div class="form-group">
              <label>Pendidikan Terakhir</label>
              <select name="pendidikanTerakhir" required <?= $readonlyAttribute ?>>
                <option value="">Pilih</option>
                <option value="SMA" <?= ($data['pendidikan_terakhir'] ?? '') == 'SMA' ? 'selected' : '' ?>>SMA</option>
                <option value="SMK" <?= ($data['pendidikan_terakhir'] ?? '') == 'SMK' ? 'selected' : '' ?>>SMK</option>
                <option value="Diploma" <?= ($data['pendidikan_terakhir'] ?? '') == 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                <option value="Sarjana" <?= ($data['pendidikan_terakhir'] ?? '') == 'Sarjana' ? 'selected' : '' ?>>Sarjana</option>
                <option value="Magister" <?= ($data['pendidikan_terakhir'] ?? '') == 'Magister' ? 'selected' : '' ?>>Magister</option>
              </select>
            </div>
          </div>
        </div>

        <div class="action-buttons">
          <?php if ($isEditable): ?>
            <button type="submit" class="btn">Perbarui Data</button>
          <?php else: ?>
            <div class="tooltip-container">
                <button type="button" class="btn btn-disabled" disabled>Perbarui Data</button>
                <span class="tooltip-text">Data tidak dapat diubah setelah dinyatakan diterima atau sedang diproses.</span>
            </div>
          <?php endif; ?>
          
          <a href="dashboardpelamar.php" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>