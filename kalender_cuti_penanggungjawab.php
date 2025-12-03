<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_pj = $user['nama_lengkap'];
$divisi_pj = $user['divisi'];
$jabatan = "Penanggung Jawab Divisi " . $divisi_pj;

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$prev_month = $month - 1;
$prev_year = $year;
$next_month = $month + 1;
$next_year = $year;

if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$first_day_of_week = date('w', $first_day);

$first_day_of_week = $first_day_of_week == 0 ? 6 : $first_day_of_week - 1;

$cuti_by_date = [];
$sql = "SELECT dpc.*, dk.nama_lengkap, dk.jabatan, dk.role 
        FROM data_pengajuan_cuti dpc 
        JOIN data_karyawan dk ON dpc.kode_karyawan = dk.kode_karyawan 
        WHERE dpc.divisi = ? 
        AND (
            (MONTH(dpc.tanggal_mulai) = ? AND YEAR(dpc.tanggal_mulai) = ?) OR
            (MONTH(dpc.tanggal_akhir) = ? AND YEAR(dpc.tanggal_akhir) = ?) OR
            (dpc.tanggal_mulai <= ? AND dpc.tanggal_akhir >= ?)
        )
        AND dpc.status = 'Diterima'
        ORDER BY dpc.tanggal_mulai, dpc.tanggal_akhir";
$stmt = $conn->prepare($sql);
$start_date = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$end_date = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
$stmt->bind_param("siiisss", $divisi_pj, $month, $year, $month, $year, $end_date, $start_date);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['tanggal_mulai']);
    $end = new DateTime($row['tanggal_akhir']);
    $end->modify('+1 day'); 
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach ($period as $date) {
        $current_month = (int)$date->format('n');
        $current_year = (int)$date->format('Y');
        
        if ($current_month == $month && $current_year == $year) {
            $date_str = $date->format('Y-m-d');
            if (!isset($cuti_by_date[$date_str])) {
                $cuti_by_date[$date_str] = [];
            }
            $cuti_by_date[$date_str][] = [
                'id' => $row['id'],
                'kode_karyawan' => $row['kode_karyawan'],
                'nama_karyawan' => $row['nama_karyawan'],
                'jabatan' => $row['jabatan'],
                'jenis_cuti' => $row['jenis_cuti'],
                'tanggal_mulai' => $row['tanggal_mulai'],
                'tanggal_akhir' => $row['tanggal_akhir'],
                'alasan' => $row['alasan'],
                'status' => $row['status'],
                'role' => $row['role']
            ];
        }
    }
}
$stmt->close();

$total_cuti_hari = array_sum(array_map('count', $cuti_by_date));
$hari_dengan_cuti = count($cuti_by_date);

$stats_jenis = [
    'Tahunan' => 0,
    'Lustrum' => 0,
    'Sakit' => 0,
    'Khusus - Menikah' => 0,
    'Lainnya' => 0
];

$stats_role = [
    'penanggung jawab' => 0,
    'karyawan' => 0
];

foreach ($cuti_by_date as $cuti_list) {
    foreach ($cuti_list as $cuti) {
        $jenis = $cuti['jenis_cuti'];
        if (isset($stats_jenis[$jenis])) {
            $stats_jenis[$jenis]++;
        } else {
            $stats_jenis['Lainnya']++;
        }
        
        if ($cuti['role'] === 'penanggung jawab') {
            $stats_role['penanggung jawab']++;
        } else {
            $stats_role['karyawan']++;
        }
    }
}

$conn->close();

