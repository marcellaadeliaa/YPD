<?php
session_start();

// Sertakan file koneksi database Anda di sini
// contoh: include 'config/koneksi.php';

// Data Penanggung Jawab yang sedang login (seharusnya dari session setelah login)
$user_data = [
    'nik' => 'PJ002',       // Contoh NIK untuk Konsultasi
    'nama' => 'Budi',       // Contoh Nama untuk Konsultasi
    'divisi' => 'Konsultasi'
];

// Data riwayat sengaja dikosongkan sesuai permintaan
$filtered_data = [];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Cuti Pribadi - Penanggung Jawab Konsultasi</title>
<style>
    /* CSS Styles (Sama seperti file sebelumnya, tidak perlu diubah) */
    :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); }
    body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; color: var(--text-color-light); padding-bottom: 40px; }
    header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
    .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
    nav li { position: relative; }
    nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
    nav a:hover { color: var(--accent-color); }
    nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
    nav li:hover > ul { display: block; }
    nav li ul li { padding: 5px 20px; }
    nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    /* Menghapus margin-bottom dari h1 dan p yang sudah tidak ada */
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); color: #333; }
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; text-decoration: none; display: inline-block; text-align: center; }
    .btn-ajukan { background-color: #2ecc71; margin-bottom: 20px; }
    .btn-ajukan:hover { background-color: #27ae60; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    .user-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4a3f81; }
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
            <li><a href="dashboardpenanggungjawab_konsultasi.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab_konsultasi.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab_konsultasi.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab_konsultasi.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab_konsultasi.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab_konsultasi.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab_konsultasi.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab_konsultasi.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab_konsultasi.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab_konsultasi.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab_konsultasi.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi_konsultasi.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab_konsultasi.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <div class="card">
        <h2 class="page-title">Riwayat Cuti Pribadi</h2>
        
        <div class="user-info">
            <strong>Informasi Anda:</strong><br>
            NIK: <?php echo htmlspecialchars($user_data['nik']); ?> | 
            Nama: <?php echo htmlspecialchars($user_data['nama']); ?> | 
            Divisi: <?php echo htmlspecialchars($user_data['divisi']); ?>
        </div>
        
        <a href="pengajuancuti_penanggungjawab_konsultasi.php" class="btn btn-ajukan">+ Ajukan Cuti Baru</a>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal Cuti</th>
                    <th>Jenis Cuti</th>
                    <th>Status</th>
                    <th>Persetujuan Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_data)): ?>
                    <?php // Logika ini akan dilewati karena $filtered_data kosong ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">Tidak ada data cuti yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>