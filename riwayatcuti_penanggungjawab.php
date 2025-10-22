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

$query = "SELECT dpc.*, dk.nama_lengkap, dk.divisi, dk.role, dk.sisa_cuti_tahunan, dk.sisa_cuti_lustrum
          FROM data_pengajuan_cuti dpc 
          JOIN data_karyawan dk ON dpc.kode_karyawan = dk.kode_karyawan 
          WHERE dk.divisi = ? 
          ORDER BY dpc.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $divisi_penanggung_jawab);
$stmt->execute();
$result = $stmt->get_result();

$total_cuti = 0;
$diterima = 0;
$ditolak = 0;
$menunggu = 0;

$all_rows = $result->fetch_all(MYSQLI_ASSOC);

$filtered_rows = [];
foreach ($all_rows as $row) {
    $show_row = true;
    
    if (isset($_GET['status_filter']) && $_GET['status_filter'] != '' && $row['status'] != $_GET['status_filter']) {
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

    if (isset($_GET['jenis_cuti_filter']) && $_GET['jenis_cuti_filter'] != '' && $row['jenis_cuti'] != $_GET['jenis_cuti_filter']) {
        $show_row = false;
    }

    if ($show_row) {
        $filtered_rows[] = $row;
    }
}

foreach ($all_rows as $row) {
    $total_cuti++;
    switch ($row['status']) {
        case 'Diterima': $diterima++; break;
        case 'Ditolak': $ditolak++; break;
        case 'Menunggu Persetujuan': $menunggu++; break;
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
    <title>Riwayat Cuti Divisi - Penanggung Jawab</title>
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
            max-width: 1800px; 
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
        
        .status-menunggu {
            color: #ff9800;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .status-diterima {
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
        
        .alasan-cuti, .alasan-penolakan {
            max-width: 200px;
            word-wrap: break-word;
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
        }
        
        .alasan-cuti.empty, .alasan-penolakan.empty {
            color: #999;
            font-style: italic;
        }
        
        .alasan-penolakan {
            background-color: #fff5f5;
            padding: 8px;
            border-radius: 4px;
            border-left: 3px solid #f44336;
            color: #721c24;
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

        .file-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }

        .weekend-note {
            color: #666;
            font-size: 0.75rem;
            font-style: italic;
            margin-top: 3px;
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
        <h1>Riwayat Cuti Divisi</h1>
        <p>Riwayat pengajuan cuti divisi <?php echo htmlspecialchars($divisi_penanggung_jawab); ?></p>
    </div>
    
    <div class="container">
        <div class="info-divisi">
            <strong>Divisi Penanggung Jawab:</strong> <?php echo htmlspecialchars($divisi_penanggung_jawab); ?> | 
            <strong>Nama:</strong> <?php echo htmlspecialchars($nama_pj); ?>
            <div class="weekend-note">📝 Catatan: Sabtu & Minggu tidak terhitung sebagai hari cuti</div>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="status-message status-success">
                <?php 
                if ($_GET['status'] == 'disetujui') {
                    echo '✅ Cuti berhasil disetujui!';
                } elseif ($_GET['status'] == 'ditolak') {
                    echo '❌ Cuti berhasil ditolak!';
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Cuti</div>
                <div class="stat-number"><?php echo $total_cuti; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Diterima</div>
                <div class="stat-number"><?php echo $diterima; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Ditolak</div>
                <div class="stat-number"><?php echo $ditolak; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Menunggu</div>
                <div class="stat-number"><?php echo $menunggu; ?></div>
            </div>
        </div>

        <div class="filter-section">
            <form method="GET" class="filter-form">
                <input type="text" name="search_nama" placeholder="Cari Nama Karyawan..." value="<?php echo isset($_GET['search_nama']) ? htmlspecialchars($_GET['search_nama']) : ''; ?>">
                
                <select name="status_filter">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Persetujuan" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Menunggu Persetujuan') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Diterima" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                    <option value="Ditolak" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>

                <select name="jenis_cuti_filter">
                    <option value="">Semua Jenis Cuti</option>
                    <option value="Tahunan" <?php echo (isset($_GET['jenis_cuti_filter']) && $_GET['jenis_cuti_filter'] == 'Tahunan') ? 'selected' : ''; ?>>Tahunan</option>
                    <option value="Lustrum" <?php echo (isset($_GET['jenis_cuti_filter']) && $_GET['jenis_cuti_filter'] == 'Lustrum') ? 'selected' : ''; ?>>Lustrum</option>
                    <option value="Sakit" <?php echo (isset($_GET['jenis_cuti_filter']) && $_GET['jenis_cuti_filter'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                    <option value="Khusus - Menikah" <?php echo (isset($_GET['jenis_cuti_filter']) && $_GET['jenis_cuti_filter'] == 'Khusus - Menikah') ? 'selected' : ''; ?>>Khusus - Menikah</option>
                </select>
                
                <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                
                <button type="submit">Filter</button>
                <a href="riwayatcuti_penanggungjawab.php" style="color: var(--primary-color); text-decoration: none; margin-left: 10px;">Reset</a>
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
                    <?php 
                    $no = $offset + 1;
                    foreach ($rows_for_current_page as $row): 
                        $jumlah_hari_kerja = hitungHariKerja($row['tanggal_mulai'], $row['tanggal_akhir']);
                        $total_hari_kalender = (strtotime($row['tanggal_akhir']) - strtotime($row['tanggal_mulai'])) / (60 * 60 * 24) + 1;
                        
                        // Check if there's alasan_penolakan in the row
                        $alasan_penolakan = isset($row['alasan_penolakan']) ? $row['alasan_penolakan'] : '';
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['kode_karyawan']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['nama_karyawan']); ?>
                                <div class="cuti-details">
                                    <strong>Periode:</strong> <?php echo $total_hari_kalender; ?> hari kalender<br>
                                    <div class="hari-kerja-info">
                                        <strong>Hari Kerja:</strong> <?php echo $jumlah_hari_kerja; ?> hari (Senin-Jumat)
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo str_replace(' ', '-', $row['role']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($row['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_mulai']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_akhir']); ?></td>
                            <td>
                                <div class="alasan-cuti" title="<?php echo htmlspecialchars($row['alasan']); ?>">
                                    <?php echo htmlspecialchars($row['alasan']); ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($alasan_penolakan) && $row['status'] == 'Ditolak'): ?>
                                    <div class="alasan-penolakan" title="<?php echo htmlspecialchars($alasan_penolakan); ?>">
                                        <?php echo htmlspecialchars($alasan_penolakan); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alasan-penolakan empty">-</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['file_surat_dokter'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['file_surat_dokter']); ?>" class="file-link" target="_blank">Lihat File</a>
                                <?php else: ?>
                                    <div class="alasan-cuti empty">-</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['waktu_persetujuan'])): ?>
                                    <?php echo date('d/m/Y H:i', strtotime($row['waktu_persetujuan'])); ?>
                                <?php else: ?>
                                    <div class="alasan-cuti empty">-</div>
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

                    if ($page > 1) {
                        echo '<a href="?' . $base_url . $ampersand . 'page=' . ($page - 1) . '">‹ Sebelumnya</a>';
                    } else {
                        echo '<span class="disabled">‹ Sebelumnya</span>';
                    }

                    $range = 1;
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
                <p>Tidak ada data riwayat cuti yang sesuai dengan filter yang diterapkan.</p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="persetujuancuti_penanggungjawab.php" class="back-link">📋 Kembali ke Persetujuan Cuti</a>
            <a href="dashboard_penanggungjawab.php" class="back-link">← Kembali ke Dashboard</a>
        </div>
    </div>
</main>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>