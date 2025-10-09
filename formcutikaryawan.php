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
$divisi = $user['divisi'] ?? ''; 
$jabatan = $user['jabatan'] ?? ''; 

$sisa_cuti_tahunan = 0;
$sisa_cuti_lustrum = 0;

$query_sisa_cuti = "SELECT sisa_cuti_tahunan, sisa_cuti_lustrum FROM data_karyawan WHERE kode_karyawan = ?";
$stmt_sisa = mysqli_prepare($conn, $query_sisa_cuti);

if($stmt_sisa) {
    mysqli_stmt_bind_param($stmt_sisa, "s", $nik);
    mysqli_stmt_execute($stmt_sisa);
    $result_sisa = mysqli_stmt_get_result($stmt_sisa);
    $sisa_cuti_data = mysqli_fetch_assoc($result_sisa);
    
    if ($sisa_cuti_data) {
        $sisa_cuti_tahunan = $sisa_cuti_data['sisa_cuti_tahunan'];
        $sisa_cuti_lustrum = $sisa_cuti_data['sisa_cuti_lustrum'];
    }
    mysqli_stmt_close($stmt_sisa);
}

if (empty($divisi) || empty($jabatan)) {
    $query_karyawan = "SELECT divisi, jabatan FROM data_karyawan WHERE kode_karyawan = ?";
    $stmt = mysqli_prepare($conn, $query_karyawan);
    
    if($stmt) {
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
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pengajuan Cuti</title>
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
  select,
  textarea {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    background-color:#f9f9f9;
    box-sizing:border-box;
  }
  textarea {
    min-height: 80px;
    resize: vertical;
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

  /* Style untuk input conditional */
  .conditional-input {
    display: none;
    margin-top: 10px;
    animation: fadeIn 0.3s ease-in;
  }
  
  .conditional-input.show {
    display: block;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .date-range-container {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  
  .date-range-container input {
    flex: 1;
  }
  
  .date-range-container span {
    font-weight: bold;
    color: #4a3f81;
  }
  
  small {
    display:block;
    margin-top:5px;
    color:#666;
    font-size:12px;
  }
  
  .file-input-container {
    margin-top: 5px;
  }
  
  .file-input-container input[type="file"] {
    padding: 8px;
    border: 1px dashed #ccc;
    background-color: #f9f9f9;
    border-radius: 8px;
    width: 100%;
  }
  
  .max-days-info {
    color: #e74c3c;
    font-weight: 600;
    font-size: 12px;
    margin-top: 5px;
    padding: 5px;
    background-color: #fdf2f2;
    border-radius: 4px;
    border-left: 3px solid #e74c3c;
  }
  
  .sisa-cuti-info {
    color: #2e7d32;
    font-weight: 600;
    font-size: 12px;
    margin-top: 5px;
    padding: 5px;
    background-color: #f1f8e9;
    border-radius: 4px;
    border-left: 3px solid #2e7d32;
  }
  
  .warning-message {
    color: #e65100;
    font-weight: 600;
    font-size: 12px;
    margin-top: 5px;
    padding: 5px;
    background-color: #fff3e0;
    border-radius: 4px;
    border-left: 3px solid #e65100;
  }

  .required-field::after {
    content: " *";
    color: #e74c3c;
  }

  .file-required {
    border: 1px solid #e74c3c !important;
    background-color: #fdf2f2 !important;
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
    <h2>Pengajuan Cuti</h2>
    
    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            $success_message = isset($_GET['message']) ? 
                htmlspecialchars($_GET['message']) : 'Pengajuan cuti berhasil dikirim!';
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
      <p><strong>Sisa Cuti Tahunan:</strong> <?php echo htmlspecialchars($sisa_cuti_tahunan); ?> hari</p>
      <p><strong>Sisa Cuti Lustrum:</strong> <?php echo htmlspecialchars($sisa_cuti_lustrum); ?> hari</p>
    </div>
    
    <form method="post" action="prosescuti_karyawan.php" id="formCuti" enctype="multipart/form-data">
      
      <label>No. Induk Karyawan</label>
      <input type="text" name="nik" value="<?php echo htmlspecialchars($nik); ?>" readonly>

      <label>Nama Karyawan</label>
      <input type="text" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>

      <label>Divisi</label>
      <input type="text" value="<?php echo htmlspecialchars($divisi); ?>" readonly>

      <label class="required-field">Jenis Cuti</label>
      <select name="jenis_cuti" id="jenisCuti" required onchange="toggleConditionalInputs(); checkSisaCuti(); validateFileRequired();">
        <option value="">Pilih Jenis Cuti</option>
        <option value="Tahunan">Cuti Tahunan</option>
        <option value="Lustrum">Cuti Lustrum</option>
        <option value="Khusus">Cuti Khusus</option>
        <option value="Diluar Tanggungan">Cuti Diluar Tanggungan</option>
        <option value="Sakit">Cuti Sakit</option>
        <option value="Ibadah">Cuti Ibadah</option>
      </select>

      <div id="sisaCutiInfo" class="sisa-cuti-info" style="display: none;"></div>
      <div id="warningMessage" class="warning-message" style="display: none;"></div>

      <div id="khususInputContainer" class="conditional-input">
        <label for="jenis_cuti_khusus" class="required-field">Jenis Cuti Khusus</label>
        <select name="jenis_cuti_khusus" id="jenis_cuti_khusus" onchange="updateMaxDaysInfo()">
          <option value="">Pilih Jenis Cuti Khusus</option>
          <option value="Menikah" data-max-days="3">Menikah</option>
          <option value="Pernikahan Anak/Pembatisan Anak/Pengkhitanan Anak" data-max-days="2">Pernikahan Anak/Pembatisan Anak/Pengkhitanan Anak</option>
          <option value="Istri Melahirkan/Keguguran" data-max-days="2">Istri Melahirkan/Keguguran</option>
          <option value="Suami istri, anak/menantu, orangtua/mertua meninggal" data-max-days="2">Suami istri, anak/menantu, orangtua/mertua meninggal</option>
          <option value="Anggota keluarga dalam satu rumah meninggal" data-max-days="1">Anggota keluarga dalam satu rumah meninggal</option>
          <option value="Pemeriksaan Kesehatan/Pindah Rumah" data-max-days="1">Pemeriksaan Kesehatan/Pindah Rumah</option>
        </select>
        <div id="maxDaysInfo" class="max-days-info" style="display: none;"></div>
      </div>

      <div id="sakitInputContainer" class="conditional-input">
        <label for="bukti_surat_dokter" class="required-field">Bukti Surat Keterangan Dokter</label>
        <div class="file-input-container">
          <input type="file" name="bukti_surat_dokter" id="bukti_surat_dokter" accept="image/*,.pdf" onchange="validateFileUpload(this)">
        </div>
        <small>Format: JPG, PNG, atau PDF (maks. 5MB)</small>
        <div id="fileError" class="error-message" style="display: none; margin-top: 5px; font-size: 12px; padding: 8px;"></div>
        <div id="fileSuccess" class="success-message" style="display: none; margin-top: 5px; font-size: 12px; padding: 8px;"></div>
      </div>

      <label class="required-field">Periode Cuti</label>
      <div class="date-range-container">
        <input type="date" name="tanggal_mulai" id="tanggal_mulai" min="<?php echo date('Y-m-d'); ?>" required onchange="updateTanggalAkhir(); validateMaxDays(); checkSisaCuti();">
        <span>s/d</span>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir" min="<?php echo date('Y-m-d'); ?>" required onchange="validateMaxDays(); checkSisaCuti();">
      </div>
      <small>Untuk cuti 1 hari, isi tanggal yang sama pada kedua kolom</small>

      <div id="totalHariInfo" class="sisa-cuti-info" style="display: none;"></div>

      <label class="required-field">Alasan Cuti</label>
      <textarea name="alasan_cuti" id="alasan_cuti" placeholder="Tuliskan alasan cuti Anda..." required></textarea>

      <button type="submit" id="submitButton">Ajukan Cuti</button>
    </form>
  </div>
</main>

<script>
  let currentMaxDays = 0;
  const sisaCutiTahunan = <?php echo $sisa_cuti_tahunan; ?>;
  const sisaCutiLustrum = <?php echo $sisa_cuti_lustrum; ?>;

  function toggleConditionalInputs() {
    const jenisCutiSelect = document.getElementById('jenisCuti');
    const khususInputContainer = document.getElementById('khususInputContainer');
    const khususInput = document.getElementById('jenis_cuti_khusus');
    const sakitInputContainer = document.getElementById('sakitInputContainer');
    const sakitInput = document.getElementById('bukti_surat_dokter');
    const maxDaysInfo = document.getElementById('maxDaysInfo');
    
    // Reset semua input conditional
    khususInputContainer.classList.remove('show');
    khususInput.required = false;
    sakitInputContainer.classList.remove('show');
    sakitInput.required = false;
    maxDaysInfo.style.display = 'none';
    currentMaxDays = 0;
    
    // Reset file validation messages
    document.getElementById('fileError').style.display = 'none';
    document.getElementById('fileSuccess').style.display = 'none';
    
    // Tampilkan input yang sesuai dengan pilihan
    if (jenisCutiSelect.value === 'Khusus') {
      khususInputContainer.classList.add('show');
      khususInput.required = true;
    } else if (jenisCutiSelect.value === 'Sakit') {
      sakitInputContainer.classList.add('show');
      sakitInput.required = true;
      validateFileRequired();
    }
    
    checkSisaCuti();
  }

  function validateFileRequired() {
    const jenisCutiSelect = document.getElementById('jenisCuti');
    const fileInput = document.getElementById('bukti_surat_dokter');
    
    if (jenisCutiSelect.value === 'Sakit') {
      fileInput.required = true;
      fileInput.classList.add('file-required');
    } else {
      fileInput.required = false;
      fileInput.classList.remove('file-required');
    }
  }

  function validateFileUpload(input) {
    const fileError = document.getElementById('fileError');
    const fileSuccess = document.getElementById('fileSuccess');
    
    // Reset messages
    fileError.style.display = 'none';
    fileSuccess.style.display = 'none';
    
    if (input.files.length > 0) {
      const file = input.files[0];
      const fileSize = file.size / 1024 / 1024; // in MB
      const fileType = file.type;
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
      
      // Validasi tipe file
      if (!allowedTypes.includes(fileType)) {
        fileError.textContent = 'Format file tidak didukung. Harap unggah file JPG, PNG, atau PDF.';
        fileError.style.display = 'block';
        input.value = ''; // Clear the file input
        return false;
      }
      
      // Validasi ukuran file (maks 5MB)
      if (fileSize > 5) {
        fileError.textContent = 'Ukuran file terlalu besar. Maksimal 5MB.';
        fileError.style.display = 'block';
        input.value = ''; // Clear the file input
        return false;
      }
      
      // File valid
      fileSuccess.textContent = 'File berhasil diunggah.';
      fileSuccess.style.display = 'block';
      return true;
    }
    
    return false;
  }

  function updateMaxDaysInfo() {
    const khususSelect = document.getElementById('jenis_cuti_khusus');
    const maxDaysInfo = document.getElementById('maxDaysInfo');
    const selectedOption = khususSelect.options[khususSelect.selectedIndex];
    
    if (selectedOption.value !== '') {
      currentMaxDays = parseInt(selectedOption.getAttribute('data-max-days'));
      maxDaysInfo.textContent = `Batas maksimal cuti: ${currentMaxDays} hari`;
      maxDaysInfo.style.display = 'block';
      
      validateMaxDays();
    } else {
      maxDaysInfo.style.display = 'none';
      currentMaxDays = 0;
    }
  }

  function updateTanggalAkhir() {
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalAkhir = document.getElementById('tanggal_akhir');
    
    if (tanggalMulai.value) {
      tanggalAkhir.min = tanggalMulai.value;
      
      if (tanggalAkhir.value && tanggalAkhir.value < tanggalMulai.value) {
        tanggalAkhir.value = tanggalMulai.value;
      }
    }
    
    validateMaxDays();
    checkSisaCuti();
  }

  function validateMaxDays() {
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalAkhir = document.getElementById('tanggal_akhir');
    const maxDaysInfo = document.getElementById('maxDaysInfo');
    
    if (tanggalMulai.value && tanggalAkhir.value && currentMaxDays > 0) {
      const startDate = new Date(tanggalMulai.value);
      const endDate = new Date(tanggalAkhir.value);
      
      const timeDiff = endDate.getTime() - startDate.getTime();
      const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
      
      if (dayDiff > currentMaxDays) {
        maxDaysInfo.innerHTML = `Batas maksimal cuti: ${currentMaxDays} hari <span style="color: #e74c3c;">(Anda mengajukan ${dayDiff} hari - Melebihi batas!)</span>`;
        maxDaysInfo.style.display = 'block';
      } else {
        maxDaysInfo.innerHTML = `Batas maksimal cuti: ${currentMaxDays} hari (Anda mengajukan ${dayDiff} hari)`;
        maxDaysInfo.style.display = 'block';
      }
    }
  }

  function calculateWorkingDays(startDate, endDate) {
    let count = 0;
    const current = new Date(startDate);
    const end = new Date(endDate);
    
    while (current <= end) {
      const dayOfWeek = current.getDay();
      // Hanya hitung hari Senin-Jumat (1-5)
      if (dayOfWeek !== 0 && dayOfWeek !== 6) {
        count++;
      }
      current.setDate(current.getDate() + 1);
    }
    
    return count;
  }

  function checkSisaCuti() {
    const jenisCutiSelect = document.getElementById('jenisCuti');
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalAkhir = document.getElementById('tanggal_akhir');
    const sisaCutiInfo = document.getElementById('sisaCutiInfo');
    const warningMessage = document.getElementById('warningMessage');
    const totalHariInfo = document.getElementById('totalHariInfo');
    const submitButton = document.getElementById('submitButton');
    
    // Reset messages
    sisaCutiInfo.style.display = 'none';
    warningMessage.style.display = 'none';
    totalHariInfo.style.display = 'none';
    submitButton.disabled = false;
    
    if (jenisCutiSelect.value && tanggalMulai.value && tanggalAkhir.value) {
      const startDate = new Date(tanggalMulai.value);
      const endDate = new Date(tanggalAkhir.value);
      
      // Hitung total hari cuti (termasuk weekend)
      const timeDiff = endDate.getTime() - startDate.getTime();
      const totalDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
      
      // Hitung hari kerja (Senin-Jumat)
      const workingDays = calculateWorkingDays(startDate, endDate);
      
      totalHariInfo.innerHTML = `Total hari cuti: ${totalDays} hari (${workingDays} hari kerja)`;
      totalHariInfo.style.display = 'block';
      
      if (jenisCutiSelect.value === 'Tahunan') {
        sisaCutiInfo.innerHTML = `Sisa cuti tahunan Anda: ${sisaCutiTahunan} hari`;
        sisaCutiInfo.style.display = 'block';
        
        if (workingDays > sisaCutiTahunan) {
          warningMessage.innerHTML = `PERINGATAN: Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti tahunan hanya ${sisaCutiTahunan} hari. Pengajuan tidak dapat diproses.`;
          warningMessage.style.display = 'block';
          submitButton.disabled = true;
        } else if (workingDays > 0) {
          sisaCutiInfo.innerHTML += ` - Setelah cuti ini, sisa cuti tahunan: ${sisaCutiTahunan - workingDays} hari`;
        }
        
      } else if (jenisCutiSelect.value === 'Lustrum') {
        sisaCutiInfo.innerHTML = `Sisa cuti lustrum Anda: ${sisaCutiLustrum} hari`;
        sisaCutiInfo.style.display = 'block';
        
        if (workingDays > sisaCutiLustrum) {
          warningMessage.innerHTML = `PERINGATAN: Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti lustrum hanya ${sisaCutiLustrum} hari. Pengajuan tidak dapat diproses.`;
          warningMessage.style.display = 'block';
          submitButton.disabled = true;
        } else if (workingDays > 0) {
          sisaCutiInfo.innerHTML += ` - Setelah cuti ini, sisa cuti lustrum: ${sisaCutiLustrum - workingDays} hari`;
        }
      }
    }
  }

  document.getElementById('formCuti').addEventListener('submit', function(e) {
    const jenisCutiSelect = document.getElementById('jenisCuti');
    const khususInput = document.getElementById('jenis_cuti_khusus');
    const sakitInput = document.getElementById('bukti_surat_dokter');
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalAkhir = document.getElementById('tanggal_akhir');
    
    // Validasi dasar
    if (jenisCutiSelect.value === 'Khusus' && !khususInput.value.trim()) {
      e.preventDefault();
      alert('Silakan pilih jenis cuti khusus');
      khususInput.focus();
      return;
    }
    
    // Validasi khusus untuk cuti sakit - wajib upload file
    if (jenisCutiSelect.value === 'Sakit') {
      if (!sakitInput.value) {
        e.preventDefault();
        alert('Untuk cuti sakit, wajib mengunggah bukti surat keterangan dokter');
        sakitInput.focus();
        return;
      }
      
      // Validasi file yang sudah diupload
      if (!validateFileUpload(sakitInput)) {
        e.preventDefault();
        alert('File surat dokter tidak valid. Pastikan format JPG, PNG, atau PDF dan ukuran maksimal 5MB.');
        return;
      }
    }
    
    if (tanggalMulai.value && tanggalAkhir.value && tanggalAkhir.value < tanggalMulai.value) {
      e.preventDefault();
      alert('Tanggal akhir tidak boleh lebih awal dari tanggal mulai');
      tanggalAkhir.focus();
      return;
    }
    
    // Validasi cuti khusus
    if (jenisCutiSelect.value === 'Khusus' && khususInput.value.trim()) {
      const selectedOption = khususInput.options[khususInput.selectedIndex];
      const maxDays = parseInt(selectedOption.getAttribute('data-max-days'));
      
      if (tanggalMulai.value && tanggalAkhir.value) {
        const startDate = new Date(tanggalMulai.value);
        const endDate = new Date(tanggalAkhir.value);
        const timeDiff = endDate.getTime() - startDate.getTime();
        const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
        
        if (dayDiff > maxDays) {
          e.preventDefault();
          alert(`Jumlah hari cuti untuk ${selectedOption.text} maksimal ${maxDays} hari. Anda mengajukan ${dayDiff} hari.`);
          return;
        }
      }
    }
    
    // Validasi sisa cuti tahunan dan lustrum
    if (jenisCutiSelect.value === 'Tahunan' || jenisCutiSelect.value === 'Lustrum') {
      if (tanggalMulai.value && tanggalAkhir.value) {
        const startDate = new Date(tanggalMulai.value);
        const endDate = new Date(tanggalAkhir.value);
        const workingDays = calculateWorkingDays(startDate, endDate);
        
        if (jenisCutiSelect.value === 'Tahunan' && workingDays > sisaCutiTahunan) {
          e.preventDefault();
          alert(`Sisa cuti tahunan Anda tidak mencukupi. Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti hanya ${sisaCutiTahunan} hari.`);
          return;
        }
        
        if (jenisCutiSelect.value === 'Lustrum' && workingDays > sisaCutiLustrum) {
          e.preventDefault();
          alert(`Sisa cuti lustrum Anda tidak mencukupi. Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti hanya ${sisaCutiLustrum} hari.`);
          return;
        }
      }
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    toggleConditionalInputs();
  });
</script>

<?php
// Tutup koneksi database
if(isset($conn)) {
    mysqli_close($conn);
}
?>
</body>
</html>