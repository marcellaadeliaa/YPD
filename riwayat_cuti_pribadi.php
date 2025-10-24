<?php
session_start();
require 'config.php'; 

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}
$user_data = $_SESSION['user'];
$kode_karyawan_login = $user_data['kode_karyawan'];

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';

$sql = "SELECT 
            id, 
            kode_karyawan, 
            nama_karyawan, 
            divisi, 
            tanggal_mulai, 
            tanggal_akhir, 
            jenis_cuti, 
            status, 
            waktu_persetujuan,
            file_surat_dokter,
            alasan_penolakan,
            alasan
        FROM 
            data_pengajuan_cuti 
        WHERE 
            kode_karyawan = ?";

$params = [$kode_karyawan_login];
$types = "s";

if (!empty($start_date)) {
    $sql .= " AND tanggal_mulai >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (!empty($end_date)) {
    $sql .= " AND tanggal_mulai <= ?";
    $params[] = $end_date;
    $types .= "s";
}
if (!empty($search_query)) {
    $sql .= " AND (jenis_cuti LIKE ? OR status LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " ORDER BY tanggal_mulai DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $filtered_data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $filtered_data = [];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Cuti Pribadi</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 140px; height: 50px; object-fit: contain; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
    nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { text-align:left; font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
    .filter-row { display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .filter-group.date-group { min-width: 150px; }
    .filter-group.search-group { flex-grow: 1; min-width: 200px; }
    
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; text-decoration: none; display: inline-block; text-align: center; }
    .btn-cari { background-color: #4a3f81; }
    .btn-reset { background-color: #6c757d; }
    .btn-ajukan { background-color: #2ecc71; }
    
    .data-table-container { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    .status-Diterima { color: #28a745; font-weight: 600; }
    .status-Ditolak { color: #d9534f; font-weight: 600; }
    .status-Menunggu-Persetujuan { color: #f39c12; font-weight: 600; }
    .btn-lihat-surat { background-color: #17a2b8; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 12px; }
    
    .alasan-cuti {
        max-width: 200px;
        word-wrap: break-word;
    }
    
    .alasan-penolakan {
        background: #f8d7da;
        color: #721c24;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        border-left: 3px solid #dc3545;
        margin-top: 5px;
        max-width: 250px;
        word-wrap: break-word;
    }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    .filter-info { 
        background: #e7f3ff; 
        padding: 10px 15px; 
        border-radius: 6px; 
        margin-bottom: 15px; 
        font-size: 14px; 
        border-left: 4px solid #4a3f81;
    }
    
    .user-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #4a3f81;
    }
    
    @media (max-width: 768px) {
        .filter-row { flex-direction: column; }
        .filter-group { width: 100%; }
        .action-bar { flex-direction: column; }
        .btn { width: 100%; }
        .data-table-container { overflow-x: auto; }
        .alasan-cuti { max-width: 150px; }
        .alasan-penolakan { max-width: 150px; }
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
            <li><a href="dashboardkaryawan.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="formcutikaryawan.php">Pengajuan Cuti</a></li>
                    <li><a href="riwayat_cuti_pribadi.php">Riwayat Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="formkhlkaryawan.php">Pengajuan KHL</a></li>
                    <li><a href="riwayat_khl_pribadi.php">Riwayat KHL</a></li>
                </ul>
            </li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="data_pribadi.php">Data Pribadi</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, <?php echo htmlspecialchars($user_data['nama_lengkap']); ?>!</h1>
    <p class="admin-title">Riwayat Cuti Pribadi</p>

    <div class="card">
        <h2 class="page-title">Riwayat Cuti Pribadi</h2>
        
        <div class="user-info">
            <strong>Informasi Karyawan:</strong><br>
            NIK: <?php echo htmlspecialchars($user_data['kode_karyawan']); ?> | 
            Nama: <?php echo htmlspecialchars($user_data['nama_lengkap']); ?> | 
            Divisi: <?php echo htmlspecialchars($user_data['divisi']); ?>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="riwayat_cuti_pribadi.php">
                <div class="filter-row">
                    <div class="filter-group date-group">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    
                    <div class="filter-group date-group">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    
                    <div class="filter-group search-group">
                        <label for="search">Cari (Jenis Cuti/Status)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan jenis cuti atau status..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="riwayat_cuti_pribadi.php" class="btn btn-reset">Reset Filter</a>
                    <a href="formcutikaryawan.php" class="btn btn-ajukan">+ Ajukan Cuti Baru</a>
                </div>
            </form>
        </div>

        <?php if (!empty($start_date) || !empty($end_date) || !empty($search_query)): ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong>
                <?php 
                $filters = [];
                if (!empty($start_date)) $filters[] = "Dari: " . date('d/m/Y', strtotime($start_date));
                if (!empty($end_date)) $filters[] = "Sampai: " . date('d/m/Y', strtotime($end_date));
                if (!empty($search_query)) $filters[] = "Pencarian: '" . htmlspecialchars($search_query) . "'";
                echo implode(' | ', $filters);
                ?>
                <span style="float: right; color: #666;">
                    Data ditemukan: <?= count($filtered_data) ?>
                </span>
            </div>
        <?php endif; ?>
        
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th>Jenis Cuti</th>
                        <th>Surat Dokter</th>
                        <th>Alasan Cuti</th>
                        <th>Status</th>
                        <th>Waktu Persetujuan</th>
                        <th>Alasan Penolakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($filtered_data)): ?>
                        <?php foreach($filtered_data as $index => $cuti): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= date('d/m/Y', strtotime($cuti['tanggal_mulai'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($cuti['tanggal_akhir'])) ?></td>
                            <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                            <td>
                                <?php if (!empty($cuti['file_surat_dokter'])): ?>
                                    <a href="<?= htmlspecialchars($cuti['file_surat_dokter']) ?>" target="_blank" class="btn-lihat-surat">Lihat File</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="alasan-cuti">
                                <?php if (!empty($cuti['alasan'])): ?>
                                    <?= htmlspecialchars($cuti['alasan']) ?>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $status_class = 'status-' . str_replace(' ', '-', $cuti['status']);
                                ?>
                                <span class="<?= $status_class ?>"><?= htmlspecialchars($cuti['status']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($cuti['waktu_persetujuan'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($cuti['waktu_persetujuan'])) ?>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cuti['status'] == 'Ditolak' && !empty($cuti['alasan_penolakan'])): ?>
                                    <div class="alasan-penolakan"><?= htmlspecialchars($cuti['alasan_penolakan']) ?></div>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">
                                Tidak ada data cuti yang ditemukan
                                <?php if (empty($start_date) && empty($end_date) && empty($search_query)): ?>
                                    <br><br>
                                    <a href="formcutikaryawan.php" class="btn btn-ajukan" style="text-decoration: none;">Ajukan Cuti Pertama</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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