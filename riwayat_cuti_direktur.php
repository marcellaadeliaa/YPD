<?php

session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];

$start_date = $_GET['start_date'] ?? ''; 
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$role_filter = $_GET['role'] ?? '';

$where_clauses = []; 

if (!empty($status_filter)) {
    $where_clauses[] = "status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

if (!empty($role_filter)) {
    $where_clauses[] = "role = '" . mysqli_real_escape_string($conn, $role_filter) . "'";
}

if (!empty($start_date)) {
    $where_clauses[] = "tanggal_mulai >= '" . mysqli_real_escape_string($conn, $start_date) . "'";
}
if (!empty($end_date)) {
    $where_clauses[] = "tanggal_akhir <= '" . mysqli_real_escape_string($conn, $end_date) . "'";
}

if (!empty($search_query)) {
    $search = mysqli_real_escape_string($conn, "%" . $search_query . "%");
    $where_clauses[] = "(nama_karyawan LIKE '$search' OR kode_karyawan LIKE '$search' OR divisi LIKE '$search')";
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

$filtered_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Cuti Pegawai - Direktur</title>
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
    .filter-group.status-group, .filter-group.role-group { min-width: 180px; }
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
        min-width: 1200px; /* Minimum width untuk memastikan konten terbaca */
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
            min-width: 1000px;
        }
        
        /* Perbesar scrollbar di mobile */
        .table-container::-webkit-scrollbar {
            height: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .data-table {
            min-width: 800px;
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
    <p class="admin-title">Direktur</p>

    <div class="card"> 
        <h2 class="page-title">Riwayat Cuti Pegawai</h2> 
        
        <div class="filter-section"> 
            <form method="GET" action="riwayat_cuti_direktur.php"> 
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
                        <label for="status">Filter Status Cuti</label> 
                        <select id="status" name="status"> 
                            <option value="">Semua Status</option> 
                            <option value="Diterima" <?= $status_filter == 'Diterima' ? 'selected' : '' ?>>Diterima</option> 
                            <option value="Ditolak" <?= $status_filter == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option> 
                            <option value="Menunggu Persetujuan" <?= $status_filter == 'Menunggu Persetujuan' ? 'selected' : '' ?>>Menunggu Persetujuan</option> 
                        </select> 
                    </div> 
                    <div class="filter-group role-group"> 
                        <label for="role">Filter Role Karyawan</label> 
                        <select id="role" name="role"> 
                            <option value="">Semua Role</option> 
                            <option value="karyawan" <?= $role_filter == 'karyawan' ? 'selected' : '' ?>>Karyawan</option> 
                            <option value="penanggung jawab" <?= $role_filter == 'penanggung jawab' ? 'selected' : '' ?>>Penanggung Jawab</option> 
                            <option value="direktur" <?= $role_filter == 'direktur' ? 'selected' : '' ?>>Direktur</option> 
                            <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option> 
                        </select> 
                    </div> 
                    <div class="filter-group search-group"> 
                        <label for="search">Cari (Nama/Kode/Divisi)</label> 
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan nama, NIK, atau divisi..." value="<?= htmlspecialchars($search_query) ?>"> 
                    </div> 
                </div> 
                <div class="action-bar"> 
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button> 
                    <a href="riwayat_cuti_direktur.php" class="btn btn-reset">Reset Filter</a> 
                </div> 
            </form> 
        </div>

        <?php if (!empty($start_date) || !empty($end_date) || !empty($search_query) || !empty($status_filter) || !empty($role_filter)): ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong>
                <?php 
                $filters = [];
                if (!empty($start_date)) $filters[] = "Dari: " . date('d/m/Y', strtotime($start_date));
                if (!empty($end_date)) $filters[] = "Sampai: " . date('d/m/Y', strtotime($end_date));
                if (!empty($status_filter)) $filters[] = "Status: " . htmlspecialchars($status_filter);
                if (!empty($role_filter)) $filters[] = "Role: " . htmlspecialchars(ucfirst($role_filter));
                if (!empty($search_query)) $filters[] = "Pencarian: '" . htmlspecialchars($search_query) . "'";
                echo implode(' | ', $filters);
                ?>
                <span style="float: right; color: #666;">
                    Data ditemukan: <?= count($filtered_data) ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Container untuk tabel scrollable -->
        <div class="table-container">
            <table class="data-table"> 
                <thead> 
                    <tr> 
                        <th>No</th> 
                        <th>Kode Karyawan</th> 
                        <th>Nama Karyawan</th> 
                        <th>Divisi</th> 
                        <th>Jabatan</th> 
                        <th>Role</th> 
                        <th>Jenis Cuti</th> 
                        <th>Tanggal Mulai</th> 
                        <th>Tanggal Akhir</th> 
                        <th>Alasan</th> 
                        <th>File Surat</th> 
                        <th>Status</th>
                        <th>Alasan Penolakan</th>
                    </tr> 
                </thead> 
                <tbody> 
                    <?php if (!empty($filtered_data)): ?> 
                        <?php $no = 1; foreach ($filtered_data as $cuti): ?> 
                            <tr> 
                                <td style="text-align: center;"><?= $no++ ?></td> 
                                <td><?= htmlspecialchars($cuti['kode_karyawan']) ?></td> 
                                <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td> 
                                <td><?= htmlspecialchars($cuti['divisi']) ?></td> 
                                <td><?= htmlspecialchars($cuti['jabatan']) ?></td> 
                                <td><?= htmlspecialchars(ucfirst($cuti['role'])) ?></td> 
                                <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td> 
                                <td><?= date('d/m/Y', strtotime($cuti['tanggal_mulai'])) ?></td> 
                                <td><?= date('d/m/Y', strtotime($cuti['tanggal_akhir'])) ?></td> 
                                <td class="alasan-cell"><?= htmlspecialchars($cuti['alasan']) ?></td> 
                                <td style="text-align: center;"> 
                                    <?php if (!empty($cuti['file_surat_dokter'])): ?> 
                                        <a href="<?= htmlspecialchars($cuti['file_surat_dokter']) ?>" class="file-link" target="_blank">Lihat</a> 
                                    <?php else: ?> 
                                        <span style="color:#999;">-</span> 
                                    <?php endif; ?> 
                                </td> 
                                <td style="text-align: center;"> 
                                    <?php $status = strtolower(trim($cuti['status'])); if ($status === 'diterima') { echo '<span class="status-diterima">Diterima</span>'; } elseif ($status === 'ditolak') { echo '<span class="status-ditolak">Ditolak</span>'; } elseif ($status === 'menunggu persetujuan') { echo '<span class="status-menunggu">Menunggu</span>'; } else { echo htmlspecialchars($cuti['status'] ?: '-'); } ?> 
                                </td>
                                <td class="alasan-penolakan-cell">
                                    <?= !empty($cuti['alasan_penolakan']) ? htmlspecialchars($cuti['alasan_penolakan']) : '<span style="color:#999;">-</span>' ?>
                                </td>
                            </tr> 
                        <?php endforeach; ?> 
                    <?php else: ?> 
                        <tr><td colspan="13" class="no-data">Tidak ada data cuti yang ditemukan</td></tr> 
                    <?php endif; ?> 
                </tbody> 
            </table> 
        </div>
        
        <div class="scroll-indicator">
            ← Geser untuk melihat lebih banyak data →
        </div>
    </div>
</main>

<script>
// Script untuk menangani scroll horizontal dengan keyboard
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.querySelector('.table-container');
    
    // Fungsi untuk scroll dengan keyboard
    tableContainer.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            tableContainer.scrollLeft -= 100;
            e.preventDefault();
        } else if (e.key === 'ArrowRight') {
            tableContainer.scrollLeft += 100;
            e.preventDefault();
        }
    });
    
    // Fokus pada container tabel agar bisa di-scroll dengan keyboard
    tableContainer.setAttribute('tabindex', '0');
    
    // Highlight row saat hover dengan efek yang lebih smooth
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'background-color 0.2s ease';
        });
    });
});
</script>

</body>
</html>
<?php 
mysqli_close($conn);