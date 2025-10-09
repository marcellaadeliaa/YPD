<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';

$query = "SELECT 
            dk.nama_lengkap,
            dpk.* 
          FROM data_pengajuan_khl dpk 
          JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan 
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($start_date)) {
    $query .= " AND dpk.tanggal_khl >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if (!empty($end_date)) {
    $query .= " AND dpk.tanggal_khl <= ?";
    $params[] = $end_date;
    $types .= 's';
}

if (!empty($search_query)) {
    $query .= " AND (dk.nama_lengkap LIKE ? OR dpk.kode_karyawan LIKE ? OR dpk.proyek LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " ORDER BY dpk.created_at DESC";

$stmt = $conn->prepare($query);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $riwayat_khl = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $riwayat_khl = [];
    error_log("Error preparing query: " . $conn->error);
}

$status_mapping = [
    'disetujui' => 'Diterima',
    'ditolak' => 'Ditolak',
    'pending' => 'Menunggu'
];

$filtered_data = [];
foreach ($riwayat_khl as $khl) {
    $filtered_data[] = [
        'id' => $khl['id_khl'],
        'kode_khl' => $khl['id_khl'],
        'nama_karyawan' => $khl['nama_lengkap'],
        'jenis_khl' => $khl['divisi'], 
        'projek' => $khl['proyek'],
        'tanggal_kerja' => $khl['tanggal_khl'],
        'jam_mulai_kerja' => $khl['jam_mulai_kerja'],
        'jam_selesai_kerja' => $khl['jam_akhir_kerja'],
        'tanggal_libur' => $khl['tanggal_cuti_khl'],
        'jam_mulai_libur' => $khl['jam_mulai_cuti_khl'],
        'jam_selesai_libur' => $khl['jam_akhir_cuti_khl'],
        'status' => $status_mapping[$khl['status_khl']] ?? $khl['status_khl']
    ];
}

$nama_user = 'Admin';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query_admin = $conn->prepare("SELECT nama_lengkap FROM data_karyawan WHERE id_karyawan = ? AND role = 'admin'");
    if ($query_admin) {
        $query_admin->bind_param("i", $user_id);
        $query_admin->execute();
        $result_admin = $query_admin->get_result();
        if ($result_admin && $result_admin->num_rows > 0) {
            $admin_data = $result_admin->fetch_assoc();
            $nama_user = $admin_data['nama_lengkap'];
        }
        $query_admin->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat KHL Pegawai - Admin</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; flex-wrap: wrap; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 140px; height: 50px; object-fit: contain; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; align-items: center; }
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
    .page-title { font-size: 30px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #e0e0e0; }
    .filter-row { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .filter-group.date-group { min-width: 150px; }
    .filter-group.search-group { flex-grow: 1; min-width: 200px; }
    
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: background-color 0.3s; text-decoration: none; text-align: center; }
    .btn-cari { background-color: #4a3f81; }
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    
    .data-table-container { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; color: #333; position: sticky; top: 0; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    
    .status-diterima { color: #28a745; font-weight: 600; }
    .status-ditolak { color: #d9534f; font-weight: 600; }
    .status-menunggu { color: #f0ad4e; font-weight: 600; }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    .filter-info { background: #e7f3ff; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #4a3f81; display: flex; justify-content: space-between; align-items: center; }
    
    @media (max-width: 992px) {
        header { flex-direction: column; align-items: flex-start; }
        nav ul { flex-direction: column; gap: 10px; width: 100%; margin-top: 15px; align-items: flex-start; }
        nav li ul { position: static; box-shadow: none; border: none; padding-left: 20px; }
    }
    @media (max-width: 768px) {
        .filter-row { flex-direction: column; align-items: stretch; }
        .filter-group { width: 100%; }
        .action-bar { flex-direction: column; }
        .btn { width: 100%; box-sizing: border-box; }
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
            <li><a href="dashboardadmin.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
                    <li><a href="kalender_cuti.php">Kalender Cuti</a></li>
                    <li><a href="daftar_sisa_cuti.php">Sisa Cuti Karyawan</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="riwayat_khl.php">Riwayat KHL</a></li>
                    <li><a href="kalender_khl.php">Kalender KHL</a></li>
                </ul>
            </li>
            <li><a href="#">Lamaran Kerja ▾</a>
                <ul>
                    <li><a href="administrasi_pelamar.php">Administrasi Pelamar</a></li>
                    <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
                </ul>
            </li>
            <li><a href="#">Karyawan ▾</a>
                <ul>
                    <li><a href="data_karyawan.php">Data Karyawan</a></li>
                </ul>
            </li>
            <li><a href="logout2.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="card">
        <h2 class="page-title">Riwayat KHL Pegawai</h2>
        
        <div class="filter-section">
            <form method="GET" action="">
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
                        <label for="search">Cari (Nama/Kode/Projek)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan nama, kode, atau projek..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="riwayat_khl.php" class="btn btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <?php if (!empty($start_date) || !empty($end_date) || !empty($search_query)): ?>
            <div class="filter-info">
                <span>
                    <strong>Filter Aktif:</strong>
                    <?php 
                    $filters = [];
                    if (!empty($start_date)) $filters[] = "Dari: " . date('d/m/Y', strtotime($start_date));
                    if (!empty($end_date)) $filters[] = "Sampai: " . date('d/m/Y', strtotime($end_date));
                    if (!empty($search_query)) $filters[] = "Pencarian: '" . htmlspecialchars($search_query) . "'";
                    echo implode(' | ', $filters);
                    ?>
                </span>
                <span style="color: #666;">
                    <strong>Data ditemukan: <?= count($filtered_data) ?></strong>
                </span>
            </div>
        <?php endif; ?>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Code</th>
                        <th>Name</th>
                        <th>Jenis KHL</th>
                        <th>Projek</th>
                        <th>Tanggal Kerja</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Tanggal Libur</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($filtered_data)): ?>
                        <?php foreach($filtered_data as $khl): ?>
                        <tr>
                            <td><?= htmlspecialchars($khl['kode_khl']) ?></td>
                            <td><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($khl['jenis_khl']) ?></td>
                            <td><?= htmlspecialchars($khl['projek']) ?></td>
                            <td><?= date('d/m/Y', strtotime($khl['tanggal_kerja'])) ?></td>
                            <td><?= $khl['jam_mulai_kerja'] ? date('H:i', strtotime($khl['jam_mulai_kerja'])) : '-' ?></td>
                            <td><?= $khl['jam_selesai_kerja'] ? date('H:i', strtotime($khl['jam_selesai_kerja'])) : '-' ?></td>
                            <td><?= $khl['tanggal_libur'] ? date('d/m/Y', strtotime($khl['tanggal_libur'])) : '-' ?></td>
                            <td><?= $khl['jam_mulai_libur'] ? date('H:i', strtotime($khl['jam_mulai_libur'])) : '-' ?></td>
                            <td><?= $khl['jam_selesai_libur'] ? date('H:i', strtotime($khl['jam_selesai_libur'])) : '-' ?></td>
                            <td>
                                <?php if ($khl['status'] == 'Diterima'): ?>
                                    <span class="status-diterima">Diterima</span>
                                <?php elseif ($khl['status'] == 'Ditolak'): ?>
                                    <span class="status-ditolak">Ditolak</span>
                                <?php elseif ($khl['status'] == 'Menunggu'): ?>
                                    <span class="status-menunggu">Menunggu</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($khl['status']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="no-data">Tidak ada data KHL yang ditemukan</td>
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