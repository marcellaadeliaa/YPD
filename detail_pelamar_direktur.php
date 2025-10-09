<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Pelamar tidak valid.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM data_pelamar WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data pelamar tidak ditemukan.");
}

$pelamar = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pelamar - Direktur</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; flex-wrap:wrap; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 140px; height: 50px; object-fit: contain; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; align-items: center; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
    nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:960px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { text-align:left; font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 25px; }
    .page-title { font-size: 24px; font-weight: 600; color: #1E105E; padding-bottom: 10px; }
    .btn-kembali { display: inline-block; background: #6c757d; color: #fff; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: 600; }
    
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 40px; }
    .detail-group { margin-bottom: 15px; }
    .detail-group label { display: block; font-weight: 600; font-size: 14px; color: #555; margin-bottom: 5px; }
    .detail-group p { margin: 0; font-size: 16px; color: #000; }
    .detail-group a { font-size: 16px; color: #0056b3; text-decoration:none; }
    .detail-group a:hover { text-decoration:underline; }
    .section-title { font-size: 20px; grid-column: 1 / -1; margin-top: 20px; color: #4a3f81; border-top: 1px solid #ddd; padding-top: 20px; }
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
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Semua Cuti</a></li>
                    <li><a href="riwayat_cuti_pribadi_direktur.php">Riwayat Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_direktur.php">Kalender Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat Semua KHL</a></li>
                    <li><a href="riwayat_khl_pribadi_direktur.php">Riwayat KHL Pribadi</a></li>
                    <li><a href="kalender_khl_direktur.php">Kalender KHL</a></li>
                </ul>
            </li>
            <li><a href="#">Karyawan ▾</a>
                <ul>
                    <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
                </ul>
            </li>
            <li><a href="#">Pelamar ▾</a>
                <ul>
                    <li><a href="riwayat_pelamar_direktur.php">Riwayat Pelamar</a></li>
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
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?>!</h1>
    <p class="admin-title">Direktur</p>

    <div class="card">
        <div class="page-header">
            <h2 class="page-title">Detail Data Pelamar</h2>
            <a href="riwayat_pelamar_direktur.php" class="btn-kembali">← Kembali</a>
        </div>

        <div class="detail-grid">
            <div class="detail-group"><label>Nama Lengkap</label><p><?= htmlspecialchars($pelamar['nama_lengkap']) ?></p></div>
            <div class="detail-group"><label>Posisi Dilamar</label><p><?= htmlspecialchars($pelamar['posisi_dilamar']) ?></p></div>
            <div class="detail-group"><label>Nomor Induk Kependudukan (NIK)</label><p><?= htmlspecialchars($pelamar['nik']) ?></p></div>
            <div class="detail-group"><label>Jenis Kelamin</label><p><?= htmlspecialchars($pelamar['jenis_kelamin']) ?></p></div>
            <div class="detail-group"><label>Tempat, Tanggal Lahir</label><p><?= htmlspecialchars($pelamar['tempat_lahir']) ?>, <?= date('d F Y', strtotime($pelamar['tanggal_lahir'])) ?></p></div>
            <div class="detail-group"><label>Agama</label><p><?= htmlspecialchars($pelamar['agama']) ?></p></div>
            <div class="detail-group"><label>Pendidikan Terakhir</label><p><?= htmlspecialchars($pelamar['pendidikan_terakhir']) ?></p></div>
            <div class="detail-group"><label>Alamat Rumah (Sesuai KTP)</label><p><?= htmlspecialchars($pelamar['alamat_rumah']) ?></p></div>
            <div class="detail-group"><label>Alamat Domisili</label><p><?= htmlspecialchars($pelamar['alamat_domisili']) ?></p></div>
            <div class="detail-group"><label>Email</label><p><?= htmlspecialchars($pelamar['email']) ?></p></div>
            <div class="detail-group"><label>No. Telepon</label><p><?= htmlspecialchars($pelamar['no_telp']) ?></p></div>
            <div class="detail-group"><label>Kontak Darurat</label><p><?= htmlspecialchars($pelamar['kontak_darurat']) ?></p></div>
            
            <h3 class="section-title">Berkas Terlampir</h3>
            <div class="detail-group"><label>Surat Lamaran</label><a href="<?= htmlspecialchars($pelamar['surat_lamaran']) ?>" target="_blank">Lihat Berkas</a></div>
            <div class="detail-group"><label>CV</label><a href="<?= htmlspecialchars($pelamar['cv']) ?>" target="_blank">Lihat Berkas</a></div>
            <div class="detail-group"><label>Pas Foto</label><a href="<?= htmlspecialchars($pelamar['photo_formal']) ?>" target="_blank">Lihat Foto</a></div>
            <div class="detail-group"><label>Ijazah & Transkrip</label><a href="<?= htmlspecialchars($pelamar['ijazah_transkrip']) ?>" target="_blank">Lihat Berkas</a></div>
            <div class="detail-group"><label>KTP</label>
                <?php if (!empty($pelamar['ktp'])): ?>
                    <a href="<?= htmlspecialchars($pelamar['ktp']) ?>" target="_blank">Lihat Berkas</a>
                <?php else: ?>
                    <p>Tidak diunggah</p>
                <?php endif; ?>
            </div>
            <div class="detail-group"><label>Berkas Pendukung</label><a href="<?= htmlspecialchars($pelamar['berkas_pendukung']) ?>" target="_blank">Lihat Berkas</a></div>
        </div>
    </div>
</main>

</body>
</html>