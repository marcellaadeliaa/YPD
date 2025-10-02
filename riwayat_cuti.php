<?php
session_start();

// Inisialisasi variabel filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';

// --- DUMMY DATA (DATA CONTOH) ---
$riwayat_cuti = [
    [
        'id' => 1,
        'kode_karyawan' => '11223344',
        'nama_karyawan' => 'Xue',
        'divisi' => 'Keuangan',
        'tanggal_cuti' => '2025-09-08',
        'jenis_cuti' => 'Lustrum',
        'status' => 'Diterima',
        'waktu_persetujuan' => '2025-09-09 15:47:00'
    ],
    [
        'id' => 2,
        'kode_karyawan' => '11223355',
        'nama_karyawan' => 'Adel',
        'divisi' => 'SDM',
        'tanggal_cuti' => '2025-09-08',
        'jenis_cuti' => 'Tahunan',
        'status' => 'Diterima',
        'waktu_persetujuan' => '2025-09-10 14:34:00'
    ],
    [
        'id' => 3,
        'kode_karyawan' => '11223366',
        'nama_karyawan' => 'Budi Santoso',
        'divisi' => 'Training',
        'tanggal_cuti' => '2025-09-11',
        'jenis_cuti' => 'Tahunan',
        'status' => 'Ditolak',
        'waktu_persetujuan' => '2025-09-12 10:15:00'
    ],
    [
        'id' => 4,
        'kode_karyawan' => '11223377',
        'nama_karyawan' => 'Siti Rahayu',
        'divisi' => 'Konsultasi',
        'tanggal_cuti' => '2025-09-15',
        'jenis_cuti' => 'Sakit',
        'status' => 'Diterima',
        'waktu_persetujuan' => '2025-09-16 09:20:00'
    ],
    [
        'id' => 5,
        'kode_karyawan' => '11223388',
        'nama_karyawan' => 'Ahmad Fauzi',
        'divisi' => 'Wisma',
        'tanggal_cuti' => '2025-09-20',
        'jenis_cuti' => 'Tahunan',
        'status' => 'Diterima',
        'waktu_persetujuan' => '2025-09-21 11:30:00'
    ],
];

// Filter data berdasarkan input
$filtered_data = $riwayat_cuti;

// Filter berdasarkan tanggal
if (!empty($start_date) && !empty($end_date)) {
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    $filtered_data = array_filter($filtered_data, function($cuti) use ($start_timestamp, $end_timestamp) {
        $cuti_timestamp = strtotime($cuti['tanggal_cuti']);
        return $cuti_timestamp >= $start_timestamp && $cuti_timestamp <= $end_timestamp;
    });
} elseif (!empty($start_date)) {
    $start_timestamp = strtotime($start_date);
    $filtered_data = array_filter($filtered_data, function($cuti) use ($start_timestamp) {
        return strtotime($cuti['tanggal_cuti']) >= $start_timestamp;
    });
} elseif (!empty($end_date)) {
    $end_timestamp = strtotime($end_date);
    $filtered_data = array_filter($filtered_data, function($cuti) use ($end_timestamp) {
        return strtotime($cuti['tanggal_cuti']) <= $end_timestamp;
    });
}

// Filter berdasarkan pencarian
if (!empty($search_query)) {
    $search_lower = strtolower($search_query);
    $filtered_data = array_filter($filtered_data, function($cuti) use ($search_lower) {
        return strpos(strtolower($cuti['nama_karyawan']), $search_lower) !== false ||
               strpos(strtolower($cuti['kode_karyawan']), $search_lower) !== false ||
               strpos(strtolower($cuti['divisi']), $search_lower) !== false;
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
<title>Riwayat Cuti Pegawai - Admin</title>
<style>
    /* Menggunakan style yang konsisten dari halaman admin sebelumnya */
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 50px; height: 50px; object-fit: contain; }
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
    
    /* Style untuk filter section */
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
    .filter-row { display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .filter-group.date-group { min-width: 150px; }
    .filter-group.search-group { flex-grow: 1; min-width: 200px; }
    
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; }
    .btn-cari { background-color: #4a3f81; }
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    .status-diterima { color: #28a745; font-weight: 600; }
    .status-ditolak { color: #d9534f; font-weight: 600; }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    .filter-info { 
        background: #e7f3ff; 
        padding: 10px 15px; 
        border-radius: 6px; 
        margin-bottom: 15px; 
        font-size: 14px; 
        border-left: 4px solid #4a3f81;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .filter-row { flex-direction: column; }
        .filter-group { width: 100%; }
        .action-bar { flex-direction: column; }
        .btn { width: 100%; }
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
          <ul>
                <li><a href="logout2.php">Logout</a></li>
            </ul>
        </li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, Cell!</h1>
    <p class="admin-title">Administrator</p>

    <div class="card">
        <h2 class="page-title">Riwayat Cuti Pegawai</h2>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="riwayat_cuti.php">
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
                        <label for="search">Cari (Nama/NIK/Divisi/Posisi)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan nama, NIK, atau divisi..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="riwayat_cuti.php" class="btn btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <!-- Info Filter Aktif -->
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

        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Kode Karyawan</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi/Posisi</th>
                    <th>Tanggal Cuti</th>
                    <th>Jenis Cuti</th>
                    <th>Status</th>
                    <th>Persetujuan Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_data)): ?>
                    <?php foreach($filtered_data as $cuti): ?>
                    <tr>
                        <td><?= htmlspecialchars($cuti['kode_karyawan']) ?></td>
                        <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                        <td><?= htmlspecialchars($cuti['divisi']) ?></td>
                        <td><?= date('d/m/Y', strtotime($cuti['tanggal_cuti'])) ?></td>
                        <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                        <td>
                            <?php if ($cuti['status'] == 'Diterima'): ?>
                                <span class="status-diterima">Diterima</span>
                            <?php elseif ($cuti['status'] == 'Ditolak'): ?>
                                <span class="status-ditolak">Ditolak</span>
                            <?php else: ?>
                                <?= htmlspecialchars($cuti['status']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($cuti['waktu_persetujuan'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">Tidak ada data cuti yang ditemukan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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

</body>
</html>