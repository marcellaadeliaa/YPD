<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];
$kode_direktur = $user['kode_karyawan'];

$start_date = $_GET['start_date'] ?? ''; 
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_clauses = []; 

$where_clauses[] = "kode_karyawan = '" . mysqli_real_escape_string($conn, $kode_direktur) . "'";

if (!empty($status_filter)) {
    $where_clauses[] = "status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

if (!empty($start_date)) {
    $where_clauses[] = "tanggal_mulai >= '" . mysqli_real_escape_string($conn, $start_date) . "'";
}
if (!empty($end_date)) {
    $where_clauses[] = "tanggal_akhir <= '" . mysqli_real_escape_string($conn, $end_date) . "'";
}

if (!empty($search_query)) {
    $search = mysqli_real_escape_string($conn, "%" . $search_query . "%");
    $where_clauses[] = "(alasan LIKE '$search' OR jenis_cuti LIKE '$search')";
}
 
$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$sql = "
    SELECT 
        id,
        kode_karyawan,
        nama_karyawan,
        divisi,
        jabatan,
        role,
        jenis_cuti,
        tanggal_mulai,
        tanggal_akhir,
        alasan,
        file_surat_dokter,
        status,
        alasan_penolakan,
        waktu_persetujuan,
        created_at
    FROM 
        data_pengajuan_cuti 
    $where_sql
    ORDER BY 
        tanggal_mulai DESC, id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Gagal: " . mysqli_error($conn) . " Query: " . $sql);
}

$all_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Pagination configuration
$limit = 5; // Jumlah data per halaman
$total_records = count($all_data);
$total_pages = ceil($total_records / $limit);

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;
$filtered_data = array_slice($all_data, $offset, $limit);

