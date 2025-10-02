<?php
session_start();

// Sertakan file koneksi database Anda di sini
// contoh: include 'config/koneksi.php';

// Data Penanggung Jawab yang sedang login (seharusnya dari session setelah login)
$user_data = [
    'nik' => 'PJ001',
    'nama' => 'Ria',
    'divisi' => 'Training'
];

// Inisialisasi variabel filter dari URL
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';

// --- LOGIKA PENGAMBILAN DATA DARI DATABASE ---
// Kode ini menggantikan data dummy. Sesuaikan dengan skema database Anda.

$filtered_data = [];
// Inisialisasi array kosong

/*
// Contoh implementasi dengan MySQLi:

// 1. Buat koneksi ke database
$conn = new mysqli("localhost", "username", "password", "nama_database");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 2. Query dasar untuk mengambil riwayat KHL pribadi penanggung jawab
$sql = "SELECT id, tanggal_khl, proyek, jam_kerja, tanggal_cuti_khl, jam_cuti_khl, status, waktu_persetujuan 
        FROM pengajuan_khl 
        WHERE kode_karyawan = ?";

$params = [$user_data['nik']];
$types = "s";

// 3. Tambahkan filter ke query jika ada
if (!empty($start_date)) {
    $sql .= " AND tanggal_khl >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (!empty($end_date)) {
    $sql .= " AND tanggal_khl <= ?";
    $params[] = $end_date;
    $types .= "s";
}
if (!empty($search_query)) {
    $sql .= " AND (LOWER(proyek) LIKE ? OR LOWER(status) LIKE ?)";
    $search_param = '%' . strtolower($search_query) . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " ORDER BY tanggal_khl DESC";

// 4. Eksekusi query dengan prepared statement
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Gabungkan jam kerja dan jam cuti jika disimpan terpisah di db
        // Contoh: $row['jam_kerja'] = $row['jam_mulai_kerja'] . ' - ' . $row['jam_akhir_kerja'];
        // Contoh: $row['jam_cuti_khl'] = $row['jam_mulai_cuti'] . ' - ' . $row['jam_akhir_cuti'];
        $filtered_data[] = $row;
    }
    $stmt->close();
}
$conn->close();

*/

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat KHL Pribadi - Penanggung Jawab</title>
<style>
    :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); }
    body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; color: var(--text-color-light); padding-bottom: 40px; }
    header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
    .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
    nav li { position: relative; }
    nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
    nav a:hover { color: var(--accent-color); }
    nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
    nav li:hover > ul { display: block; }
    nav li ul li { padding: 5px 20px; }
    nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { text-align:left; font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); color: #333; }
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
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    .btn-ajukan { background-color: #2ecc71; }
    .btn-ajukan:hover { background-color: #27ae60; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    .status-diterima { color: #28a745; font-weight: 600; }
    .status-ditolak { color: #d9534f; font-weight: 600; }
    .status-menunggu { color: #f39c12; font-weight: 600; }
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    .filter-info { background: #e7f3ff; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; border-left: 4px solid #4a3f81; }
    .user-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4a3f81; }
    @media (max-width: 768px) { .filter-row { flex-direction: column; } .filter-group { width: 100%; } .action-bar { flex-direction: column; } .btn { width: 100%; } .data-table { font-size: 12px; } .data-table th, .data-table td { padding: 8px 10px; } }
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
    <h1>Welcome, <?php echo htmlspecialchars($user_data['nama']); ?>!</h1>
    <p class="admin-title">Riwayat Pengajuan KHL Pribadi Anda</p>

    <div class="card">
        <h2 class="page-title">Riwayat KHL Pribadi</h2>
        
        <div class="user-info">
            <strong>Informasi Anda:</strong><br>
            NIK: <?php echo htmlspecialchars($user_data['nik']); ?> | 
            Nama: <?php echo htmlspecialchars($user_data['nama']); ?> | 
            Divisi: <?php echo htmlspecialchars($user_data['divisi']); ?>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="riwayat_khl_pribadi_penanggungjawab.php">
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
                        <label for="search">Cari (Proyek/Status)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan proyek atau status..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="riwayat_khl_pribadi_penanggungjawab.php" class="btn btn-reset">Reset Filter</a>
                    <a href="pengajuankhl_penanggungjawab.php" class="btn btn-ajukan">+ Ajukan KHL Baru</a>
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

        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal KHL</th>
                    <th>Proyek</th>
                    <th>Jam Kerja</th>
                    <th>Tanggal Cuti KHL</th>
                    <th>Jam Cuti KHL</th>
                    <th>Status</th>
                    <th>Persetujuan Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_data)): ?>
                    <?php foreach($filtered_data as $index => $khl): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= date('d/m/Y', strtotime($khl['tanggal_khl'])) ?></td>
                        <td><?= htmlspecialchars($khl['proyek']) ?></td>
                        <td><?= htmlspecialchars($khl['jam_kerja']) ?></td>
                        <td><?= date('d/m/Y', strtotime($khl['tanggal_cuti_khl'])) ?></td>
                        <td><?= htmlspecialchars($khl['jam_cuti_khl']) ?></td>
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
                        <td>
                            <?php if (!empty($khl['waktu_persetujuan'])): ?>
                                <?= date('d/m/Y H:i', strtotime($khl['waktu_persetujuan'])) ?>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">Tidak ada data KHL yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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