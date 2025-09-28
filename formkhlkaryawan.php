<?php
session_start();
// Ambil NIK karyawan dari session login
$nik = isset($_SESSION['nik']) ? $_SESSION['nik'] : '123456789';

// Jika pakai database, bisa query data user di sini
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
  }
  button:hover {background-color:#3a3162;}
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
          <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
        </ul>
      </li>
      <li><a href="#">KHL ▾</a>
        <ul>
          <li><a href="formkhlkaryawan.php">Ajukan KHL</a></li>
          <li><a href="riwayat_khl.php">Riwayat KHL</a></li>
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
    <h2>Pengajuan KHL</h2>
    <form method="post" action="proseskhl_karyawan.php">

      <label>No. Kode Karyawan</label>
      <input type="text" name="nik" value="<?php echo htmlspecialchars($nik); ?>" readonly>

      <label>Proyek</label>
      <select name="proyek" required>
        <option value="">Pilih Proyek</option>
        <option value="Proyek A">Proyek A</option>
        <option value="Proyek B">Proyek B</option>
      </select>

      <label>Tanggal KHL</label>
      <input type="date" name="tanggal_khl" required>

      <label>Jam Mulai Kerja</label>
      <select name="jam_mulai_kerja" required>
        <option value="">Pilih Jam Mulai Kerja</option>
        <option>08:00</option>
        <option>09:00</option>
        <option>10:00</option>
      </select>

      <label>Jam Akhir Kerja</label>
      <select name="jam_akhir_kerja" required>
        <option value="">Pilih Jam Akhir Kerja</option>
        <option>16:00</option>
        <option>17:00</option>
        <option>18:00</option>
      </select>

      <label>Tanggal Cuti KHL</label>
      <input type="date" name="tanggal_cuti_khl" required>

      <label>Jam Mulai Cuti KHL</label>
      <select name="jam_mulai_cuti_khl" required>
        <option value="">Pilih Jam Mulai Cuti</option>
        <option>08:00</option>
        <option>09:00</option>
      </select>

      <label>Jam Akhir Cuti KHL</label>
      <select name="jam_akhir_cuti_khl" required>
        <option value="">Pilih Jam Akhir Cuti</option>
        <option>16:00</option>
        <option>17:00</option>
      </select>

      <button type="submit">Masukkan</button>
    </form>
  </div>
</main>
</body>
</html>
