<?php
session_start();

// --- KONEKSI DATABASE ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ypd_ibd';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


// Ambil data direktur dari database
$id_karyawan = 1; // sementara hardcode, bisa diganti $_SESSION['id_karyawan']
$data = null;
$sql = "SELECT kode_karyawan, nama_lengkap, jabatan, divisi, no_telp, email 
        FROM data_karyawan WHERE id_karyawan = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id_karyawan);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil Direktur</title>
<style>
    :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); --shadow-strong: rgba(0,0,0,0.25); }
    body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; color: var(--text-color-dark); display:flex; flex-direction:column; }
    header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent-color); flex-wrap: wrap; box-shadow: 0 4px 15px var(--shadow-light); }
    .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
    nav li { position: relative; }
    nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; transition: color 0.3s ease; }
    nav a:hover { color: var(--accent-color); }
    nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
    nav li:hover > ul { display: block; }
    nav li ul li { padding: 5px 20px; }
    nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; }
    main { flex:1; display:flex; justify-content:center; align-items:flex-start; padding:40px 20px; }
    .card { width:100%; max-width:500px; background:var(--card-bg); border-radius:15px; padding:30px 40px; box-shadow:0 0 15px rgba(0,0,0,0.2); }
    h2 { text-align: center; font-size: 24px; margin-bottom: 25px; color: var(--text-color-dark); border-bottom: 2px solid #eee; padding-bottom: 10px; }
    .list-data p { margin: 12px 0; font-size: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; }
    .list-data p:last-child { border-bottom: none; }
    .list-data p strong { display: block; color: #555; margin-bottom: 5px; font-size: 14px; }
    .btn-kembali { display: block; width: 100%; margin-top: 25px; padding: 12px; background: var(--accent-color); color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-kembali:hover { background: #3a3162; }
    @media(max-width:768px){ header{flex-direction: column; align-items: flex-start;} nav ul{flex-direction: column; gap: 10px; width: 100%; margin-top: 15px;} nav li ul { position: static; border: none; box-shadow: none; padding-left: 20px; } }
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
            <li><a href="dashboarddirektur.php">Beranda</a></li> 
            <li><a href="#">Cuti ▾</a> 
                <ul> 
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li> 
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li> 
                </ul> 
            </li> 
            <li><a href="#">KHL ▾</a> 
                <ul> 
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li> 
                    <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li> 
                </ul> 
            </li> 
            <li><a href="#">Karyawan ▾</a> 
                <ul> 
                    <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li> 
                </ul> 
            </li> 
            <li><a href="#">Pelamar ▾</a> 
                <ul> 
                    <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li> 
                </ul> 
            </li>
            <li><a href="#">Profil ▾</a> 
                <ul> 
                    <li><a href="profil_direktur.php">Profil Saya</a></li> 
                    <li><a href="logout2.php">Logout</a></li> 
                </ul> 
            </li> 
        </ul> 
    </nav>
</header>

<main>
  <div class="card">
      <h2>Profil Saya</h2>
      <?php if($data): ?>
      <div class="list-data">
        <p><strong>Kode Karyawan:</strong> <?= htmlspecialchars($data['kode_karyawan']) ?></p>
        <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
        <p><strong>Jabatan:</strong> <?= htmlspecialchars($data['jabatan']) ?></p>
        <p><strong>Divisi:</strong> <?= htmlspecialchars($data['divisi']) ?></p>
        <p><strong>No. Telepon:</strong> <?= htmlspecialchars($data['no_telp']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
      </div>
      <?php else: ?>
        <p style="text-align:center;">Data profil tidak ditemukan.</p>
      <?php endif; ?>
      <a href="dashboarddirektur.php" class="btn-kembali">Kembali ke Dashboard</a>
  </div>
</main>

</body>
</html>
