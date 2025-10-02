<?php
// FILE: persetujuankhl_penanggungjawab.php

$divisi_pj = "Training";

// --- LOGIKA AKSI (diambil dari administrasikhl.php) ---
$pesan_aksi = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    if ($action == 'approve') {
        // Di sini Anda akan menjalankan query UPDATE untuk mengubah status menjadi 'Diterima'
        $pesan_aksi = "Pengajuan KHL ID #$id telah disetujui.";
    } elseif ($action == 'reject') {
        // Di sini Anda akan menjalankan query UPDATE untuk mengubah status menjadi 'Ditolak'
        $pesan_aksi = "Pengajuan KHL ID #$id telah ditolak.";
    }
}


// --- DATA DUMMY (diperkaya dengan detail tambahan untuk Citra) ---
$pengajuan_khl = [
    [
        'id' => 1,
        'kode_karyawan' => 'TRN-002',
        'nama_karyawan' => 'Citra',
        'divisi' => 'Training',
        'proyek' => 'Lembur',
        'tanggal_kerja' => '2025-09-30',
        'jam_mulai_kerja' => '09:00:00',
        'jam_selesai_kerja' => '17:00:00',
        'tanggal_libur' => '2025-10-02',
        'jam_mulai_libur' => '08:00:00',
        'jam_selesai_libur' => '17:00:00',
        'status' => 'Menunggu'
    ],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan KHL Karyawan</title>
    <style>
        /* CSS LENGKAP DIAMBIL DARI administrasikhl.php UNTUK UI YANG SAMA */
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE;
            --accent-color: #4A3F81; 
            --card-bg: #FFFFFF; 
            --text-light: #fff; 
            --text-dark: #333; 
            --shadow-light: rgba(0,0,0,0.1); 
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; }
        header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li a { color: var(--text-dark); font-weight: 400; padding: 5px 20px; white-space: nowrap; }
        main { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); color: var(--text-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        h2 { color: var(--primary-color); font-size: 24px; margin-top: 0; margin-bottom: 20px; }
        .action-message { padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .table-responsive { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; white-space: nowrap; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .karyawan-info { font-weight: 600; }
        .action-links a { text-decoration: none; font-size: 20px; margin: 0 8px; }
        .action-approve { color: #28a745; }
        .action-reject { color: #dc3545; }
        .no-data { text-align: center; padding: 20px; color: #6c757d; }
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
                    <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <div class="card">
        <h2>Persetujuan Pengajuan KHL (Divisi <?= htmlspecialchars($divisi_pj) ?>)</h2>
        
        <?php if ($pesan_aksi): ?>
        <div class="action-message">
            <?= htmlspecialchars($pesan_aksi) ?>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Karyawan</th>
                        <th>Nama Karyawan</th>
                        <th>Divisi</th>
                        <th>Proyek</th>
                        <th>Tanggal Kerja</th>
                        <th>Mulai Kerja</th>
                        <th>Selesai Kerja</th>
                        <th>Libur Pengganti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pengajuan_khl)): ?>
                        <?php foreach($pengajuan_khl as $khl): ?>
                        <tr>
                            <td class="karyawan-info"><?= htmlspecialchars($khl['kode_karyawan']) ?></td>
                            <td class="karyawan-info"><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($khl['divisi']) ?></td>
                            <td><?= htmlspecialchars($khl['proyek']) ?></td>
                            <td><?= date('d/m/Y', strtotime($khl['tanggal_kerja'])) ?></td>
                            <td><?= date('H:i', strtotime($khl['jam_mulai_kerja'])) ?></td>
                            <td><?= date('H:i', strtotime($khl['jam_selesai_kerja'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($khl['tanggal_libur'])) ?></td>
                            <td class="action-links">
                                <a href="?action=approve&id=<?= $khl['id'] ?>" class="action-approve" title="Setujui" onclick="return confirm('Anda yakin ingin menyetujui KHL ini?')">✓</a>
                                <a href="?action=reject&id=<?= $khl['id'] ?>" class="action-reject" title="Tolak" onclick="return confirm('Anda yakin ingin menolak KHL ini?')">✗</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">Tidak ada pengajuan KHL yang menunggu persetujuan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>