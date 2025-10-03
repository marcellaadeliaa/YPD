<?php
session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }
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
    /* Satu gradasi penuh dari atas ke bawah */
    background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
    min-height:100vh;
    display:flex;
    flex-direction:column;
  }
  header {
    background:rgba(255, 255, 255, 1);
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:2px solid #34377c;
    backdrop-filter: blur(5px);
  }
  .logo {display:flex;align-items:center;gap:16px;font-weight:500;font-size:20px;color:#2e1f4f;}
  .logo img {width:130px;height:50px;object-fit:contain;}

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
    font-size:24px;
    color:#2e1f4f;
    margin-bottom:25px;
    border-bottom:2px solid #eee;
    padding-bottom:10px;
  }
  label {display:block;font-weight:600;margin:18px 0 6px;color:#222;}
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
  
  /* Style untuk input manual yang tersembunyi */
  .manual-input {
    display: none;
    margin-top: 10px;
    animation: fadeIn 0.3s ease-in;
  }
  
  .manual-input.show {
    display: block;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
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
      <li><a href="dashboard.php">Beranda</a></li>
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
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </li>
    </ul>
  </nav>
</header>

<main>
  <div class="form-container">
    <h2>Pengajuan Cuti</h2>
    <form method="post" action="prosescuti_karyawan.php" id="formCuti">
      <label>No. Induk Karyawan</label>
      <input type="text" name="nik" value="YPD002" readonly>

      <label>Jenis Cuti</label>
      <select name="jenis_cuti" id="jenisCuti" required onchange="toggleManualInput()">
        <option value="">Pilih Jenis Cuti</option>
        <option value="Tahunan">Cuti Tahunan</option>
        <option value="Lustrum">Cuti Lustrum</option>
        <option value="Khusus">Cuti Khusus</option>
        <option value="Sakit">Cuti Diluar Tanggungan</option>
        <option value="Sakit">Cuti Sakit</option>
        <option value="Ibadah">Cuti Ibadah</option>
        <option value="Lainnya">Lainnya</option>
      </select>

      <!-- Input manual untuk jenis cuti lainnya -->
      <div id="manualInputContainer" class="manual-input">
        <label for="jenis_cuti_manual">Tulis Jenis Cuti</label>
        <input type="text" name="jenis_cuti_manual" id="jenis_cuti_manual" placeholder="Masukkan jenis cuti lainnya...">
      </div>

      <label>Tanggal Cuti</label>
      <input type="date" name="tanggal_cuti" min="<?php echo date('Y-m-d'); ?>" required>

      <button type="submit">Masukkan</button>
    </form>
  </div>
</main>

<script>
  function toggleManualInput() {
    const jenisCutiSelect = document.getElementById('jenisCuti');
    const manualInputContainer = document.getElementById('manualInputContainer');
    const manualInput = document.getElementById('jenis_cuti_manual');
    
    if (jenisCutiSelect.value === 'Lainnya') {
      manualInputContainer.classList.add('show');
      manualInput.required = true;
    } else {
      manualInputContainer.classList.remove('show');
      manualInput.required = false;
      manualInput.value = ''; // Clear input ketika tidak dipakai
    }
  }

  // Validasi form sebelum submit
  document.getElementById('formCuti').addEventListener('submit', function(e) {
    const jenisCutiSelect = document.getElementById('jenisCuti');
    const manualInput = document.getElementById('jenis_cuti_manual');
    
    if (jenisCutiSelect.value === 'Lainnya' && !manualInput.value.trim()) {
      e.preventDefault();
      alert('Silakan tulis jenis cuti lainnya');
      manualInput.focus();
    }
  });

  // Inisialisasi saat page load
  document.addEventListener('DOMContentLoaded', function() {
    toggleManualInput();
  });
</script>
</body>
</html>