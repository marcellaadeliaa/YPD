<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$kode_karyawan = $user['kode_karyawan'];
$nama_pj = $user['nama_lengkap'];
$divisi_pj = $user['divisi'];
$jabatan = "Penanggung Jawab Divisi " . $divisi_pj;

$limit = 5; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM data_pengajuan_cuti 
        WHERE kode_karyawan = ? 
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $kode_karyawan, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$riwayat_cuti = [];
while ($row = $result->fetch_assoc()) {
    $riwayat_cuti[] = $row;
}

$sql_total = "SELECT COUNT(*) as total FROM data_pengajuan_cuti WHERE kode_karyawan = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("s", $kode_karyawan);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_data = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

$sql_stats = "SELECT 
              COUNT(*) as total,
              SUM(CASE WHEN status = 'Diterima' THEN 1 ELSE 0 END) as diterima,
              SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak,
              SUM(CASE WHEN status = 'Menunggu Persetujuan' THEN 1 ELSE 0 END) as menunggu
              FROM data_pengajuan_cuti 
              WHERE kode_karyawan = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("s", $kode_karyawan);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();
$stats = $result_stats->fetch_assoc();

$total_pengajuan = $stats['total'];
$diterima_count = $stats['diterima'];
$ditolak_count = $stats['ditolak'];
$menunggu_count = $stats['menunggu'];

function hitungHariKerja($tanggal_mulai, $tanggal_akhir) {
    $jumlah_hari = 0;
    $current_date = new DateTime($tanggal_mulai);
    $end_date = new DateTime($tanggal_akhir);
    
    while ($current_date <= $end_date) {
        $day_of_week = $current_date->format('N'); 
        if ($day_of_week >= 1 && $day_of_week <= 5) { 
            $jumlah_hari++;
        }
        $current_date->modify('+1 day');
    }
    
    return $jumlah_hari;
}

$sql_hari_kerja = "SELECT tanggal_mulai, tanggal_akhir FROM data_pengajuan_cuti 
                   WHERE kode_karyawan = ? AND status = 'Diterima'";
$stmt_hari_kerja = $conn->prepare($sql_hari_kerja);
$stmt_hari_kerja->bind_param("s", $kode_karyawan);
$stmt_hari_kerja->execute();
$result_hari_kerja = $stmt_hari_kerja->get_result();

$total_hari_kerja = 0;
while ($row = $result_hari_kerja->fetch_assoc()) {
    $total_hari_kerja += hitungHariKerja($row['tanggal_mulai'], $row['tanggal_akhir']);
}

