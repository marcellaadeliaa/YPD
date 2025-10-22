<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$divisi_penanggung_jawab = $user['divisi'];
$nama_pj = $user['nama_lengkap'];

$query = "SELECT dpk.*, dk.nama_lengkap, dk.divisi, dk.role 
          FROM data_pengajuan_khl dpk 
          JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan 
          WHERE dk.divisi = ? 
          ORDER BY dpk.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $divisi_penanggung_jawab);
$stmt->execute();
$result = $stmt->get_result();

$total_khl = 0;
$disetujui = 0;
$ditolak = 0;
$pending = 0;

$all_rows = $result->fetch_all(MYSQLI_ASSOC);

$filtered_rows = [];
foreach ($all_rows as $row) {
    $show_row = true;
    
    if (isset($_GET['status_filter']) && $_GET['status_filter'] != '' && $row['status_khl'] != $_GET['status_filter']) {
        $show_row = false;
    }
    
    if (isset($_GET['start_date']) && $_GET['start_date'] != '' && date('Y-m-d', strtotime($row['created_at'])) < $_GET['start_date']) {
        $show_row = false;
    }
    
    if (isset($_GET['end_date']) && $_GET['end_date'] != '' && date('Y-m-d', strtotime($row['created_at'])) > $_GET['end_date']) {
        $show_row = false;
    }

    if (isset($_GET['search_nama']) && !empty(trim($_GET['search_nama']))) {
        if (stripos($row['nama_lengkap'], trim($_GET['search_nama'])) === false) {
            $show_row = false;
        }
    }

    if ($show_row) {
        $filtered_rows[] = $row;
    }
}


foreach ($all_rows as $row) {
    $total_khl++;
    switch ($row['status_khl']) {
        case 'disetujui': $disetujui++; break;
        case 'ditolak': $ditolak++; break;
        case 'pending': $pending++; break;
    }
}

$limit = 5; 
$total_records = count($filtered_rows);
$total_pages = ceil($total_records / $limit);

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;

$rows_for_current_page = array_slice($filtered_rows, $offset, $limit);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat KHL Divisi - Penanggung Jawab</title>
    <style>
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
        
        .role-karyawan {
            background: #d4edda;
            color: #155724;
        }
        
        .role-penanggung-jawab {
            background: #cce7ff;
            color: #004085;
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

        .pagination-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .pagination-container a, .pagination-container span {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            color: var(--primary-color);
            background: #f0f0f0;
            border: 1px solid #ddd;
            font-weight: 500;
            transition: background-color 0.3s, color 0.3s;
        }

        .pagination-container a:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .pagination-container span.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            cursor: default;
        }

        .pagination-container span.disabled {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
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
        <h1>Riwayat Kerja Hari Libur (KHL)</h1>
        <p>Riwayat pengajuan KHL divisi <?php echo htmlspecialchars($divisi_penanggung_jawab); ?></p>
    </div>
    
    <div class="container">
        <div class="info-divisi">
            <strong>Divisi Penanggung Jawab:</strong> <?php echo htmlspecialchars($divisi_penanggung_jawab); ?> | 
            <strong>Nama:</strong> <?php echo htmlspecialchars($nama_pj); ?>
        </div>

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

        <div class="filter-section">
            <form method="GET" class="filter-form">
                <input type="text" name="search_nama" placeholder="Cari Nama Karyawan..." value="<?php echo isset($_GET['search_nama']) ? htmlspecialchars($_GET['search_nama']) : ''; ?>">
                
                <select name="status_filter">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="disetujui" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="ditolak" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
                
                <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                
                <button type="submit">Filter</button>
                <a href="riwayatkhl_penanggungjawab.php" style="color: var(--primary-color); text-decoration: none; margin-left: 10px;">Reset</a>
            </form>
        </div>
        
        <?php if (!empty($rows_for_current_page)): ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Proyek</th>
                        <th>Tanggal KHL</th>
                        <th>Jam Kerja</th>
                        <th>Tanggal Cuti</th>
                        <th>Jam Cuti</th>
                        <th>Status</th>
                        <th>Alasan Penolakan</th>
                        <th>Tanggal Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $offset + 1;
                    foreach ($rows_for_current_page as $row): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['kode_karyawan']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo str_replace(' ', '-', $row['role']); ?>">
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
                                    echo $status_text[$row['status_khl']]; 
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
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php 
                    endforeach; 
                    ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
<div class="pagination-wrapper">
    <?php
    $query_params = $_GET;
    unset($query_params['page']);
    $base_url = http_build_query($query_params);
    $ampersand = !empty($base_url) ? '&' : '';
    
    $range = 2; 

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
<?php endif; ?>

        <?php else: ?>
            <div class="no-data">
                <p>Tidak ada data riwayat KHL yang sesuai dengan filter yang diterapkan.</p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="persetujuankhl_penanggungjawab.php" class="back-link">📋 Kembali ke Persetujuan KHL</a>
            <a href="dashboard_penanggungjawab.php" class="back-link">← Kembali ke Dashboard</a>
        </div>
    </div>
</main>
</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>