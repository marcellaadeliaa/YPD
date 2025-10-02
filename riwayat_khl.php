<?php
session_start();

// Inisialisasi variabel filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';

// --- DUMMY DATA (DATA CONTOH) ---
// Data Anda tetap di sini, saya singkat agar tidak terlalu panjang
$riwayat_khl = [
    [
        'id' => 1, 'kode_khl' => '11221384', 'nama_karyawan' => 'Adrianna', 'jenis_khl' => 'Training', 'projek' => 'Pelatihan Karyawan', 'tanggal_kerja' => '2025-09-10', 'jam_mulai_kerja' => '07:00:00', 'jam_selesai_kerja' => '16:00:00', 'tanggal_libur' => '2025-09-11', 'jam_mulai_libur' => '', 'jam_selesai_libur' => '', 'status' => 'Ditolak'
    ],
    [
        'id' => 2, 'kode_khl' => '11221385', 'nama_karyawan' => 'Budi Santoso', 'jenis_khl' => 'Proyek Khusus', 'projek' => 'Implementasi Sistem Baru', 'tanggal_kerja' => '2025-09-12', 'jam_mulai_kerja' => '08:00:00', 'jam_selesai_kerja' => '17:00:00', 'tanggal_libur' => '2025-09-13', 'jam_mulai_libur' => '09:00:00', 'jam_selesai_libur' => '14:00:00', 'status' => 'Diterima'
    ],
    [
        'id' => 3, 'kode_khl' => '11221386', 'nama_karyawan' => 'Siti Rahayu', 'jenis_khl' => 'Lembur', 'projek' => 'Laporan Bulanan', 'tanggal_kerja' => '2025-09-15', 'jam_mulai_kerja' => '17:00:00', 'jam_selesai_kerja' => '21:00:00', 'tanggal_libur' => '2025-09-16', 'jam_mulai_libur' => '', 'jam_selesai_libur' => '', 'status' => 'Diterima'
    ],
    [
        'id' => 4, 'kode_khl' => '11221387', 'nama_karyawan' => 'Ahmad Fauzi', 'jenis_khl' => 'Training', 'projek' => 'Workshop Leadership', 'tanggal_kerja' => '2025-09-18', 'jam_mulai_kerja' => '08:30:00', 'jam_selesai_kerja' => '16:30:00', 'tanggal_libur' => '2025-09-19', 'jam_mulai_libur' => '10:00:00', 'jam_selesai_libur' => '15:00:00', 'status' => 'Menunggu'
    ],
    [
        'id' => 5, 'kode_khl' => '11221388', 'nama_karyawan' => 'Rina Melati', 'jenis_khl' => 'Proyek Khusus', 'projek' => 'Event Company Gathering', 'tanggal_kerja' => '2025-09-20', 'jam_mulai_kerja' => '06:00:00', 'jam_selesai_kerja' => '18:00:00', 'tanggal_libur' => '2025-09-21', 'jam_mulai_libur' => '', 'jam_selesai_libur' => '', 'status' => 'Diterima'
    ],
];


// Filter data berdasarkan input
$filtered_data = $riwayat_khl;

// Filter berdasarkan tanggal
if (!empty($start_date) && !empty($end_date)) {
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    $filtered_data = array_filter($filtered_data, function($khl) use ($start_timestamp, $end_timestamp) {
        $khl_timestamp = strtotime($khl['tanggal_kerja']);
        return $khl_timestamp >= $start_timestamp && $khl_timestamp <= $end_timestamp;
    });
} elseif (!empty($start_date)) {
    $start_timestamp = strtotime($start_date);
    $filtered_data = array_filter($filtered_data, function($khl) use ($start_timestamp) {
        return strtotime($khl['tanggal_kerja']) >= $start_timestamp;
    });
} elseif (!empty($end_date)) {
    $end_timestamp = strtotime($end_date);
    $filtered_data = array_filter($filtered_data, function($khl) use ($end_timestamp) {
        return strtotime($khl['tanggal_kerja']) <= $end_timestamp;
    });
}

// Filter berdasarkan pencarian
if (!empty($search_query)) {
    $search_lower = strtolower($search_query);
    $filtered_data = array_filter($filtered_data, function($khl) use ($search_lower) {
        return strpos(strtolower($khl['nama_karyawan']), $search_lower) !== false ||
               strpos(strtolower($khl['kode_khl']), $search_lower) !== false ||
               strpos(strtolower($khl['projek']), $search_lower) !== false ||
               strpos(strtolower($khl['jenis_khl']), $search_lower) !== false;
    });
}

// Reset array keys
$filtered_data = array_values($filtered_data);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat KHL Pegawai - Admin</title>
<style>
    /* Menggunakan style yang konsisten dari halaman admin sebelumnya */
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; flex-wrap: wrap; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    /* Menyamakan style logo dengan perbaikan sebelumnya agar konsisten */
    .logo img { width: 130px; height: 50px; object-fit: contain; }
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
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    
    /* Style untuk filter section */
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
    
    /* Status styles */
    .status-diterima { color: #28a745; font-weight: 600; }
    .status-ditolak { color: #d9534f; font-weight: 600; }
    .status-menunggu { color: #f0ad4e; font-weight: 600; }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    .filter-info { background: #e7f3ff; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #4a3f81; display: flex; justify-content: space-between; align-items: center; }
    
    /* Responsive */
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
    <h1>Welcome, Cell!</h1>
    <p class="admin-title">Administrator</p>

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
    // Validasi tanggal: end date tidak boleh kurang dari start date
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