$stmt->close();
$stmt_total->close();
$stmt_stats->close();
$stmt_hari_kerja->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Cuti Pribadi - Penanggung Jawab</title>
    <style>
        .heading-section h1 { font-size: 2.5rem; margin: 0; color: #fff;}
        .heading-section p { font-size: 1.1rem; margin-top: 5px; opacity: 0.9; margin-bottom: 30px; color: #fff;}
        
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE; 
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF; 
            --text-color-light: #fff; 
            --text-color-dark: #2e1f4f; 
            --shadow-light: rgba(0,0,0,0.15);
            --status-menunggu: #ffc107;
            --status-diterima: #28a745;
            --status-ditolak: #dc3545;
            --cuti-tahunan: #4ecdc4;
            --cuti-lustrum: #45b7af;
            --cuti-sakit: #ff6b6b;
            --cuti-khusus: #ffd93d;
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
        
        .user-info { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            border-left: 4px solid var(--primary-color);
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
        
        .status-menunggu { 
            background-color: var(--status-menunggu); 
            color: #000; 
        }
        
        .status-diterima { 
            background-color: var(--status-diterima); 
            color: white; 
        }
        
        .status-ditolak { 
            background-color: var(--status-ditolak); 
            color: white; 
        }
        
        .cuti-badge { 
            padding: 4px 8px; 
            border-radius: 12px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            text-align: center; 
            display: inline-block; 
            margin-right: 5px;
        }
        
        .badge-tahunan { 
            background-color: var(--cuti-tahunan); 
            color: white; 
        }
        
        .badge-lustrum { 
            background-color: var(--cuti-lustrum); 
            color: white; 
        }
        
        .badge-sakit { 
            background-color: var(--cuti-sakit); 
            color: white; 
        }
        
        .badge-khusus { 
            background-color: var(--cuti-khusus); 
            color: #000; 
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
        
        .periode-info { 
            font-size: 0.8rem; 
            color: #666; 
        }
        
        .hari-kerja-info {
            background: #e7f3ff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-top: 3px;
            display: inline-block;
            border-left: 3px solid #2196F3;
        }
        
        .file-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }
        
        .alasan-text {
            max-width: 200px;
            word-wrap: break-word;
            font-size: 0.85rem;
            line-height: 1.3;
        }
        
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
        <h1>Riwayat Cuti Saya</h1>
        <p>Lihat semua riwayat pengajuan cuti yang pernah Anda buat.</p>
    </div>

    <div class="card">
        <div class="user-info">
            <p><strong>Kode Karyawan:</strong> <?= htmlspecialchars($kode_karyawan) ?></p>
            <p><strong>Nama:</strong> <?= htmlspecialchars($nama_pj) ?></p>
            <p><strong>Divisi:</strong> <?= htmlspecialchars($divisi_pj) ?></p>
            <p><strong>Jabatan:</strong> <?= htmlspecialchars($jabatan) ?></p>
            <div class="hari-kerja-info">📝 Catatan: Sabtu & Minggu tidak terhitung sebagai hari cuti</div>
        </div>

        <div class="info-pagination">
            Menampilkan <?php echo count($riwayat_cuti); ?> dari <?php echo $total_data; ?> cuti 
            (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
            <a href="pengajuancuti_penanggungjawab.php" class="btn">+ Ajukan Cuti Baru</a>
        </div>

        <div class="table-container">
            <?php if (empty($riwayat_cuti)): ?>
                <div class="empty-state">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23666'%3E%3Cpath d='M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z'/%3E%3C/svg%3E" alt="No Data">
                    <h3>Belum Ada Riwayat Cuti</h3>
                    <p>Anda belum pernah mengajukan cuti.</p>
                    
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jenis Cuti</th>
                            <th>Periode Cuti</th>
                            <th>Hari Kerja</th>
                            <th>Alasan</th>
                            <th>File Surat Dokter</th>
                            <th>Status</th>
                            <th>Waktu Persetujuan</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        foreach ($riwayat_cuti as $cuti): 
                        ?>
                            <?php
                            $jumlah_hari_kerja = hitungHariKerja($cuti['tanggal_mulai'], $cuti['tanggal_akhir']);
                            $total_hari_kalender = (strtotime($cuti['tanggal_akhir']) - strtotime($cuti['tanggal_mulai'])) / (60 * 60 * 24) + 1;
                            
                            $badge_class = '';
                            switch ($cuti['jenis_cuti']) {
                                case 'Tahunan':
                                    $badge_class = 'badge-tahunan';
                                    break;
                                case 'Lustrum':
                                    $badge_class = 'badge-lustrum';
                                    break;
                                case 'Sakit':
                                    $badge_class = 'badge-sakit';
                                    break;
                                case 'Khusus - Menikah':
                                    $badge_class = 'badge-khusus';
                                    break;
                                default:
                                    $badge_class = 'badge-tahunan';
                            }
                            
                            $status_class = '';
                            $status_text = '';
                            switch ($cuti['status']) {
                                case 'Menunggu Persetujuan':
                                    $status_class = 'status-menunggu';
                                    $status_text = 'Menunggu';
                                    break;
                                case 'Diterima':
                                    $status_class = 'status-diterima';
                                    $status_text = 'Diterima';
                                    break;
                                case 'Ditolak':
                                    $status_class = 'status-ditolak';
                                    $status_text = 'Ditolak';
                                    break;
                                default:
                                    $status_class = 'status-menunggu';
                                    $status_text = 'Menunggu';
                            }
                            ?>
                            <tr>
                                <td>#<?= $cuti['id'] ?></td>
                                <td>
                                    <span class="cuti-badge <?= $badge_class ?>"><?= htmlspecialchars($cuti['jenis_cuti']) ?></span>
                                </td>
                                <td>
                                    <div class="periode-info">
                                        <strong><?= date('d/m/Y', strtotime($cuti['tanggal_mulai'])) ?></strong> s/d<br>
                                        <strong><?= date('d/m/Y', strtotime($cuti['tanggal_akhir'])) ?></strong>
                                    </div>
                                    <div class="hari-kerja-info">
                                        <?= $total_hari_kalender ?> hari kalender<br>
                                        <?= $jumlah_hari_kerja ?> hari kerja
                                    </div>
                                </td>
                                <td>
                                    <strong><?= $jumlah_hari_kerja ?> hari</strong>
                                    <div style="font-size: 0.7rem; color: #666;">(Senin-Jumat)</div>
                                </td>
                                <td>
                                    <div class="alasan-text" title="<?= htmlspecialchars($cuti['alasan']) ?>">
                                        <?= htmlspecialchars($cuti['alasan']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($cuti['file_surat_dokter'])): ?>
                                        <a href="<?= htmlspecialchars($cuti['file_surat_dokter']) ?>" class="file-link" target="_blank">Lihat File</a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.8rem;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                    <?php if ($cuti['status'] == 'Ditolak' && !empty($cuti['alasan'])): ?>
                                        <br><small style="color: #dc3545; font-size: 0.7rem;"><?= htmlspecialchars($cuti['alasan']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cuti['waktu_persetujuan'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($cuti['waktu_persetujuan'])) ?>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.8rem;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($cuti['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($cuti['status'] == 'Menunggu Persetujuan'): ?>
                                            <a href="edit_cuti.php?id=<?= $cuti['id'] ?>" class="btn-small btn-edit">Edit</a>
                                            <a href="hapus_cuti.php?id=<?= $cuti['id'] ?>" class="btn-small btn-delete" onclick="return confirm('Yakin ingin menghapus pengajuan cuti ini?')">Hapus</a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 0.8rem;">Tidak bisa diubah</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <?php
                    if ($page > 1) {
                        echo '<a href="?page=' . ($page - 1) . '">‹ Sebelumnya</a>';
                    } else {
                        echo '<span class="disabled">‹ Sebelumnya</span>';
                    }

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

        <?php if (!empty($riwayat_cuti)): ?>
            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                <h4 style="margin-top: 0; color: var(--primary-color);">Statistik Pengajuan Cuti</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);"><?= $total_pengajuan ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Total Pengajuan</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--status-diterima);"><?= $diterima_count ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Diterima</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--status-menunggu);"><?= $menunggu_count ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Menunggu</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--status-ditolak);"><?= $ditolak_count ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Ditolak</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--cuti-tahunan);"><?= $total_hari_kerja ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Total Hari Kerja</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function confirmDelete(cutiId) {
    if (confirm('Apakah Anda yakin ingin menghapus pengajuan cuti ini?')) {
        window.location.href = 'hapus_cuti.php?id=' + cutiId;
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    const alasanElements = document.querySelectorAll('.alasan-text');
    alasanElements.forEach(function(el) {
        if (el.scrollWidth > el.clientWidth) {
            el.title = el.textContent;
        }
    });
});
</script>
</body>
</html>