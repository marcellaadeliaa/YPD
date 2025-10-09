<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
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

$query_khl = "SELECT 
                dk.nama_lengkap,
                dpk.* 
              FROM data_pengajuan_khl dpk 
              JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan 
              WHERE dpk.status_khl = 'disetujui'
              ORDER BY dpk.tanggal_khl ASC";

$result_khl = $conn->query($query_khl);
$data_khl = [];

if ($result_khl && $result_khl->num_rows > 0) {
    while($row = $result_khl->fetch_assoc()) {
        $data_khl[] = [
            'id' => $row['id_khl'],
            'kode_khl' => $row['id_khl'],
            'nama_karyawan' => $row['nama_lengkap'],
            'jenis_khl' => $row['divisi'],
            'projek' => $row['proyek'],
            'tanggal_kerja' => $row['tanggal_khl'],
            'jam_mulai_kerja' => $row['jam_mulai_kerja'],
            'jam_selesai_kerja' => $row['jam_akhir_kerja'],
            'status' => 'Diterima'
        ];
    }
}

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$selected_year = isset($_GET['filter_year']) ? (int)$_GET['filter_year'] : $year;

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

$khl_by_date = [];
foreach ($data_khl as $khl) {
    $khl_year = date('Y', strtotime($khl['tanggal_kerja']));
    if ($khl_year == $selected_year) {
        $date = $khl['tanggal_kerja'];
        if (!isset($khl_by_date[$date])) {
            $khl_by_date[$date] = [];
        }
        $khl_by_date[$date][] = $khl;
    }
}

