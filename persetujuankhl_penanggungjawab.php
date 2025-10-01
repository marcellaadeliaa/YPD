<?php
// FILE: persetujuankhl_penanggungjawab.php
$divisi_pj = "IT";
$pengajuan_khl = [
    ['id' => 1, 'nama_karyawan' => 'Citra', 'tgl_khl' => '2025-10-12', 'jenis_khl' => 'Lembur', 'keterangan' => 'Menyelesaikan migrasi server'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan KHL Karyawan</title>
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
            <li><a href="logout2.php">Logout</a></li>
        </ul>
    </nav>
</header>
<main>
    <div class="card">
        <h2>Persetujuan Pengajuan KHL (Divisi <?= $divisi_pj ?>)</h2>
        <table class="data-table">
            <thead><tr><th>Nama Karyawan</th><th>Tanggal KHL</th><th>Jenis</th><th>Keterangan</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if (!empty($pengajuan_khl)): foreach($pengajuan_khl as $khl): ?>
                    <tr>
                        <td><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                        <td><?= date('d-m-Y', strtotime($khl['tgl_khl'])) ?></td>
                        <td><?= htmlspecialchars($khl['jenis_khl']) ?></td>
                        <td><?= htmlspecialchars($khl['keterangan']) ?></td>
                        <td class="action-buttons">
                            <a href="#" class="btn-action btn-success">Setujui</a>
                            <a href="#" class="btn-action btn-danger">Tolak</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center;">Tidak ada pengajuan KHL.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>