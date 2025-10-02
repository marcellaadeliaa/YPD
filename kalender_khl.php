<?php
session_start();

// --- DUMMY DATA KHL ---
$data_khl = [
    [
        'id' => 1,
        'kode_khl' => '11221384',
        'nama_karyawan' => 'Adrianna',
        'jenis_khl' => 'Training',
        'projek' => 'Pelatihan Karyawan',
        'tanggal_kerja' => '2025-09-10',
        'jam_mulai_kerja' => '07:00:00',
        'jam_selesai_kerja' => '16:00:00',
        'status' => 'Diterima'
    ],
    [
        'id' => 2,
        'kode_khl' => '11221385',
        'nama_karyawan' => 'Budi Santoso',
        'jenis_khl' => 'Proyek Khusus',
        'projek' => 'Implementasi Sistem Baru',
        'tanggal_kerja' => '2025-09-12',
        'jam_mulai_kerja' => '08:00:00',
        'jam_selesai_kerja' => '17:00:00',
        'status' => 'Diterima'
    ],
    [
        'id' => 3,
        'kode_khl' => '11221386',
        'nama_karyawan' => 'Siti Rahayu',
        'jenis_khl' => 'Lembur',
        'projek' => 'Laporan Bulanan',
        'tanggal_kerja' => '2025-09-15',
        'jam_mulai_kerja' => '17:00:00',
        'jam_selesai_kerja' => '21:00:00',
        'status' => 'Diterima'
    ],
    [
        'id' => 4,
        'kode_khl' => '11221387',
        'nama_karyawan' => 'Ahmad Fauzi',
        'jenis_khl' => 'Training',
        'projek' => 'Workshop Leadership',
        'tanggal_kerja' => '2025-09-18',
        'jam_mulai_kerja' => '08:30:00',
        'jam_selesai_kerja' => '16:30:00',
        'status' => 'Diterima'
    ],
    [
        'id' => 5,
        'kode_khl' => '11221388',
        'nama_karyawan' => 'Rina Melati',
        'jenis_khl' => 'Proyek Khusus',
        'projek' => 'Event Company Gathering',
        'tanggal_kerja' => '2025-09-20',
        'jam_mulai_kerja' => '06:00:00',
        'jam_selesai_kerja' => '18:00:00',
        'status' => 'Diterima'
    ],
    [
        'id' => 6,
        'kode_khl' => '11221389',
        'nama_karyawan' => 'Xue',
        'jenis_khl' => 'Lembur',
        'projek' => 'Laporan Kuartalan',
        'tanggal_kerja' => '2025-09-25',
        'jam_mulai_kerja' => '19:00:00',
        'jam_selesai_kerja' => '22:00:00',
        'status' => 'Diterima'
    ],
];

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

// Group KHL by date for easy lookup
$khl_by_date = [];
foreach ($data_khl as $khl) {
    $date = $khl['tanggal_kerja'];
    if (!isset($khl_by_date[$date])) {
        $khl_by_date[$date] = [];
    }
    $khl_by_date[$date][] = $khl;
}

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
<title>Kalender KHL - Admin SDM</title>
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
    .nav-btn { padding: 8px 16px; background: #4a3f81; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
    .nav-btn:hover { background: #3a3162; }
    
    .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #ddd; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
    .calendar-day-header { background: #4a3f81; color: white; padding: 15px; text-align: center; font-weight: 600; }
    .calendar-day { background: white; min-height: 120px; padding: 8px; border: 1px solid #eee; position: relative; }
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
        <h2 class="page-title">Kalender KHL Pegawai</h2>
        
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">&larr; Prev</a>
                <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">Next &rarr;</a>
            </div>
            <a href="kalender_khl.php" class="nav-btn">Bulan Ini</a>
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
    </div>
</main>

<!-- Modal for KHL details -->
<div id="khlModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-title" id="modalTitle">Detail KHL</div>
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