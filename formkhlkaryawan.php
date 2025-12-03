<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];
$divisi = $user['divisi'];
$jabatan = $user['jabatan'];

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
<title>Pengajuan KHL</title>
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
  .logo img {width:140px;height:50px;object-fit:contain;}

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
  button:disabled {
    background-color:#cccccc;
    cursor:not-allowed;
  }
  
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

  .holiday-warning {
    background-color: #fff3cd;
    color: #856404;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #ffeaa7;
    text-align: center;
  }

  .required-field::after {
    content: " *";
    color: #e74c3c;
  }

  .time-container {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  
  .time-container select {
    flex: 1;
  }
  
  small {
    display:block;
    margin-top:5px;
    color:#666;
    font-size:12px;
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
  <div class="form-container">
    <h2>Pengajuan KHL</h2>
  
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

    <div class="user-info">
      <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($nik); ?></p>
      <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_lengkap); ?></p>
      <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
      <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
      <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role']))); ?></p>
    </div>
    
    <form method="post" action="proseskhl_karyawan.php" id="formKHL">

      <label>No. Kode Karyawan</label>
      <input type="text" name="nik" value="<?php echo htmlspecialchars($nik); ?>" readonly>

      <label>Nama Karyawan</label>
      <input type="text" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>

      <label>Divisi</label>
      <input type="text" value="<?php echo htmlspecialchars($divisi); ?>" readonly>

      <label class="required-field">Proyek</label>
      <input type="text" name="proyek" placeholder="Masukkan nama proyek" required>

      <label class="required-field">Tanggal KHL</label>
      <input type="date" name="tanggal_khl" id="tanggal_khl" required onchange="validateSelectedDates()">


      <label class="required-field">Jam Kerja</label>
      <div class="time-container">
        <select name="jam_mulai_kerja" id="jam_mulai_kerja" required onchange="validateJamKerja()">
          <option value="">Mulai</option>
          <option value="08:00">08:00</option>
          <option value="09:00">09:00</option>
          <option value="10:00">10:00</option>
        </select>
        <span>-</span>
        <select name="jam_akhir_kerja" id="jam_akhir_kerja" required onchange="validateJamKerja()">
          <option value="">Akhir</option>
          <option value="16:00">16:00</option>
          <option value="17:00">17:00</option>
          <option value="18:00">18:00</option>
        </select>
      </div>
      <div id="jamKerjaError" class="error-message" style="display: none; margin-top: 5px; font-size: 12px; padding: 8px;"></div>

      <label class="required-field">Tanggal Cuti KHL</label>
      <input type="date" name="tanggal_cuti_khl" id="tanggal_cuti_khl" required onchange="validateSelectedDates()">

      <label class="required-field">Jam Cuti KHL</label>
      <div class="time-container">
        <select name="jam_mulai_cuti_khl" id="jam_mulai_cuti_khl" required onchange="validateJamCuti()">
          <option value="">Mulai</option>
          <option value="08:00">08:00</option>
          <option value="09:00">09:00</option>
          <option value="10:00">10:00</option>
        </select>
        <span>-</span>
        <select name="jam_akhir_cuti_khl" id="jam_akhir_cuti_khl" required onchange="validateJamCuti()">
          <option value="">Akhir</option>
          <option value="16:00">16:00</option>
          <option value="17:00">17:00</option>
          <option value="18:00">18:00</option>
        </select>
      </div>
      <div id="jamCutiError" class="error-message" style="display: none; margin-top: 5px; font-size: 12px; padding: 8px;"></div>

      <div id="dateError" class="error-message" style="display: none; margin-top: 10px; margin-bottom: 10px;"></div>

      <button type="submit" id="submitButton">Ajukan KHL</button>
    </form>
  </div>
</main>

<script>
  const fixedHolidays = [
      '01-01', 
      '08-17', 
      '12-25' 
  ];

  function isHoliday(dateString) {
      const date = new Date(dateString);
      const monthDay = `${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
      return fixedHolidays.includes(monthDay);
  }

  function isWeekend(dateString) {
      const date = new Date(dateString);
      const dayOfWeek = date.getDay();
      return dayOfWeek === 0 || dayOfWeek === 6;
  }

  function validateSelectedDates() {
      const tanggalKHL = document.getElementById('tanggal_khl');
      const tanggalCutiKHL = document.getElementById('tanggal_cuti_khl');
      const errorDiv = document.getElementById('dateError');
      const submitButton = document.getElementById('submitButton');
      
      errorDiv.style.display = 'none';
      submitButton.disabled = false;
      
      let hasError = false;
      let errorMessage = '';
      
      if (tanggalCutiKHL.value) {
          if (isHoliday(tanggalCutiKHL.value)) {
              errorMessage += `Tanggal Cuti KHL (${formatDate(tanggalCutiKHL.value)}) adalah hari libur nasional. `;
              hasError = true;
          }
          if (isWeekend(tanggalCutiKHL.value)) {
              errorMessage += `Tanggal Cuti KHL (${formatDate(tanggalCutiKHL.value)}) adalah hari weekend. `;
              hasError = true;
          }
      }
      
      if (tanggalKHL.value && tanggalCutiKHL.value && tanggalKHL.value === tanggalCutiKHL.value) {
          errorMessage += 'Tanggal KHL dan Tanggal Cuti KHL tidak boleh sama. ';
          hasError = true;
      }
      
      if (hasError) {
          showDateError(errorMessage);
          submitButton.disabled = true;
          return false;
      }
      
      return true;
  }

  function validateJamKerja() {
      const jamMulai = document.getElementById('jam_mulai_kerja');
      const jamAkhir = document.getElementById('jam_akhir_kerja');
      const errorDiv = document.getElementById('jamKerjaError');
      
      errorDiv.style.display = 'none';
      
      if (jamMulai.value && jamAkhir.value) {
          const mulai = convertToMinutes(jamMulai.value);
          const akhir = convertToMinutes(jamAkhir.value);
          
          if (akhir <= mulai) {
              errorDiv.textContent = 'Jam akhir kerja harus setelah jam mulai kerja';
              errorDiv.style.display = 'block';
              return false;
          }
          
          const durasi = akhir - mulai;
          if (durasi < 60) {
              errorDiv.textContent = 'Durasi kerja minimal 1 jam';
              errorDiv.style.display = 'block';
              return false;
          }
      }
      
      return true;
  }

  function validateJamCuti() {
      const jamMulai = document.getElementById('jam_mulai_cuti_khl');
      const jamAkhir = document.getElementById('jam_akhir_cuti_khl');
      const errorDiv = document.getElementById('jamCutiError');
      
      errorDiv.style.display = 'none';
      
      if (jamMulai.value && jamAkhir.value) {
          const mulai = convertToMinutes(jamMulai.value);
          const akhir = convertToMinutes(jamAkhir.value);
          
          if (akhir <= mulai) {
              errorDiv.textContent = 'Jam akhir cuti harus setelah jam mulai cuti';
              errorDiv.style.display = 'block';
              return false;
          }
          
          const durasi = akhir - mulai;
          if (durasi < 60) { 
              errorDiv.textContent = 'Durasi cuti minimal 1 jam';
              errorDiv.style.display = 'block';
              return false;
          }
      }
      
      return true;
  }

  function convertToMinutes(timeString) {
      const [hours, minutes] = timeString.split(':').map(Number);
      return hours * 60 + minutes;
  }

  function showDateError(message) {
      const errorDiv = document.getElementById('dateError');
      errorDiv.innerHTML = message;
      errorDiv.style.display = 'block';
  }

  function formatDate(dateString) {
      const date = new Date(dateString);
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return date.toLocaleDateString('id-ID', options);
  }

  document.getElementById('formKHL').addEventListener('submit', function(e) {
      const tanggalKHL = document.getElementById('tanggal_khl');
      const tanggalCutiKHL = document.getElementById('tanggal_cuti_khl');
      
      if (tanggalCutiKHL.value && (isWeekend(tanggalCutiKHL.value) || isHoliday(tanggalCutiKHL.value))) {
          e.preventDefault();
          alert('Tanggal Cuti KHL tidak boleh pada hari weekend atau hari libur nasional');
          tanggalCutiKHL.focus();
          return;
      }
      
      if (!validateJamKerja()) {
          e.preventDefault();
          alert('Jam kerja tidak valid. Periksa kembali jam mulai dan jam akhir kerja.');
          return;
      }
      
      if (!validateJamCuti()) {
          e.preventDefault();
          alert('Jam cuti tidak valid. Periksa kembali jam mulai dan jam akhir cuti.');
          return;
      }
      
      if (tanggalKHL.value && tanggalCutiKHL.value && tanggalKHL.value === tanggalCutiKHL.value) {
          e.preventDefault();
          alert('Tanggal KHL dan Tanggal Cuti KHL tidak boleh sama');
          return;
      }
  });

  document.addEventListener('DOMContentLoaded', function() {
  });
</script>

<?php
mysqli_close($conn);
?>
</body>
</html>