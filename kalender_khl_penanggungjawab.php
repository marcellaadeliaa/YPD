<?php
session_start();
require 'config.php';

// Cek apakah user sudah login sebagai penanggung jawab
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_pj = $user['nama_lengkap'];
$divisi_pj = $user['divisi'];
$jabatan = "Penanggung Jawab Divisi " . $divisi_pj;

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Navigation
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

// Get first day of the month
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$first_day_of_week = date('w', $first_day);

// Adjust Sunday to be 0 instead of 7
$first_day_of_week = $first_day_of_week == 0 ? 6 : $first_day_of_week - 1;

// Query untuk mengambil data KHL divisi penanggung jawab dari database
$khl_by_date = [];
$sql = "SELECT dpk.*, dk.nama_lengkap 
        FROM data_pengajuan_khl dpk 
        JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan 
        WHERE dpk.divisi = ? AND MONTH(dpk.tanggal_khl) = ? AND YEAR(dpk.tanggal_khl) = ? 
        AND dpk.status_khl = 'disetujui' 
        ORDER BY dpk.tanggal_khl, dpk.jam_mulai_kerja";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $divisi_pj, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $date = $row['tanggal_khl'];
    if (!isset($khl_by_date[$date])) {
        $khl_by_date[$date] = [];
    }
    $khl_by_date[$date][] = [
        'id_khl' => $row['id_khl'],
        'kode_karyawan' => $row['kode_karyawan'],
        'nama_karyawan' => $row['nama_lengkap'],
        'proyek' => $row['proyek'],
        'tanggal_khl' => $row['tanggal_khl'],
        'jam_mulai_kerja' => $row['jam_mulai_kerja'],
        'jam_akhir_kerja' => $row['jam_akhir_kerja'],
        'status_khl' => $row['status_khl']
    ];
}
$stmt->close();
$conn->close();

$month_names = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalender KHL Divisi - Penanggung Jawab</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 120px; height: 60px; object-fit: contain; }
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
    
    .divisi-info { 
        background: #e7f3ff; 
        padding: 10px 15px; 
        border-radius: 8px; 
        margin-bottom: 15px; 
        border-left: 4px solid #4a3f81;
    }
    
    @media (max-width: 768px) {
        .calendar-day { min-height: 80px; padding: 4px; font-size: 12px; }
        .khl-list { max-height: 60px; }
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
    <h1>Welcome, <?= htmlspecialchars($nama_pj) ?>!</h1>
    <p class="admin-title"><?= htmlspecialchars($jabatan) ?></p>

    <div class="card">
        <h2 class="page-title">Kalender KHL Divisi <?= htmlspecialchars($divisi_pj) ?></h2>
        
        <div class="divisi-info">
            <strong>Divisi:</strong> <?= htmlspecialchars($divisi_pj) ?> | 
            <strong>Total KHL Disetujui:</strong> <?= array_sum(array_map('count', $khl_by_date)) ?> |
            <strong>Hari dengan KHL:</strong> <?= count($khl_by_date) ?>
        </div>
        
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">&larr; Prev</a>
                <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">Next &rarr;</a>
            </div>
            <a href="kalender_khl_penanggungjawab.php" class="nav-btn">Bulan Ini</a>
        </div>

        <div class="calendar">
            <!-- Day Headers -->
            <div class="calendar-day-header">Senin</div>
            <div class="calendar-day-header">Selasa</div>
            <div class="calendar-day-header">Rabu</div>
            <div class="calendar-day-header">Kamis</div>
            <div class="calendar-day-header">Jumat</div>
            <div class="calendar-day-header">Sabtu</div>
            <div class="calendar-day-header">Minggu</div>
            
            <!-- Empty days for first week -->
            <?php for ($i = 0; $i < $first_day_of_week; $i++): ?>
                <div class="calendar-day other-month"></div>
            <?php endfor; ?>
            
            <!-- Days of the month -->
            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                <?php
                $current_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                $is_today = $current_date == date('Y-m-d');
                $has_khl = isset($khl_by_date[$current_date]);
                $khl_count = $has_khl ? count($khl_by_date[$current_date]) : 0;
                ?>
                <div class="calendar-day <?= $is_today ? 'today' : '' ?>" onclick="showKhlDetails('<?= $current_date ?>')">
                    <div class="day-number"><?= $day ?></div>
                    <?php if ($has_khl): ?>
                        <span class="khl-indicator"><?= $khl_count ?> KHL</span>
                        <div class="khl-list">
                            <?php foreach (array_slice($khl_by_date[$current_date], 0, 3) as $khl): ?>
                                <div class="khl-item" title="<?= htmlspecialchars($khl['nama_karyawan']) ?> - <?= htmlspecialchars($khl['proyek']) ?>">
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
    </div>
</main>

<!-- Modal for KHL details -->
<div id="khlModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-title" id="modalTitle">Detail KHL Divisi <?= htmlspecialchars($divisi_pj) ?></div>
        <div class="karyawan-list" id="karyawanList">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
    // Modal functionality
    const modal = document.getElementById('khlModal');
    const closeBtn = document.querySelector('.close');
    const modalTitle = document.getElementById('modalTitle');
    const karyawanList = document.getElementById('karyawanList');
    
    // Data KHL from PHP (converted to JavaScript object)
    const khlData = <?= json_encode($khl_by_date) ?>;
    const monthNames = <?= json_encode($month_names) ?>;
    const divisiPj = "<?= $divisi_pj ?>";
    
    function showKhlDetails(date) {
        const khlOnDate = khlData[date] || [];
        
        if (khlOnDate.length === 0) {
            return;
        }
        
        const dateObj = new Date(date);
        const day = dateObj.getDate();
        const month = monthNames[dateObj.getMonth() + 1];
        const year = dateObj.getFullYear();
        
        modalTitle.textContent = `KHL Divisi ${divisiPj} - ${day} ${month} ${year}`;
        
        let html = '';
        if (khlOnDate.length === 0) {
            html = '<p>Tidak ada karyawan yang KHL pada tanggal ini.</p>';
        } else {
            khlOnDate.forEach(khl => {
                const jamMulai = khl.jam_mulai_kerja ? new Date('1970-01-01T' + khl.jam_mulai_kerja).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '-';
                const jamSelesai = khl.jam_akhir_kerja ? new Date('1970-01-01T' + khl.jam_akhir_kerja).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '-';
                
                html += `
                    <div class="karyawan-item">
                        <strong>${khl.nama_karyawan}</strong><br>
                        <small>Kode: ${khl.kode_karyawan} | Projek: ${khl.proyek}</small><br>
                        <small>Jam Kerja: ${jamMulai} - ${jamSelesai}</small><br>
                        <small>Status: <span style="color: #28a745;">Disetujui</span></small>
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