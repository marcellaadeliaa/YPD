<?php
session_start();

// sementara untuk contoh tanpa login
if (!isset($_SESSION['kode_karyawan'])) {
    $_SESSION['kode_karyawan'] = '123456789';
}

// simpan data ke session saat submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $_SESSION['data_pribadi'] = [
        'nama'    => $_POST['nama'] ?? '',
        'divisi'  => $_POST['divisi'] ?? '',
        'role'    => $_POST['role'] ?? '',
        'telepon' => $_POST['telepon'] ?? '',
        'email'   => $_POST['email'] ?? ''
    ];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        unset($_SESSION['data_pribadi']);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    if (isset($_POST['to_dashboard'])) {
        header("Location: dashboardkaryawan.php");
        exit;
    }
}



$data  = $_SESSION['data_pribadi'] ?? null;
$kodeKaryawan = $_SESSION['kode_karyawan'];
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
  max-width:500px;
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
label{display:block;font-weight:600;margin:18px 0 6px;color:#222;}
input[type="text"],
input[type="tel"],
input[type="email"],
select{
  width:100%;
  padding:10px;
  border:1px solid #ccc;
  border-radius:8px;
  background:#f9f9f9;
}
button{
  display:block;
  margin-top:25px;
  padding:12px;
  background:#4a3f81;
  color:#fff;
  border:none;
  border-radius:8px;
  font-weight:700;
  font-size:15px;
  cursor:pointer;
}
button:hover{background:#3a3162;}
.list-data p{margin:8px 0;font-weight:500;}
/* Responsive */
@media(max-width:768px){
  header{flex-direction:column;align-items:flex-start;}
  nav ul{flex-direction:column;gap:10px;}
  nav li ul{position:relative;border:none;box-shadow:none;}
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
          <li><a href="formcutikaryawan.php">Ajukan Cuti</a></li>
          <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
        </ul>
      </li>
      <li><a href="#">KHL ▾</a>
        <ul>
          <li><a href="formcutikhl.php">Ajukan Cuti</a></li>
          <li><a href="riwayat_khl.php">Riwayat Cuti</a></li>
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
  <div class="card">
    <?php if(!$data): ?>
      <h2>Lengkapi Data Pribadi</h2>
      <form method="post">
        <label>No. Induk Karyawan</label>
        <input type="text" name="kode" value="<?= htmlspecialchars($kodeKaryawan) ?>" readonly>

        <label>Nama Karyawan</label>
        <input type="text" name="nama" required>

        <label>Divisi/Bagian</label>
        <select name="divisi" required>
          <option value="">Pilih Divisi</option>
          <option value="HRD">HRD</option>
          <option value="IT">IT</option>
          <option value="Finance">Finance</option>
        </select>

        <label>Role</label>
        <select name="role" required>
          <option value="">Pilih Role</option>
          <option value="Staff">Staff</option>
          <option value="Manager">Manager</option>
        </select>

        <label>No. Telepon</label>
        <input type="tel" name="telepon" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <button type="submit" name="save">Simpan</button>
      </form>
    <?php else: ?>
      <h2>Data Pribadi Anda</h2>
      <div class="list-data">
        <p><strong>No. Induk Karyawan:</strong> <?= htmlspecialchars($kodeKaryawan) ?></p>
        <p><strong>Nama:</strong> <?= htmlspecialchars($data['nama']) ?></p>
        <p><strong>Divisi:</strong> <?= htmlspecialchars($data['divisi']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($data['role']) ?></p>
        <p><strong>Telepon:</strong> <?= htmlspecialchars($data['telepon']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
      </div>
      <form method="post">
        <button type="submit" name="reset">Edit Data</button>
      </form>

      <form action="dashboardkaryawan.php" method="get">
        <button type="submit">Kembali ke Dashboard</button>
      </form>

    <?php endif; ?>
  </div>
</main>

</body>
</html>