$month_names = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$cuti_colors = [
    'Tahunan' => '#4ecdc4',
    'Lustrum' => '#45b7af',
    'Sakit' => '#ff6b6b',
    'Khusus - Menikah' => '#ffd93d',
    'Lainnya' => '#6c757d'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Cuti Divisi - Penanggung Jawab</title>
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
            max-width: 1400px; 
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
        
        /* Calendar Styles */
        .card { 
            background: var(--card-bg); 
            color: var(--text-color-dark); 
            border-radius: 20px; 
            padding: 30px 40px; 
            box-shadow: 0 5px 20px var(--shadow-light); 
            margin-bottom: 30px; 
        }
        
        .calendar-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            padding: 15px; 
            background: #f8f9fa; 
            border-radius: 10px; 
        }
        
        .calendar-nav { 
            display: flex; 
            gap: 10px; 
            align-items: center; 
        }
        
        .calendar-title { 
            font-size: 24px; 
            font-weight: 600; 
            color: var(--primary-color); 
        }
        
        .nav-btn { 
            padding: 8px 16px; 
            background: var(--accent-color); 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-block; 
        }
        
        .nav-btn:hover { 
            background: #3a3162; 
        }
        
        .calendar { 
            display: grid; 
            grid-template-columns: repeat(7, 1fr); 
            gap: 1px; 
            background: #ddd; 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            overflow: hidden; 
        }
        
        .calendar-day-header { 
            background: #4a3f81; 
            color: white; 
            padding: 15px; 
            text-align: center; 
            font-weight: 600; 
        }
        
        .calendar-day { 
            background: white; 
            min-height: 120px; 
            padding: 8px; 
            border: 1px solid #eee; 
            position: relative; 
            cursor: pointer; 
        }
        
        .calendar-day.other-month { 
            background: #f8f9fa; 
            color: #999; 
        }
        
        .day-number { 
            font-weight: 600; 
            margin-bottom: 5px; 
        }
        
        .cuti-indicator { 
            background: #4ecdc4; 
            color: white; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 11px; 
            margin-bottom: 2px;
            display: block;
        }
        
        .cuti-list { 
            max-height: 80px; 
            overflow-y: auto; 
            font-size: 11px; 
        }
        
        .cuti-item { 
            background: #e0f7fa; 
            padding: 2px 4px; 
            margin-bottom: 2px; 
            border-radius: 3px; 
            border-left: 3px solid #4ecdc4;
        }
        
        .cuti-item.pj { 
            background: #fff3cd; 
            border-left: 3px solid #ffc107;
        }
        
        .cuti-item.sakit { 
            background: #ffeaea; 
            border-left: 3px solid #ff6b6b;
        }
        
        .cuti-item.lustrum { 
            background: #e0f7fa; 
            border-left: 3px solid #45b7af;
        }
        
        .cuti-item.khusus { 
            background: #fff9e6; 
            border-left: 3px solid #ffd93d;
        }
        
        .today { 
            background: #e7f3ff !important; 
            border: 2px solid #4a3f81; 
        }
        
        .legend { 
            display: flex; 
            gap: 20px; 
            margin-top: 20px; 
            padding: 15px; 
            background: #f8f9fa; 
            border-radius: 10px; 
            flex-wrap: wrap;
        }
        
        .legend-item { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 14px; 
        }
        
        .legend-color { 
            width: 20px; 
            height: 20px; 
            border-radius: 4px; 
        }
        
        .legend-tahunan { background: #4ecdc4; }
        .legend-lustrum { background: #45b7af; }
        .legend-sakit { background: #ff6b6b; }
        .legend-khusus { background: #ffd93d; }
        .legend-pj { background: #ffc107; }
        
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.5); 
        }
        
        .modal-content { 
            background-color: white; 
            margin: 5% auto; 
            padding: 20px; 
            border-radius: 10px; 
            width: 80%; 
            max-width: 600px; 
            color: var(--text-color-dark); 
        }
        
        .close { 
            color: #aaa; 
            float: right; 
            font-size: 28px; 
            font-weight: bold; 
            cursor: pointer; 
        }
        
        .close:hover { 
            color: #000; 
        }
        
        .modal-title { 
            font-size: 20px; 
            font-weight: 600; 
            margin-bottom: 15px; 
            color: #1E105E; 
        }
        
        .karyawan-list { 
            max-height: 400px; 
            overflow-y: auto; 
        }
        
        .karyawan-item { 
            padding: 10px; 
            border-bottom: 1px solid #eee; 
        }
        
        .karyawan-item:last-child { 
            border-bottom: none; 
        }
        
        .karyawan-item.pj { 
            background: #fffdf6; 
            border-left: 4px solid #ffc107; 
        }
        
        .karyawan-item.sakit { 
            background: #fff5f5; 
            border-left: 4px solid #ff6b6b; 
        }
        
        .karyawan-item.lustrum { 
            background: #f0f9ff; 
            border-left: 4px solid #45b7af; 
        }
        
        .karyawan-item.khusus { 
            background: #fffdf0; 
            border-left: 4px solid #ffd93d; 
        }
        
        .divisi-info { 
            background: #e7f3ff; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            border-left: 4px solid #4a3f81;
        }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 10px; 
            margin-top: 10px;
        }
        
        .stat-box { 
            background: white; 
            padding: 10px; 
            border-radius: 6px; 
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .stat-number { 
            font-size: 1.5rem; 
            font-weight: bold; 
            color: #1E105E; 
            display: block;
        }
        
        .stat-label { 
            font-size: 0.8rem; 
            color: #666;
        }
        
        .role-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .badge-pj {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }
        
        .badge-karyawan {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #17a2b8;
        }
        
        .cuti-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .badge-tahunan {
            background: #d1f2eb;
            color: #0c5460;
            border: 1px solid #4ecdc4;
        }
        
        .badge-lustrum {
            background: #c1ece9;
            color: #0c5460;
            border: 1px solid #45b7af;
        }
        
        .badge-sakit {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #ff6b6b;
        }
        
        .badge-khusus {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffd93d;
        }
        
        @media (max-width: 768px) {
            .calendar-day { min-height: 80px; padding: 4px; font-size: 12px; }
            .cuti-list { max-height: 60px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .legend { flex-direction: column; gap: 10px; }
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
        <h1>Kalender Cuti Divisi</h1>
        <p>Lihat jadwal cuti yang telah disetujui untuk karyawan divisi <?= htmlspecialchars($divisi_pj) ?></p>
    </div>

    <div class="card">
        <h2 style="text-align: center; color: #1E105E; margin-bottom: 20px;">Kalender Cuti Divisi <?= htmlspecialchars($divisi_pj) ?></h2>
        
        <div class="divisi-info">
            <strong>Divisi:</strong> <?= htmlspecialchars($divisi_pj) ?>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-number"><?= $total_cuti_hari ?></span>
                    <span class="stat-label">Total Hari Cuti</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?= $hari_dengan_cuti ?></span>
                    <span class="stat-label">Hari dengan Cuti</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?= $stats_role['penanggung jawab'] ?></span>
                    <span class="stat-label">Cuti Penanggung Jawab</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?= $stats_role['karyawan'] ?></span>
                    <span class="stat-label">Cuti Karyawan</span>
                </div>
            </div>
        </div>
        
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">&larr; Prev</a>
                <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">Next &rarr;</a>
            </div>
            <a href="kalender_cuti_penanggungjawab.php" class="nav-btn">Bulan Ini</a>
        </div>

        <div class="calendar">
            <div class="calendar-day-header">Senin</div>
            <div class="calendar-day-header">Selasa</div>
            <div class="calendar-day-header">Rabu</div>
            <div class="calendar-day-header">Kamis</div>
            <div class="calendar-day-header">Jumat</div>
            <div class="calendar-day-header">Sabtu</div>
            <div class="calendar-day-header">Minggu</div>
            
            <?php for ($i = 0; $i < $first_day_of_week; $i++): ?>
                <div class="calendar-day other-month"></div>
            <?php endfor; ?>
            
            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                <?php
                $current_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                $is_today = $current_date == date('Y-m-d');
                $has_cuti = isset($cuti_by_date[$current_date]);
                $cuti_count = $has_cuti ? count($cuti_by_date[$current_date]) : 0;
                ?>
                <div class="calendar-day <?= $is_today ? 'today' : '' ?>" onclick="showCutiDetails('<?= $current_date ?>')">
                    <div class="day-number"><?= $day ?></div>
                    <?php if ($has_cuti): ?>
                        <span class="cuti-indicator"><?= $cuti_count ?> Cuti</span>
                        <div class="cuti-list">
                            <?php foreach (array_slice($cuti_by_date[$current_date], 0, 3) as $cuti): ?>
                                <?php
                                $cuti_class = '';
                                if ($cuti['role'] === 'penanggung jawab') {
                                    $cuti_class = 'pj';
                                } elseif ($cuti['jenis_cuti'] === 'Sakit') {
                                    $cuti_class = 'sakit';
                                } elseif ($cuti['jenis_cuti'] === 'Lustrum') {
                                    $cuti_class = 'lustrum';
                                } elseif (strpos($cuti['jenis_cuti'], 'Khusus') !== false) {
                                    $cuti_class = 'khusus';
                                }
                                ?>
                                <div class="cuti-item <?= $cuti_class ?>" 
                                     title="<?= htmlspecialchars($cuti['nama_karyawan']) ?> - <?= htmlspecialchars($cuti['jenis_cuti']) ?>">
                                    <?= htmlspecialchars($cuti['nama_karyawan']) ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($cuti_count > 3): ?>
                                <div class="cuti-item">+<?= $cuti_count - 3 ?> lebih</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color legend-tahunan"></div>
                <span>Cuti Tahunan</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-lustrum"></div>
                <span>Cuti Lustrum</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-sakit"></div>
                <span>Cuti Sakit</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-khusus"></div>
                <span>Cuti Khusus</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-pj"></div>
                <span>Penanggung Jawab</span>
            </div>
            <div class="legend-item">
                <div style="background: #e7f3ff; border: 2px solid #4a3f81; width: 20px; height: 20px;"></div>
                <span>Hari Ini</span>
            </div>
        </div>
    </div>
</main>

<div id="cutiModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-title" id="modalTitle">Detail Cuti Divisi <?= htmlspecialchars($divisi_pj) ?></div>
        <div class="karyawan-list" id="karyawanList">
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('cutiModal');
    const closeBtn = document.querySelector('.close');
    const modalTitle = document.getElementById('modalTitle');
    const karyawanList = document.getElementById('karyawanList');
    
    const cutiData = <?= json_encode($cuti_by_date) ?>;
    const monthNames = <?= json_encode($month_names) ?>;
    const divisiPj = "<?= $divisi_pj ?>";
    
    function showCutiDetails(date) {
        const cutiOnDate = cutiData[date] || [];
        
        if (cutiOnDate.length === 0) {
            return;
        }
        
        const dateObj = new Date(date);
        const day = dateObj.getDate();
        const month = monthNames[dateObj.getMonth() + 1];
        const year = dateObj.getFullYear();
        
        modalTitle.textContent = `Cuti Divisi ${divisiPj} - ${day} ${month} ${year}`;
        
        let html = '';
        if (cutiOnDate.length === 0) {
            html = '<p>Tidak ada cuti pada tanggal ini.</p>';
        } else {
            cutiOnDate.forEach(cuti => {
                const isPj = cuti.role === 'penanggung jawab';
                const roleBadge = isPj ? '<span class="role-badge badge-pj">PJ</span>' : '<span class="role-badge badge-karyawan">Karyawan</span>';
                
                let cutiBadgeClass = 'badge-tahunan';
                let cutiItemClass = '';
                
                if (cuti.jenis_cuti === 'Sakit') {
                    cutiBadgeClass = 'badge-sakit';
                    cutiItemClass = 'sakit';
                } else if (cuti.jenis_cuti === 'Lustrum') {
                    cutiBadgeClass = 'badge-lustrum';
                    cutiItemClass = 'lustrum';
                } else if (cuti.jenis_cuti.includes('Khusus')) {
                    cutiBadgeClass = 'badge-khusus';
                    cutiItemClass = 'khusus';
                }
                
                const cutiBadge = `<span class="cuti-badge ${cutiBadgeClass}">${cuti.jenis_cuti}</span>`;
                
                html += `
                    <div class="karyawan-item ${isPj ? 'pj' : ''} ${cutiItemClass}">
                        <strong>${cuti.nama_karyawan}</strong> ${roleBadge} ${cutiBadge}<br>
                        <small>Kode: ${cuti.kode_karyawan} | Jabatan: ${cuti.jabatan}</small><br>
                        <small>Periode: ${cuti.tanggal_mulai} s/d ${cuti.tanggal_akhir}</small><br>
                        <small>Alasan: ${cuti.alasan || '-'}</small><br>
                        <small>Status: <span style="color: #28a745;">Diterima</span></small>
                    </div>
                `;
            });
        }
        
        karyawanList.innerHTML = html;
        modal.style.display = 'block';
    }
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>