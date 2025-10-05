<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login sebagai penanggung jawab
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_penanggungjawab.php");
    exit();
}

// Ambil data dari session
$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];
$divisi = $user['divisi'];
$jabatan = $user['jabatan'];
$role = $user['role'];

// Jika ada data yang kosong, ambil dari database
if (empty($divisi) || empty($jabatan)) {
    $query_karyawan = "SELECT divisi, jabatan FROM data_karyawan WHERE kode_karyawan = ?";
    $stmt = mysqli_prepare($conn, $query_karyawan);
    mysqli_stmt_bind_param($stmt, "s", $nik);
    mysqli_stmt_execute($stmt);
    $result_karyawan = mysqli_stmt_get_result($stmt);
    $karyawan_detail = mysqli_fetch_assoc($result_karyawan);
    
    if ($karyawan_detail) {
        $divisi = $karyawan_detail['divisi'];
        $jabatan = $karyawan_detail['jabatan'];
        // Update session
        $_SESSION['user']['divisi'] = $divisi;
        $_SESSION['user']['jabatan'] = $jabatan;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pengajuan KHL - Penanggung Jawab</title>
<style>
  body {
    margin:0;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
    min-height:100vh;
    display:flex;
    flex-direction:column;
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
  .logo img {width:120px;height:50px;object-fit:contain;}

  nav ul {list-style:none;margin:0;padding:0;display:flex;gap:30px;}
  nav li {position:relative;}
  nav a {text-decoration:none;color:#333;font-weight:600;}
  nav li ul {
      display:none;
      position:absolute;
      background:#fff;
      padding:10px 0;
      border-radius:8px;
      box-shadow:0 2px 8px rgba(0,0,0,.15);
      min-width:150px;
  }
  nav li:hover ul {display:block;}
  nav li ul li {padding:5px 20px;}
  nav li ul li a {color:#333;font-weight:400;}

  main {
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
  }
  .form-container {
    width:100%;
    max-width:500px;
    background:rgba(255,255,255,0.95);
    border-radius:15px;
    padding:30px 40px;
    box-shadow:0 0 15px rgba(0,0,0,0.2);
  }
  h2 {
    text-align:center;
    font-size:22px;
    color:#2e1f4f;
    margin-bottom:20px;
  }
  label {display:block;font-weight:600;margin:16px 0 6px;color:#222;}
  input[type="text"],
  input[type="date"],
  select {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    background-color:#f9f9f9;
    box-sizing:border-box;
  }
  button {
    display:block;
    margin-top:25px;
    padding:12px;
    background-color:#4a3f81;
    color:#fff;
    border:none;
    border-radius:8px;
    font-weight:700;
    font-size:15px;
    cursor:pointer;
    width:100%;
  }
  button:hover {background-color:#3a3162;}
  
  .success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
    text-align: center;
  }
  
  .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
    text-align: center;
  }
  
  .user-info {
    background-color: #f0f0f0;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #4a3f81;
  }
  
  .user-info p {
    margin: 8px 0;
    font-size: 14px;
    color: #333;
  }
  
  .user-info strong {
    color: #4a3f81;
  }

  .info-message {
    background-color: #d1ecf1;
    color: #0c5460;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #bee5eb;
    text-align: center;
  }

  .form-note {
    background-color: #fff3cd;
    color: #856404;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
    border: 1px solid #ffeaa7;
    font-size: 14px;
  }

  .required::after {
    content: " *";
    color: #dc3545;
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
      <li><a href="dashboard_penanggungjawab.php">Beranda</a></li>
      <li><a href="#">Cuti ▾</a>
        <ul>
          <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti</a></li>
          <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Divisi</a></li>
          <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
          <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
          <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
        </ul>
      </li>
      <li><a href="#">KHL ▾</a>
        <ul>
          <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL</a></li>
          <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Divisi</a></li>
          <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
          <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
          <li><a href="riwayat_khl_pribadi_penanggungjawab.php">Riwayat KHL Pribadi</a></li>
        </ul>
      </li>
      <li><a href="karyawan_divisi.php">Karyawan Divisi</a></li>
      <li><a href="#">Profil ▾</a>
        <ul>
          <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
          <li><a href="logout2.php">Logout</a></li>
        </ul>
      </li>
    </ul>
  </nav>
</header>

<main>
  <div class="form-container">
    <h2>Pengajuan KHL - Penanggung Jawab</h2>
    
    <!-- Tampilkan pesan sukses/error jika ada -->
    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            $success_message = isset($_GET['message']) ? 
                htmlspecialchars($_GET['message']) : 'Pengajuan KHL berhasil dikirim!';
            echo '<div class="success-message">' . $success_message . '</div>';
        } elseif ($_GET['status'] == 'error') {
            $error_message = isset($_GET['message']) ? 
                htmlspecialchars($_GET['message']) : 'Terjadi kesalahan. Silakan coba lagi.';
            echo '<div class="error-message">' . $error_message . '</div>';
        } elseif ($_GET['status'] == 'info') {
            $info_message = isset($_GET['message']) ? 
                htmlspecialchars($_GET['message']) : 'Informasi';
            echo '<div class="info-message">' . $info_message . '</div>';
        }
    }
    ?>

    <!-- Info Pengguna -->
    <div class="user-info">
      <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($nik); ?></p>
      <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_lengkap); ?></p>
      <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
      <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
      <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $role))); ?></p>
    </div>

    <div class="form-note">
      <strong>Catatan:</strong> Sebagai Penanggung Jawab, pengajuan KHL Anda akan masuk dengan status <strong>Pending</strong> dan membutuhkan persetujuan dari atasan.
    </div>
    
    <form method="post" action="proseskhl_penanggungjawab.php">

      <input type="hidden" name="kode_karyawan" value="<?php echo htmlspecialchars($nik); ?>">
      <input type="hidden" name="divisi" value="<?php echo htmlspecialchars($divisi); ?>">
      <input type="hidden" name="jabatan" value="<?php echo htmlspecialchars($jabatan); ?>">
      <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">

      <label class="required">Proyek</label>
      <input type="text" name="proyek" placeholder="Masukkan nama proyek" required value="<?php echo isset($_POST['proyek']) ? htmlspecialchars($_POST['proyek']) : ''; ?>">

      <label class="required">Tanggal KHL</label>
      <input type="date" name="tanggal_khl" required value="<?php echo isset($_POST['tanggal_khl']) ? htmlspecialchars($_POST['tanggal_khl']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>">

      <label class="required">Jam Mulai Kerja</label>
      <select name="jam_mulai_kerja" required>
        <option value="">Pilih Jam Mulai Kerja</option>
        <option value="07:00:00" <?php echo (isset($_POST['jam_mulai_kerja']) && $_POST['jam_mulai_kerja'] == '07:00:00') ? 'selected' : ''; ?>>07:00</option>
        <option value="08:00:00" <?php echo (isset($_POST['jam_mulai_kerja']) && $_POST['jam_mulai_kerja'] == '08:00:00') ? 'selected' : ''; ?>>08:00</option>
        <option value="09:00:00" <?php echo (isset($_POST['jam_mulai_kerja']) && $_POST['jam_mulai_kerja'] == '09:00:00') ? 'selected' : ''; ?>>09:00</option>
        <option value="10:00:00" <?php echo (isset($_POST['jam_mulai_kerja']) && $_POST['jam_mulai_kerja'] == '10:00:00') ? 'selected' : ''; ?>>10:00</option>
      </select>

      <label class="required">Jam Akhir Kerja</label>
      <select name="jam_akhir_kerja" required>
        <option value="">Pilih Jam Akhir Kerja</option>
        <option value="16:00:00" <?php echo (isset($_POST['jam_akhir_kerja']) && $_POST['jam_akhir_kerja'] == '16:00:00') ? 'selected' : ''; ?>>16:00</option>
        <option value="17:00:00" <?php echo (isset($_POST['jam_akhir_kerja']) && $_POST['jam_akhir_kerja'] == '17:00:00') ? 'selected' : ''; ?>>17:00</option>
        <option value="18:00:00" <?php echo (isset($_POST['jam_akhir_kerja']) && $_POST['jam_akhir_kerja'] == '18:00:00') ? 'selected' : ''; ?>>18:00</option>
        <option value="19:00:00" <?php echo (isset($_POST['jam_akhir_kerja']) && $_POST['jam_akhir_kerja'] == '19:00:00') ? 'selected' : ''; ?>>19:00</option>
      </select>

      <label class="required">Tanggal Cuti KHL</label>
      <input type="date" name="tanggal_cuti_khl" required value="<?php echo isset($_POST['tanggal_cuti_khl']) ? htmlspecialchars($_POST['tanggal_cuti_khl']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>">

      <label class="required">Jam Mulai Cuti KHL</label>
      <select name="jam_mulai_cuti_khl" required>
        <option value="">Pilih Jam Mulai Cuti</option>
        <option value="08:00:00" <?php echo (isset($_POST['jam_mulai_cuti_khl']) && $_POST['jam_mulai_cuti_khl'] == '08:00:00') ? 'selected' : ''; ?>>08:00</option>
        <option value="09:00:00" <?php echo (isset($_POST['jam_mulai_cuti_khl']) && $_POST['jam_mulai_cuti_khl'] == '09:00:00') ? 'selected' : ''; ?>>09:00</option>
        <option value="10:00:00" <?php echo (isset($_POST['jam_mulai_cuti_khl']) && $_POST['jam_mulai_cuti_khl'] == '10:00:00') ? 'selected' : ''; ?>>10:00</option>
      </select>

      <label class="required">Jam Akhir Cuti KHL</label>
      <select name="jam_akhir_cuti_khl" required>
        <option value="">Pilih Jam Akhir Cuti</option>
        <option value="16:00:00" <?php echo (isset($_POST['jam_akhir_cuti_khl']) && $_POST['jam_akhir_cuti_khl'] == '16:00:00') ? 'selected' : ''; ?>>16:00</option>
        <option value="17:00:00" <?php echo (isset($_POST['jam_akhir_cuti_khl']) && $_POST['jam_akhir_cuti_khl'] == '17:00:00') ? 'selected' : ''; ?>>17:00</option>
        <option value="18:00:00" <?php echo (isset($_POST['jam_akhir_cuti_khl']) && $_POST['jam_akhir_cuti_khl'] == '18:00:00') ? 'selected' : ''; ?>>18:00</option>
      </select>

      <button type="submit">Ajukan KHL</button>
    </form>
  </div>
</main>

<?php
// Tutup koneksi database
mysqli_close($conn);
?>
</body>
</html>