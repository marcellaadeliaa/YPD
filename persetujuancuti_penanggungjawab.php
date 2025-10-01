<?php
// FILE: persetujuancuti_penanggungjawab.php
$divisi_pj = "IT";
$pengajuan_cuti = [
    ['id' => 1, 'nama_karyawan' => 'Andi', 'tgl_mulai' => '2025-10-05', 'tgl_selesai' => '2025-10-06', 'jumlah' => 2, 'keterangan' => 'Acara keluarga di luar kota'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan Cuti Karyawan</title>
    <style>
        :root { --primary-color: #1E105E; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); --success-color: #28a745; --danger-color: #dc3545; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; padding-bottom: 40px; }
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
        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); color: var(--text-color-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 5px 20px var(--shadow-light); }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background-color: #f8f9fa; }
        .action-buttons { display: flex; gap: 10px; }
        .btn-action { color: #fff; padding: 8px 15px; border-radius: 6px; text-decoration: none; }
        .btn-success { background-color: var(--success-color); }
        .btn-danger { background-color: var(--danger-color); }
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
            <li><a href="dashboardpenanggungjawab.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
    <ul>
        <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
        <li><a href="#">Profil ▾</a>
    <ul>
        <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
        <li><a href="logout2.php">Logout</a></li>
    </ul>
</li>
    </ul>
</li>
        </ul>
    </nav>
</header>
<main>
    <div class="card">
        <h2>Persetujuan Pengajuan Cuti (Divisi <?= $divisi_pj ?>)</h2>
        <table class="data-table">
            <thead><tr><th>Nama Karyawan</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Jumlah</th><th>Keterangan</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if (!empty($pengajuan_cuti)): foreach($pengajuan_cuti as $cuti): ?>
                    <tr>
                        <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                        <td><?= date('d-m-Y', strtotime($cuti['tgl_mulai'])) ?></td>
                        <td><?= date('d-m-Y', strtotime($cuti['tgl_selesai'])) ?></td>
                        <td><?= htmlspecialchars($cuti['jumlah']) ?></td>
                        <td><?= htmlspecialchars($cuti['keterangan']) ?></td>
                        <td class="action-buttons">
                            <a href="#" class="btn-action btn-success">Setujui</a>
                            <a href="#" class="btn-action btn-danger">Tolak</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align:center;">Tidak ada pengajuan cuti.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>