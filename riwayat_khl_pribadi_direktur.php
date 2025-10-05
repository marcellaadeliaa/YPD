<?php
session_start();
require 'config.php';

// Simulasi user direktur (untuk keperluan development)
// Dalam produksi, bagian ini harus diganti dengan sistem login yang sesungguhnya
$_SESSION['user'] = [
    'role' => 'direktur',
    'nama_lengkap' => 'Direktur Perusahaan',
    'kode_karyawan' => 'DIR001'
];

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];
$kode_direktur = $user['kode_karyawan'];

// Inisialisasi variabel filter
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query dengan filter untuk data pribadi direktur
$query = "SELECT * FROM data_pengajuan_khl 
          WHERE role = 'direktur' AND kode_karyawan = ?";

$params = [$kode_direktur];
$types = 's';

// Tambahkan kondisi filter berdasarkan input
if (!empty($status_filter)) {
    $query .= " AND status_khl = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($start_date)) {
    $query .= " AND DATE(tanggal_khl) >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if (!empty($end_date)) {
    $query .= " AND DATE(tanggal_khl) <= ?";
    $params[] = $end_date;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (proyek LIKE ? OR kode_karyawan LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$query .= " ORDER BY tanggal_khl DESC";

// Prepare dan execute query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Hitung statistik untuk data pribadi direktur
$stats_query = "SELECT 
    COUNT(*) as total_khl,
    SUM(CASE WHEN status_khl = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status_khl = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status_khl = 'pending' THEN 1 ELSE 0 END) as pending
    FROM data_pengajuan_khl 
    WHERE role = 'direktur' AND kode_karyawan = ?";
    
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("s", $kode_direktur);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$total_khl = $stats['total_khl'] ?? 0;
$disetujui = $stats['disetujui'] ?? 0;
$ditolak = $stats['ditolak'] ?? 0;
$pending = $stats['pending'] ?? 0;

$stats_stmt->close();

// Reset pointer result untuk iterasi data
if ($result) {
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat KHL Pribadi - Direktur</title>
    <style>
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE; 
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF; 
            --text-color-light: #fff; 
            --text-color-dark: #2e1f4f; 
            --shadow-light: rgba(0,0,0,0.15); 
        }
        
        body { 
            margin: 0; 
            font-family: 'Segoe UI', sans-serif; 
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh; 
            color: var(--text-color-light); 
            padding-bottom: 40px; 
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
            max-width: 1600px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        
        .heading-section h1 { 
            font-size: 2.5rem; 
            margin: 0; 
            color: #fff;
        }
        
        .heading-section p { 
            font-size: 1.1rem; 
            margin-top: 5px; 
            opacity: 0.9; 
            margin-bottom: 30px; 
            color: #fff;
        }
        
        .container {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px var(--shadow-light);
            margin-top: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px var(--shadow-light);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px var(--shadow-light);
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-pending {
            color: #ff9800;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .status-disetujui {
            color: #4caf50;
            font-weight: bold;
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .status-ditolak {
            color: #f44336;
            font-weight: bold;
            background-color: #f8d7da;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1rem;
        }
        
        .info-divisi {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
            color: var(--text-color-dark);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            background: #f0f0f0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-form select, .filter-form input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .filter-form button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .filter-form button:hover {
            background: var(--accent-color);
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .search-box button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .alasan-penolakan {
            max-width: 300px;
            word-wrap: break-word;
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
        }
        
        .alasan-penolakan.empty {
            color: #999;
            font-style: italic;
        }
        
        .role-badge {
            background: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .role-direktur {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .filter-info {
            background: #e7f3ff;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #2196F3;
            font-size: 0.9rem;
        }
        
        .result-count {
            background: #d4edda;
            padding: 8px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #155724;
        }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboard_direktur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="riwayat_cuti_pribadi_direktur.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="riwayat_khl_pribadi_direktur.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="profil_direktur.php">Profil</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="heading-section">
        <h1>Riwayat Kerja Hari Libur (KHL) Pribadi</h1>
        <p>Riwayat pengajuan KHL pribadi Anda sebagai Direktur</p>
    </div>
    
    <div class="container">
        <div class="info-divisi">
            <strong>Peran:</strong> Direktur | 
            <strong>Nama:</strong> <?php echo htmlspecialchars($nama_direktur); ?> |
            <strong>Kode:</strong> <?php echo htmlspecialchars($kode_direktur); ?>
        </div>

        <!-- Tampilkan pesan status dari persetujuan -->
        <?php if (isset($_GET['status'])): ?>
            <div class="status-message status-success">
                <?php 
                if ($_GET['status'] == 'disetujui') {
                    echo '✅ KHL berhasil disetujui!';
                } elseif ($_GET['status'] == 'ditolak') {
                    echo '❌ KHL berhasil ditolak!';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total KHL</div>
                <div class="stat-number"><?php echo $total_khl; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Disetujui</div>
                <div class="stat-number"><?php echo $disetujui; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Ditolak</div>
                <div class="stat-number"><?php echo $ditolak; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Menunggu</div>
                <div class="stat-number"><?php echo $pending; ?></div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="filter-section">
            <form method="GET" class="search-box">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Cari berdasarkan proyek atau kode karyawan...">
                <button type="submit">🔍 Cari</button>
            </form>

            <!-- Filter Section -->
            <form method="GET" class="filter-form">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="status_filter">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="disetujui" <?php echo ($status_filter == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="ditolak" <?php echo ($status_filter == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
                
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" placeholder="Dari Tanggal">
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" placeholder="Sampai Tanggal">
                
                <button type="submit">Filter</button>
                <a href="riwayat_khl_pribadi_direktur.php" style="color: var(--primary-color); text-decoration: none; margin-left: 10px;">Reset</a>
            </form>
        </div>

        <!-- Info Filter Aktif -->
        <?php 
        $active_filters = [];
        if (!empty($status_filter)) $active_filters[] = "Status: " . ucfirst($status_filter);
        if (!empty($start_date)) $active_filters[] = "Dari: " . $start_date;
        if (!empty($end_date)) $active_filters[] = "Sampai: " . $end_date;
        if (!empty($search)) $active_filters[] = "Pencarian: " . $search;
        
        if (!empty($active_filters)): 
        ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong> <?php echo implode(' • ', $active_filters); ?>
            </div>
        <?php endif; ?>

        <!-- Jumlah Hasil -->
        <?php 
        $filtered_count = $result ? $result->num_rows : 0;
        if ($filtered_count > 0): 
        ?>
            <div class="result-count">
                Menampilkan <?php echo $filtered_count; ?> data KHL pribadi
                <?php if ($filtered_count < $total_khl): ?>
                    (difilter dari total <?php echo $total_khl; ?> data)
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID KHL</th>
                        <th>Kode</th>
                        <th>Divisi</th>
                        <th>Jabatan</th>
                        <th>Role</th>
                        <th>Proyek</th>
                        <th>Tanggal KHL</th>
                        <th>Jam Kerja</th>
                        <th>Tanggal Cuti</th>
                        <th>Jam Cuti</th>
                        <th>Status</th>
                        <th>Alasan Penolakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['id_khl'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['kode_karyawan']); ?></td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                            <td>
                                <span class="role-badge role-direktur">
                                    <?php echo htmlspecialchars(ucfirst($row['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['proyek']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_khl']); ?></td>
                            <td><?php echo htmlspecialchars($row['jam_mulai_kerja'] . ' - ' . $row['jam_akhir_kerja']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_cuti_khl']); ?></td>
                            <td><?php echo htmlspecialchars($row['jam_mulai_cuti_khl'] . ' - ' . $row['jam_akhir_cuti_khl']); ?></td>
                            <td>
                                <span class="status-<?php echo $row['status_khl']; ?>">
                                    <?php 
                                    $status_text = [
                                        'pending' => 'Menunggu',
                                        'disetujui' => 'Disetujui',
                                        'ditolak' => 'Ditolak'
                                    ];
                                    echo $status_text[$row['status_khl']] ?? $row['status_khl']; 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status_khl'] == 'ditolak' && !empty($row['alasan_penolakan'])): ?>
                                    <div class="alasan-penolakan" title="<?php echo htmlspecialchars($row['alasan_penolakan']); ?>">
                                        <?php echo htmlspecialchars($row['alasan_penolakan']); ?>
                                    </div>
                                <?php elseif ($row['status_khl'] == 'ditolak'): ?>
                                    <div class="alasan-penolakan empty">Tidak ada alasan</div>
                                <?php else: ?>
                                    <div class="alasan-penolakan empty">-</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>Belum ada riwayat KHL pribadi</p>
                <?php if (!empty($active_filters)): ?>
                    <p><small>Atau tidak ada data yang sesuai dengan filter yang diterapkan. <a href="riwayat_khl_pribadi_direktur.php" style="color: var(--primary-color);">Tampilkan semua data</a></small></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="pengajuan_khl_direktur.php" class="back-link">📋 Ajukan KHL Baru</a>
            <a href="dashboard_direktur.php" class="back-link">← Kembali ke Dashboard</a>
        </div>
    </div>
</main>
</body>
</html>

<?php
// Tutup koneksi
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>