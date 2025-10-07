<?php
session_start();
require 'config.php';

// Cek apakah user sudah login sebagai penanggung jawab
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$kode_karyawan = $user['kode_karyawan'];
$nama_pj = $user['nama_lengkap'];
$divisi_pj = $user['divisi'];
$jabatan = "Penanggung Jawab Divisi " . $divisi_pj;

// --- KONFIGURASI PAGINASI ---
$limit = 5; // Hanya tampilkan 5 data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// --- QUERY UTAMA DENGAN LIMIT & OFFSET ---
$sql = "SELECT * FROM data_pengajuan_khl 
        WHERE kode_karyawan = ? 
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $kode_karyawan, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$riwayat_khl = [];
while ($row = $result->fetch_assoc()) {
    $riwayat_khl[] = $row;
}

// --- QUERY UNTUK TOTAL DATA (untuk pagination) ---
$sql_total = "SELECT COUNT(*) as total FROM data_pengajuan_khl WHERE kode_karyawan = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("s", $kode_karyawan);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_data = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// --- HITUNG STATISTIK (dari semua data) ---
$sql_stats = "SELECT 
              COUNT(*) as total,
              SUM(CASE WHEN status_khl = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
              SUM(CASE WHEN status_khl = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
              SUM(CASE WHEN status_khl = 'pending' THEN 1 ELSE 0 END) as pending
              FROM data_pengajuan_khl 
              WHERE kode_karyawan = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("s", $kode_karyawan);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();
$stats = $result_stats->fetch_assoc();

$total_pengajuan = $stats['total'];
$disetujui_count = $stats['disetujui'];
$ditolak_count = $stats['ditolak'];
$pending_count = $stats['pending'];

$stmt->close();
$stmt_total->close();
$stmt_stats->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat KHL Pribadi - Penanggung Jawab</title>
    <style>
        .heading-section h1 { font-size: 2.5rem; margin: 0; color: #fff;}
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE; 
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF; 
            --text-color-light: #fff; 
            --text-color-dark: #2e1f4f; 
            --shadow-light: rgba(0,0,0,0.15);
            --status-pending: #ffc107;
            --status-disetujui: #28a745;
            --status-ditolak: #dc3545;
        }
        
        body { 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh; 
            color: var(--text-color-light); 
        }
        
        header { 
            background: var(--card-bg); 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 15px var(--shadow-light); 
        }
        
        .logo { 
            display: flex; 
            align-items: center; 
            gap: 16px; 
            font-weight: 500; 
            font-size: 20px; 
            color: var(--text-color-dark); 
        }
        
        .logo img { 
            width: 50px; 
            height: 50px; 
            object-fit: contain; 
            border-radius: 50%; 
        }
        
        nav ul { 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            gap: 30px; 
        }
        
        nav li { 
            position: relative; 
        }
        
        nav a { 
            text-decoration: none; 
            color: var(--text-color-dark); 
            font-weight: 600; 
            padding: 8px 4px; 
            display: block; 
        }
        
        nav li ul { 
            display: none; 
            position: absolute; 
            top: 100%; 
            left: 0; 
            background: var(--card-bg); 
            padding: 10px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px var(--shadow-light); 
            min-width: 200px; 
            z-index: 999; 
        }
        
        nav li:hover > ul { 
            display: block; 
        }
        
        nav li ul li a { 
            color: var(--text-color-dark); 
            font-weight: 400; 
            white-space: nowrap; 
            padding: 5px 20px; 
        }
        
        main { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        
        .card { 
            background: var(--card-bg); 
            color: var(--text-color-dark); 
            border-radius: 20px; 
            padding: 30px; 
            box-shadow: 0 5px 20px var(--shadow-light); 
            margin-bottom: 30px; 
        }
        
        .page-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            padding-bottom: 15px; 
            border-bottom: 2px solid #eee; 
        }
        
        .page-title { 
            font-size: 1.8rem; 
            font-weight: 700; 
            color: var(--primary-color); 
            margin: 0; 
        }
        
        .user-info { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
        }
        
        .user-info p { 
            margin: 5px 0; 
            font-size: 0.9rem; 
            color: #666; 
        }
        
        .user-info strong { 
            color: var(--primary-color); 
        }
        
        .table-container { 
            overflow-x: auto; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
        }
        
        th { 
            background-color: var(--primary-color); 
            color: white; 
            font-weight: 600; 
        }
        
        tr:hover { 
            background-color: #f5f5f5; 
        }
        
        .status-badge { 
            padding: 5px 10px; 
            border-radius: 15px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            text-align: center; 
            display: inline-block; 
        }
        
        .status-pending { 
            background-color: var(--status-pending); 
            color: #000; 
        }
        
        .status-disetujui { 
            background-color: var(--status-disetujui); 
            color: white; 
        }
        
        .status-ditolak { 
            background-color: var(--status-ditolak); 
            color: white; 
        }
        
        .empty-state { 
            text-align: center; 
            padding: 40px; 
            color: #666; 
        }
        
        .empty-state img { 
            width: 100px; 
            height: 100px; 
            margin-bottom: 20px; 
            opacity: 0.5; 
        }
        
        .btn { 
            display: inline-block; 
            background: var(--accent-color); 
            color: white; 
            padding: 10px 20px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            margin-top: 10px; 
        }
        
        .btn:hover { 
            background: #3a3162; 
        }
        
        .action-buttons { 
            display: flex; 
            gap: 5px; 
        }
        
        .btn-small { 
            padding: 5px 10px; 
            font-size: 0.8rem; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none; 
        }
        
        .btn-edit { 
            background: #17a2b8; 
            color: white; 
        }
        
        .btn-delete { 
            background: #dc3545; 
            color: white; 
        }
        
        .jam-info { 
            font-size: 0.8rem; 
            color: #666; 
        }
        
        /* --- GAYA PAGINASI BARU --- */
        .pagination-wrapper {
            background-color: #f8f9fa;
            padding: 20px 15px;
            margin-top: 30px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-wrapper a, 
        .pagination-wrapper span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
            user-select: none;
        }

        .pagination-wrapper a {
            color: var(--primary-color);
            background-color: #fff;
            border: 1px solid #dee2e6;
        }

        .pagination-wrapper a:hover {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .pagination-wrapper span.active {
            background-color: var(--primary-color);
            color: #fff;
            border: 1px solid var(--primary-color);
            cursor: default;
            box-shadow: 0 4px 10px rgba(30, 16, 94, 0.3);
        }

        .pagination-wrapper span.disabled {
            color: #adb5bd;
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            cursor: not-allowed;
        }

        .pagination-wrapper span.ellipsis {
            background-color: transparent;
            border: none;
            color: #6c757d;
            font-weight: bold;
        }
        
        .info-pagination {
            background: #e7f3ff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 500;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="image/namayayasan.png" alt="Logo">
        <span>Yayasan Purba Danarta</span>
    </div>
    <nav>
        <ul>
            <li><a href="dashboard_penanggungjawab.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Divisi</a></li>
                    <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Divisi</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab.php">Riwayat KHL Pribadi</a></li>
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
    
    <div class="heading-section">
        <h1>Riwayat KHL Saya</h1>
        <p>Lihat semua riwayat pengajuan KHL yang pernah Anda buat.</p>
    </div>

    <div class="card">
        <div class="user-info">
            <p><strong>Kode Karyawan:</strong> <?= htmlspecialchars($kode_karyawan) ?></p>
            <p><strong>Nama:</strong> <?= htmlspecialchars($nama_pj) ?></p>
            <p><strong>Divisi:</strong> <?= htmlspecialchars($divisi_pj) ?></p>
            <p><strong>Jabatan:</strong> <?= htmlspecialchars($jabatan) ?></p>
        </div>

        <!-- Info Pagination -->
        <div class="info-pagination">
            Menampilkan <?php echo count($riwayat_khl); ?> dari <?php echo $total_data; ?> KHL 
            (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
            <a href="pengajuankhl_penanggungjawab.php" class="btn">+ Ajukan KHL Baru</a>
        </div>

        <div class="table-container">
            <?php if (empty($riwayat_khl)): ?>
                <div class="empty-state">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23666'%3E%3Cpath d='M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z'/%3E%3C/svg%3E" alt="No Data">
                    <h3>Belum Ada Riwayat KHL</h3>
                    <p>Anda belum pernah mengajukan KHL.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID KHL</th>
                            <th>Proyek</th>
                            <th>Tanggal KHL</th>
                            <th>Jam Kerja</th>
                            <th>Tanggal Cuti KHL</th>
                            <th>Jam Cuti</th>
                            <th>Status</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        foreach ($riwayat_khl as $khl): 
                        ?>
                            <tr>
                                <td>#<?= $khl['id_khl'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($khl['proyek']) ?></strong>
                                    <?php if (!empty($khl['alasan_penolakan'])): ?>
                                        <br><small style="color: #dc3545;">Alasan penolakan: <?= htmlspecialchars($khl['alasan_penolakan']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($khl['tanggal_khl'])) ?></td>
                                <td class="jam-info">
                                    <?= date('H:i', strtotime($khl['jam_mulai_kerja'])) ?> - 
                                    <?= date('H:i', strtotime($khl['jam_akhir_kerja'])) ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($khl['tanggal_cuti_khl'])) ?></td>
                                <td class="jam-info">
                                    <?= date('H:i', strtotime($khl['jam_mulai_cuti_khl'])) ?> - 
                                    <?= date('H:i', strtotime($khl['jam_akhir_cuti_khl'])) ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($khl['status_khl']) {
                                        case 'pending':
                                            $status_class = 'status-pending';
                                            $status_text = 'Menunggu';
                                            break;
                                        case 'disetujui':
                                            $status_class = 'status-disetujui';
                                            $status_text = 'Disetujui';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'status-ditolak';
                                            $status_text = 'Ditolak';
                                            break;
                                        default:
                                            $status_class = 'status-pending';
                                            $status_text = 'Menunggu';
                                    }
                                    ?>
                                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($khl['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($khl['status_khl'] == 'pending'): ?>
                                            <a href="edit_khl.php?id=<?= $khl['id_khl'] ?>" class="btn-small btn-edit">Edit</a>
                                            <a href="hapus_khl.php?id=<?= $khl['id_khl'] ?>" class="btn-small btn-delete" onclick="return confirm('Yakin ingin menghapus pengajuan KHL ini?')">Hapus</a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 0.8rem;">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <?php
                    // Tombol Sebelumnya
                    if ($page > 1) {
                        echo '<a href="?page=' . ($page - 1) . '">‹ Sebelumnya</a>';
                    } else {
                        echo '<span class="disabled">‹ Sebelumnya</span>';
                    }

                    // Nomor halaman
                    $range = 1;
                    if ($page > ($range + 1)) {
                        echo '<a href="?page=1">1</a>';
                        if ($page > ($range + 2)) {
                            echo '<span class="ellipsis">...</span>';
                        }
                    }

                    for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
                        if ($i == $page) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="?page=' . $i . '">' . $i . '</a>';
                        }
                    }

                    if ($page < ($total_pages - $range)) {
                        if ($page < ($total_pages - $range - 1)) {
                            echo '<span class="ellipsis">...</span>';
                        }
                        echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
                    }

                    // Tombol Selanjutnya
                    if ($page < $total_pages) {
                        echo '<a href="?page=' . ($page + 1) . '">Selanjutnya ›</a>';
                    } else {
                        echo '<span class="disabled">Selanjutnya ›</span>';
                    }
                    ?>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>

        <!-- STATISTIK -->
        <?php if (!empty($riwayat_khl)): ?>
            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                <h4 style="margin-top: 0; color: var(--primary-color);">Statistik Pengajuan KHL</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);"><?= $total_pengajuan ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Total Pengajuan</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--status-disetujui);"><?= $disetujui_count ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Disetujui</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--status-pending);"><?= $pending_count ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Menunggu</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--status-ditolak);"><?= $ditolak_count ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Ditolak</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Konfirmasi sebelum menghapus
function confirmDelete(khlId) {
    if (confirm('Apakah Anda yakin ingin menghapus pengajuan KHL ini?')) {
        window.location.href = 'hapus_khl.php?id=' + khlId;
    }
    return false;
}
</script>
</body>
</html>