<?php
// FILE: persetujuancuti_penanggungjawab_konsultasi.php

$divisi_pj = "konsultasi"; // Diubah menjadi 'konsultasi'

// --- LOGIKA AKSI ---
$pesan_aksi = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    if ($action == 'approve') {
        $pesan_aksi = "Pengajuan Cuti ID #$id telah disetujui.";
    } elseif ($action == 'reject') {
        $pesan_aksi = "Pengajuan Cuti ID #$id telah ditolak.";
    }
}

// --- DATA DUMMY untuk Divisi Konsultasi ---
$pengajuan_cuti = [
    [
        'id' => 4,
        'kode_karyawan' => 'KSL-001',
        'nama_karyawan' => 'Gita',
        'divisi' => 'Konsultasi',
        'jenis_cuti' => 'Cuti Tahunan',
        'mulai_cuti' => '2025-10-20',
        'selesai_cuti' => '2025-10-22',
        'keterangan' => 'Liburan keluarga',
        'status' => 'Menunggu'
    ],
    [
        'id' => 5,
        'kode_karyawan' => 'KSL-004',
        'nama_karyawan' => 'Indra',
        'divisi' => 'Konsultasi',
        'jenis_cuti' => 'Cuti Sakit',
        'mulai_cuti' => '2025-10-10',
        'selesai_cuti' => '2025-10-10',
        'keterangan' => 'Pemeriksaan kesehatan',
        'status' => 'Menunggu'
    ],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan Cuti Karyawan - Konsultasi</title>
    <style>
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
        .status { padding: 5px 12px; border-radius: 15px; font-weight: 600; font-size: 12px; background-color: #fff3cd; color: #856404; }
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
        <h2>Persetujuan Pengajuan Cuti (Divisi <?= htmlspecialchars(ucfirst($divisi_pj)) ?>)</h2>
        
        <?php if ($pesan_aksi): ?>
        <div class="action-message">
            <?= htmlspecialchars($pesan_aksi) ?>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Nama Karyawan</th>
                        <th>Jenis Cuti</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pengajuan_cuti)): ?>
                        <?php foreach($pengajuan_cuti as $cuti): ?>
                        <tr>
                            <td><?= htmlspecialchars($cuti['id']) ?></td>
                            <td><?= htmlspecialchars($cuti['kode_karyawan']) ?></td>
                            <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                            <td><?= date('d/m/Y', strtotime($cuti['mulai_cuti'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($cuti['selesai_cuti'])) ?></td>
                            <td><?= htmlspecialchars($cuti['keterangan']) ?></td>
                            <td>
                                <span class="status"><?= htmlspecialchars($cuti['status']) ?></span>
                            </td>
                            <td class="action-links">
                                <a href="?action=approve&id=<?= $cuti['id'] ?>" class="action-approve" title="Setujui" onclick="return confirm('Anda yakin ingin menyetujui cuti ini?')">✓</a>
                                <a href="?action=reject&id=<?= $cuti['id'] ?>" class="action-reject" title="Tolak" onclick="return confirm('Anda yakin ingin menolak cuti ini?')">✗</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">Tidak ada pengajuan cuti yang menunggu persetujuan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>