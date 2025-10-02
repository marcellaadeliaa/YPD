<?php
// FILE: persetujuancuti_penanggungjawab.php

$divisi_pj = "Training";

// --- LOGIKA AKSI (diambil dari administrrasicuti.php) ---
$pesan_aksi = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    if ($action == 'approve') {
        // Di sini Anda akan menjalankan query UPDATE untuk mengubah status menjadi 'Diterima'
        $pesan_aksi = "Pengajuan Cuti ID #$id telah disetujui.";
    } elseif ($action == 'reject') {
        // Di sini Anda akan menjalankan query UPDATE untuk mengubah status menjadi 'Ditolak'
        $pesan_aksi = "Pengajuan Cuti ID #$id telah ditolak.";
    }
    // Untuk demo, kita tidak akan mengubah data aslinya
}


// --- DATA DUMMY (diperkaya dengan detail tambahan) ---
// Fani adalah satu-satunya dengan status Menunggu, sesuai data kalender
$pengajuan_cuti = [
    [
        'id' => 3,
        'kode_karyawan' => 'TRN-003',
        'nama_karyawan' => 'Fani',
        'divisi' => 'Training',
        'jenis_cuti' => 'Cuti Alasan Penting',
        'mulai_cuti' => '2025-10-15',
        'selesai_cuti' => '2025-10-16',
        'keterangan' => 'Keperluan keluarga mendadak',
        'status' => 'Menunggu'
    ],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan Cuti Karyawan</title>
    <style>
        /* CSS LENGKAP DIAMBIL DARI administrasicuti.php UNTUK UI YANG SAMA */
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE;
            --accent-color: #4A3F81; 
            --card-bg: #FFFFFF; 
            --text-light: #fff; 
            --text-dark: #333; 
            --status-pending: #fff3cd;
            --status-pending-text: #856404;
            --status-approved: #d4edda;
            --status-approved-text: #155724;
            --status-rejected: #f8d7da;
            --status-rejected-text: #721c24;
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
        .status { padding: 5px 12px; border-radius: 15px; font-weight: 600; font-size: 12px; }
        .status-pending { background-color: var(--status-pending); color: var(--status-pending-text); }
        .status-approved { background-color: var(--status-approved); color: var(--status-approved-text); }
        .status-rejected { background-color: var(--status-rejected); color: var(--status-rejected-text); }
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
        <h2>Persetujuan Pengajuan Cuti (Divisi <?= htmlspecialchars($divisi_pj) ?>)</h2>
        
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
                                <span class="status status-pending"><?= htmlspecialchars($cuti['status']) ?></span>
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