// Fungsi untuk menghitung hari kerja
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

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Cuti Pribadi - Direktur</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
    nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:1600px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { text-align:left; font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
    .filter-row { display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group input, .filter-group select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .filter-group.date-group { min-width: 150px; }
    .filter-group.search-group { flex-grow: 1; min-width: 200px; }
    .filter-group.status-group { min-width: 180px; }
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; text-decoration: none; display: inline-block; text-align: center; }
    .btn-cari { background-color: #4a3f81; }
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    
    /* Container untuk tabel scrollable */
    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-top: 20px;
        border: 2px solid #34377c;
        border-radius: 8px;
        background: white;
    }
    
    .data-table { 
        width: 100%;
        min-width: 1400px; /* Minimum width untuk memastikan konten terbaca */
        border-collapse: collapse; 
        font-size: 14px; 
    }
    
    .data-table th, .data-table td { 
        padding: 12px 15px; 
        text-align: left; 
        border: 1px solid #ddd;
        white-space: nowrap; /* Mencegah wrap di sel normal */
    }
    
    .data-table th { 
        background-color: #4a3f81; 
        font-weight: 600; 
        color: white;
        border-bottom: 2px solid #34377c;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .data-table td {
        background-color: white;
    }
    
    .data-table tbody tr:hover { 
        background-color: #f1f1f1; 
    }
    
    .data-table tbody tr:nth-child(even) td {
        background-color: #f8f9fa;
    }
    
    .data-table tbody tr:nth-child(even):hover td {
        background-color: #e9ecef;
    }
    
    /* Kolom dengan teks panjang bisa wrap */
    .data-table td.alasan-cell,
    .data-table td.alasan-penolakan-cell {
        white-space: normal; /* Biarkan wrap untuk kolom alasan */
        min-width: 200px;
        max-width: 300px;
        word-wrap: break-word;
    }
    
    .status-diterima { color: #28a745; font-weight: 600; }
    .status-ditolak { color: #d9534f; font-weight: 600; }
    .status-menunggu { color: #ffc107; font-weight: 600; }
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    .filter-info { background: #e7f3ff; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; border-left: 4px solid #4a3f81;}
    .file-link { color: #4a3f81; text-decoration: none; font-weight: 500;}
    .file-link:hover { text-decoration: underline;}
    
    /* Styling untuk kolom alasan */
    .alasan-cell {
        max-width: 300px;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    /* Styling untuk alasan penolakan */
    .alasan-penolakan-cell { 
        color: #d9534f; 
        font-style: italic;
        font-size: 13px;
        max-width: 300px;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    /* Scrollbar styling */
    .table-container::-webkit-scrollbar {
        height: 12px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 0 0 6px 6px;
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: #4a3f81;
        border-radius: 6px;
    }
    
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #3a3162;
    }
    
    /* Indikator scroll */
    .scroll-indicator {
        text-align: center;
        color: #666;
        font-size: 12px;
        margin-top: 5px;
        font-style: italic;
    }

    /* Style tambahan dari riwayatcuti_penanggungjawab.php */
    .cuti-details {
        background: #f8f9fa;
        padding: 8px;
        border-radius: 6px;
        margin: 5px 0;
        font-size: 0.8rem;
    }

    .hari-kerja-info {
        background: #e7f3ff;
        padding: 6px 10px;
        border-radius: 4px;
        margin: 3px 0;
        font-size: 0.75rem;
        border-left: 3px solid #2196F3;
    }

    .role-badge {
        background: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .role-karyawan {
        background: #d4edda;
        color: #155724;
    }
    
    .role-penanggung-jawab {
        background: #cce7ff;
        color: #004085;
    }
    
    .role-direktur {
        background: #f8d7da;
        color: #721c24;
    }
    
    .role-admin {
        background: #e2e3e5;
        color: #383d41;
    }

    .weekend-note {
        color: #666;
        font-size: 0.75rem;
        font-style: italic;
        margin-top: 3px;
    }

    .user-info {
        background: #d4edda;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
        color: #155724;
    }

    /* Pagination Styles */
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
        color: #1E105E; /* Warna teks untuk link */
        background-color: #fff;
        border: 1px solid #dee2e6;
    }

    .pagination-wrapper a:hover {
        background-color: #1E105E;
        color: #fff;
        border-color: #1E105E;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .pagination-wrapper span.active {
        background-color: #1E105E;
        color: #fff;
        border: 1px solid #1E105E;
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

    .pagination-info {
        text-align: center;
        margin-top: 15px;
        color: #666;
        font-size: 14px;
    }
    
    @media (max-width: 768px) { 
        .filter-row { flex-direction: column; } 
        .filter-group { width: 100%; } 
        .action-bar { flex-direction: column; } 
        .btn { width: 100%; } 
        .data-table { font-size: 12px; } 
        .data-table th, .data-table td { padding: 8px 10px; }
        .card { padding: 20px; }
        main { max-width: 1400px; }
        
        /* Untuk mobile, kurangi min-width tabel */
        .data-table {
            min-width: 1200px;
        }
        
        /* Perbesar scrollbar di mobile */
        .table-container::-webkit-scrollbar {
            height: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .data-table {
            min-width: 1000px;
        }
        
        .data-table th, .data-table td {
            padding: 6px 8px;
            font-size: 11px;
        }
    }
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
                    <li><a href="persetujuan_cuti_karyawan.php">Persetujuan Cuti</a></li>
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
    <h1>Welcome, <?php echo htmlspecialchars($nama_direktur); ?>!</h1>
    <p class="admin-title">Riwayat Cuti Pribadi</p>

    <div class="card">
        <h2 class="page-title">Riwayat Cuti Pribadi</h2>
        
        <div class="user-info">
            <strong>Informasi Pengguna:</strong><br>
            Nama: <?php echo htmlspecialchars($nama_direktur); ?> | 
            Kode Karyawan: <?php echo htmlspecialchars($kode_direktur); ?> | 
            Role: Direktur
            <div class="weekend-note">📝 Catatan: Sabtu & Minggu tidak terhitung sebagai hari cuti</div>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="riwayat_cuti_pribadi_direktur.php"> 
                <div class="filter-row">
                    <div class="filter-group date-group">
                        <label for="start_date">Dari Tanggal Cuti</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    
                    <div class="filter-group date-group">
                        <label for="end_date">Sampai Tanggal Cuti</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    
                    <div class="filter-group status-group">
                        <label for="status">Status Cuti</label>
                        <select id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="Diterima" <?= $status_filter == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                            <option value="Ditolak" <?= $status_filter == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            <option value="Menunggu Persetujuan" <?= $status_filter == 'Menunggu Persetujuan' ? 'selected' : '' ?>>Menunggu Persetujuan</option>
                        </select>
                    </div>
                    
                    <div class="filter-group search-group">
                        <label for="search">Cari (Alasan/Jenis Cuti)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan alasan atau jenis cuti..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="riwayat_cuti_pribadi_direktur.php" class="btn btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <?php if (!empty($start_date) || !empty($end_date) || !empty($search_query) || !empty($status_filter)): ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong>
                <?php 
                $filters = [];
                if (!empty($start_date)) $filters[] = "Dari Tanggal: " . date('d/m/Y', strtotime($start_date));
                if (!empty($end_date)) $filters[] = "Sampai Tanggal: " . date('d/m/Y', strtotime($end_date));
                if (!empty($status_filter)) $filters[] = "Status: " . htmlspecialchars($status_filter);
                if (!empty($search_query)) $filters[] = "Pencarian: '" . htmlspecialchars($search_query) . "'";
                echo implode(' | ', $filters);
                ?>
                <span style="float: right; color: #666;">
                    Total Data: <?= $total_records ?> | Halaman <?= $page ?> dari <?= $total_pages ?>
                </span>
            </div>
        <?php else: ?>
            <div class="filter-info">
                <strong>Total Data:</strong> <?= $total_records ?> cuti pribadi
                <span style="float: right; color: #666;">
                    Halaman <?= $page ?> dari <?= $total_pages ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Container untuk tabel scrollable -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th>Alasan Cuti</th>
                        <th>Alasan Penolakan</th>
                        <th>File Surat Dokter</th>
                        <th>Status</th>
                        <th>Waktu Persetujuan</th>
                        <th>Tanggal Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($filtered_data)): ?>
                        <?php $no = $offset + 1; foreach ($filtered_data as $cuti): ?>
                            <?php 
                            $jumlah_hari_kerja = hitungHariKerja($cuti['tanggal_mulai'], $cuti['tanggal_akhir']);
                            $total_hari_kalender = (strtotime($cuti['tanggal_akhir']) - strtotime($cuti['tanggal_mulai'])) / (60 * 60 * 24) + 1;
                            $alasan_penolakan = isset($cuti['alasan_penolakan']) ? $cuti['alasan_penolakan'] : '';
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($cuti['kode_karyawan']) ?></td>
                                <td>
                                    <?= htmlspecialchars($cuti['nama_karyawan']) ?>
                                    <div class="cuti-details">
                                        <strong>Periode:</strong> <?= $total_hari_kalender ?> hari kalender<br>
                                        <div class="hari-kerja-info">
                                            <strong>Hari Kerja:</strong> <?= $jumlah_hari_kerja ?> hari (Senin-Jumat)
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge role-<?= str_replace(' ', '-', $cuti['role']) ?>">
                                        <?= htmlspecialchars(ucfirst($cuti['role'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                                <td><?= htmlspecialchars($cuti['tanggal_mulai']) ?></td>
                                <td><?= htmlspecialchars($cuti['tanggal_akhir']) ?></td>
                                <td class="alasan-cell"><?= htmlspecialchars($cuti['alasan']) ?></td>
                                <td class="alasan-penolakan-cell">
                                    <?php if (!empty($alasan_penolakan) && $cuti['status'] == 'Ditolak'): ?>
                                        <?= htmlspecialchars($alasan_penolakan) ?>
                                    <?php else: ?>
                                        <span style="color:#999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($cuti['file_surat_dokter'])): ?>
                                        <a href="<?= htmlspecialchars($cuti['file_surat_dokter']) ?>" class="file-link" target="_blank">Lihat File</a>
                                    <?php else: ?>
                                        <span style="color:#999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php $status = strtolower(trim($cuti['status'])); if ($status === 'diterima') { echo '<span class="status-diterima">Diterima</span>'; } elseif ($status === 'ditolak') { echo '<span class="status-ditolak">Ditolak</span>'; } elseif ($status === 'menunggu persetujuan') { echo '<span class="status-menunggu">Menunggu</span>'; } else { echo htmlspecialchars($cuti['status'] ?: '-'); } ?>
                                </td>
                                <td>
                                    <?php if (!empty($cuti['waktu_persetujuan'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($cuti['waktu_persetujuan'])) ?>
                                    <?php else: ?>
                                        <span style="color:#999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($cuti['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="13" class="no-data">
                            <?php if (!empty($start_date) || !empty($end_date) || !empty($search_query) || !empty($status_filter)): ?>
                                Tidak ada data cuti pribadi yang sesuai dengan filter yang dipilih.
                            <?php else: ?>
                                Belum ada riwayat cuti pribadi.
                                <br><br>
                                <a href="pengajuan_cuti_direktur.php" style="color: #4a3f81; font-weight: 600;">Ajukan Cuti Sekarang</a>
                            <?php endif; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="scroll-indicator">
            ← Geser untuk melihat lebih banyak data →
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <?php
                $query_params = $_GET;
                unset($query_params['page']);
                $base_url = http_build_query($query_params);
                $ampersand = !empty($base_url) ? '&' : '';
                
                $range = 2; // Jumlah halaman yang ditampilkan di kiri dan kanan halaman aktif

                if ($page > 1) {
                    echo '<a href="?' . $base_url . $ampersand . 'page=' . ($page - 1) . '">‹ Sebelumnya</a>';
                } else {
                    echo '<span class="disabled">‹ Sebelumnya</span>';
                }

                if ($page > ($range + 1)) {
                    echo '<a href="?' . $base_url . $ampersand . 'page=1">1</a>';
                    if ($page > ($range + 2)) {
                        echo '<span class="ellipsis">...</span>';
                    }
                }

                for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
                    if ($i == $page) {
                        echo '<span class="active">' . $i . '</span>';
                    } else {
                        echo '<a href="?' . $base_url . $ampersand . 'page=' . $i . '">' . $i . '</a>';
                    }
                }

                if ($page < ($total_pages - $range)) {
                    if ($page < ($total_pages - $range - 1)) {
                        echo '<span class="ellipsis">...</span>';
                    }
                    echo '<a href="?' . $base_url . $ampersand . 'page=' . $total_pages . '">' . $total_pages . '</a>';
                }

                if ($page < $total_pages) {
                    echo '<a href="?' . $base_url . $ampersand . 'page=' . ($page + 1) . '">Selanjutnya ›</a>';
                } else {
                    echo '<span class="disabled">Selanjutnya ›</span>';
                }
                ?>
            </div>
            
            <div class="pagination-info">
                Menampilkan <?= count($filtered_data) ?> dari <?= $total_records ?> data cuti pribadi
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="pengajuan_cuti_direktur.php" class="btn btn-cari" style="background-color: #28a745;">+ Ajukan Cuti Baru</a>
            <a href="dashboarddirektur.php" class="btn btn-reset">← Kembali ke Dashboard</a>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.querySelector('.table-container');
    
    tableContainer.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            tableContainer.scrollLeft -= 100;
            e.preventDefault();
        } else if (e.key === 'ArrowRight') {
            tableContainer.scrollLeft += 100;
            e.preventDefault();
        }
    });
    
    tableContainer.setAttribute('tabindex', '0');
    
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'background-color 0.2s ease';
        });
    });

    // Validasi tanggal
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    function validateDates() {
        if (startDate.value && endDate.value) {
            if (new Date(startDate.value) > new Date(endDate.value)) {
                alert('Tanggal akhir tidak boleh kurang dari tanggal awal');
                endDate.value = '';
            }
        }
    }
    
    startDate.addEventListener('change', validateDates);
    endDate.addEventListener('change', validateDates);
});
</script>

</body>
</html>
<?php 
mysqli_close($conn); 