$month_names = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$available_years = [];
foreach ($data_khl as $khl) {
    $khl_year = date('Y', strtotime($khl['tanggal_kerja']));
    if (!in_array($khl_year, $available_years)) {
        $available_years[] = $khl_year;
    }
}
rsort($available_years);
if (empty($available_years)) {
    $available_years[] = date('Y');
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalender KHL</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 140px; height: 60px; object-fit: contain; }
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
    .page-title { font-size: 30px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    
    /* Filter Section */
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #e0e0e0; }
    .filter-row { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group select, .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; background: white; }
    .filter-group select { min-width: 120px; }
    
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: background-color 0.3s; text-decoration: none; text-align: center; display: inline-block; }
    .btn-cari { background-color: #4a3f81; }
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    
    /* Calendar Styles */
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
    .calendar-nav { display: flex; gap: 10px; align-items: center; }
    .calendar-title { font-size: 24px; font-weight: 600; color: #1E105E; }
    .nav-btn { padding: 8px 16px; background: #4a3f81; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
    .nav-btn:hover { background: #3a3162; }
    
    .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #ddd; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
    .calendar-day-header { background: #4a3f81; color: white; padding: 15px; text-align: center; font-weight: 600; }
    .calendar-day { background: white; min-height: 120px; padding: 8px; border: 1px solid #eee; position: relative; cursor: pointer; }
    .calendar-day.other-month { background: #f8f9fa; color: #999; }
    .calendar-day.other-month { cursor: default; }
    .day-number { font-weight: 600; margin-bottom: 5px; }
    
    .khl-indicator { 
        background: #4ecdc4; 
        color: white; 
        padding: 2px 6px; 
        border-radius: 3px; 
        font-size: 11px; 
        margin-bottom: 2px;
        display: block;
    }
    
    .khl-list { 
        max-height: 80px; 
        overflow-y: auto; 
        font-size: 11px; 
    }
    .khl-item { 
        background: #e0f7fa; 
        padding: 2px 4px; 
        margin-bottom: 2px; 
        border-radius: 3px; 
        border-left: 3px solid #4ecdc4;
    }
    
    .today { background: #e7f3ff !important; border: 2px solid #4a3f81; }
    
    .legend { display: flex; gap: 20px; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 14px; }
    .legend-color { width: 20px; height: 20px; border-radius: 4px; }
    .legend-khl { background: #4ecdc4; }
    
    /* Modal Styles */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 5% auto; padding: 20px; border-radius: 10px; width: 80%; max-width: 600px; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover { color: #000; }
    
    .modal-title { font-size: 20px; font-weight: 600; margin-bottom: 15px; color: #1E105E; }
    .karyawan-list { max-height: 400px; overflow-y: auto; }
    .karyawan-item { padding: 10px; border-bottom: 1px solid #eee; }
    .karyawan-item:last-child { border-bottom: none; }
    
    .filter-info { background: #e7f3ff; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #4a3f81; }
    
    @media (max-width: 768px) {
        .calendar-day { min-height: 80px; padding: 4px; font-size: 12px; }
        .khl-list { max-height: 60px; }
        .filter-row { flex-direction: column; }
        .filter-group { width: 100%; }
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
        <h2 class="page-title">Kalender KHL Pegawai</h2>

        <div class="filter-section">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter_year">Tahun</label>
                        <select id="filter_year" name="filter_year">
                            <?php foreach ($available_years as $avail_year): ?>
                                <option value="<?= $avail_year ?>" <?= $selected_year == $avail_year ? 'selected' : '' ?>>
                                    <?= $avail_year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="month">Bulan</label>
                        <select id="month" name="month">
                            <?php foreach ($month_names as $key => $name): ?>
                                <option value="<?= $key ?>" <?= $month == $key ? 'selected' : '' ?>>
                                    <?= $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Terapkan Filter</button>
                    <a href="kalender_khl.php" class="btn btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <?php if ($selected_year != date('Y') || $month != date('n')): ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong> 
                Menampilkan data KHL untuk <?= $month_names[$month] ?> <?= $selected_year ?>
                | <a href="kalender_khl.php" style="color: #4a3f81; text-decoration: none;">Tampilkan Bulan Ini</a>
            </div>
        <?php endif; ?>

        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>&filter_year=<?= $selected_year ?>" class="nav-btn">&larr; Prev</a>
                <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>&filter_year=<?= $selected_year ?>" class="nav-btn">Next &rarr;</a>
            </div>
            <a href="kalender_khl.php" class="nav-btn">Bulan Ini</a>
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
                $has_khl = isset($khl_by_date[$current_date]);
                $khl_count = $has_khl ? count($khl_by_date[$current_date]) : 0;
                ?>
                <div class="calendar-day <?= $is_today ? 'today' : '' ?> <?= !$has_khl ? 'no-khl' : '' ?>" 
                     <?= $has_khl ? 'onclick="showKhlDetails(\'' . $current_date . '\')"' : '' ?>>
                    <div class="day-number"><?= $day ?></div>
                    <?php if ($has_khl): ?>
                        <span class="khl-indicator"><?= $khl_count ?> KHL</span>
                        <div class="khl-list">
                            <?php foreach (array_slice($khl_by_date[$current_date], 0, 3) as $khl): ?>
                                <div class="khl-item" title="<?= htmlspecialchars($khl['nama_karyawan']) ?> - <?= htmlspecialchars($khl['jenis_khl']) ?>">
                                    <?= htmlspecialchars($khl['nama_karyawan']) ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($khl_count > 3): ?>
                                <div class="khl-item">+<?= $khl_count - 3 ?> lebih</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color legend-khl"></div>
                <span>Karyawan KHL</span>
            </div>
            <div class="legend-item">
                <div style="background: #e7f3ff; border: 2px solid #4a3f81; width: 20px; height: 20px;"></div>
                <span>Hari Ini</span>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; text-align: center;">
            <strong>Summary <?= $month_names[$month] ?> <?= $selected_year ?>:</strong>
            <?php
            $total_khl_month = 0;
            $unique_days = array_keys($khl_by_date);
            foreach ($khl_by_date as $date_khl) {
                $total_khl_month += count($date_khl);
            }
            ?>
            <span style="margin-left: 15px;">Total KHL: <?= $total_khl_month ?> pengajuan</span>
            <span style="margin-left: 15px;">Hari dengan KHL: <?= count($unique_days) ?> hari</span>
        </div>
    </div>
</main>

<div id="khlModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-title" id="modalTitle">Detail KHL</div>
        <div class="karyawan-list" id="karyawanList">
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('khlModal');
    const closeBtn = document.querySelector('.close');
    const modalTitle = document.getElementById('modalTitle');
    const karyawanList = document.getElementById('karyawanList');

    const khlData = <?= json_encode($khl_by_date) ?>;
    const monthNames = <?= json_encode($month_names) ?>;
    
    function showKhlDetails(date) {
        const khlOnDate = khlData[date] || [];
        
        if (khlOnDate.length === 0) {
            return;
        }
        
        const dateObj = new Date(date);
        const day = dateObj.getDate();
        const month = monthNames[dateObj.getMonth() + 1];
        const year = dateObj.getFullYear();
        
        modalTitle.textContent = `Karyawan KHL - ${day} ${month} ${year}`;
        
        let html = '';
        if (khlOnDate.length === 0) {
            html = '<p>Tidak ada karyawan yang KHL pada tanggal ini.</p>';
        } else {
            khlOnDate.forEach(khl => {
                const jamMulai = khl.jam_mulai_kerja ? new Date('1970-01-01T' + khl.jam_mulai_kerja).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '-';
                const jamSelesai = khl.jam_selesai_kerja ? new Date('1970-01-01T' + khl.jam_selesai_kerja).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '-';
                
                html += `
                    <div class="karyawan-item">
                        <strong>${khl.nama_karyawan}</strong><br>
                        <small>Jenis: ${khl.jenis_khl} | Projek: ${khl.projek}</small><br>
                        <small>Jam: ${jamMulai} - ${jamSelesai} | Status: <span style="color: #28a745;">${khl.status}</span></small><br>
                        <small>Kode: ${khl.kode_khl}</small>
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