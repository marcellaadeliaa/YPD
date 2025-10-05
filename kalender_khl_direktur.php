<?php
session_start();
require 'config.php';

// Simulasi user direksi
$_SESSION['user'] = [
    'role' => 'direksi',
    'nama_lengkap' => 'Pico',
    'kode_karyawan' => 'DIR001'
];

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];

// Ambil data KHL dari database
$query = "SELECT dpk.*, dk.nama_lengkap, dk.divisi 
          FROM data_pengajuan_khl dpk 
          JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan 
          WHERE dpk.status_khl = 'disetujui'
          ORDER BY dpk.tanggal_khl ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$data_khl = [];
while ($row = $result->fetch_assoc()) {
    $data_khl[] = [
        'id' => $row['id_khl'],
        'kode_khl' => 'KHL-' . $row['id_khl'],
        'nama_karyawan' => $row['nama_lengkap'],
        'jenis_khl' => $row['proyek'],
        'projek' => $row['proyek'],
        'tanggal_kerja' => $row['tanggal_khl'],
        'jam_mulai_kerja' => $row['jam_mulai_kerja'],
        'jam_selesai_kerja' => $row['jam_akhir_kerja'],
        'status' => ucfirst($row['status_khl']),
        'divisi' => $row['divisi']
    ];
}

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

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalender KHL - Direktur</title>
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
        margin:0; 
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%); 
        min-height:100vh; 
        color:#333; 
    }
    
    header { 
        background: var(--card-bg); 
        padding:20px 40px; 
        display:flex; 
        justify-content:space-between; 
        align-items:center; 
        box-shadow: 0 4px 15px var(--shadow-light); 
    }
    
    .logo { 
        display:flex; 
        align-items:center; 
        gap:16px; 
        font-weight:500; 
        font-size:20px; 
        color: var(--text-color-dark); 
    }
    
    .logo img { 
        width: 50px; 
        height: 50px; 
        object-fit: contain; 
        border-radius: 50%; 
    }
    
    nav ul { 
        list-style:none; 
        margin:0; 
        padding:0; 
        display:flex; 
        gap:30px; 
    }
    
    nav li { 
        position:relative; 
    }
    
    nav a { 
        text-decoration:none; 
        color: var(--text-color-dark); 
        font-weight:600; 
        padding:8px 4px; 
        display:block; 
    }
    
    nav li ul { 
        display:none; 
        position:absolute; 
        top:100%; 
        left:0; 
        background: var(--card-bg); 
        padding:10px 0; 
        border-radius:8px; 
        box-shadow:0 2px 10px var(--shadow-light); 
        min-width:200px; 
        z-index:999; 
    }
    
    nav li:hover > ul { 
        display:block; 
    }
    
    nav li ul li { 
        padding:5px 20px; 
    }
    
    nav li ul li a { 
        color: var(--text-color-dark); 
        font-weight:400; 
        white-space:nowrap; 
    }
    
    main { 
        max-width:1400px; 
        margin:40px auto; 
        padding:0 20px; 
    }
    
    h1, p.direktur-title { 
        color: var(--text-color-light); 
    }
    
    h1 { 
        text-align:left; 
        font-size:2.5rem; 
        margin-bottom:10px; 
    }
    
    p.direktur-title { 
        font-size: 1.1rem; 
        margin-top: 0; 
        margin-bottom: 30px; 
        font-weight: 400; 
        opacity: 0.9; 
    }
    
    .card { 
        background: var(--card-bg); 
        border-radius:20px; 
        padding:30px 40px; 
        box-shadow:0 5px 20px var(--shadow-light); 
    }
    
    .page-title { 
        font-size: 2rem; 
        font-weight: 600; 
        text-align: center; 
        margin-bottom: 30px; 
        color: var(--primary-color); 
    }
    
    /* Calendar Styles */
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
        font-size: 1.5rem; 
        font-weight: 600; 
        color: var(--primary-color); 
    }
    
    .nav-btn { 
        padding: 10px 20px; 
        background: var(--accent-color); 
        color: white; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 600; 
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    
    .nav-btn:hover { 
        background: #352d5c; 
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
        background: var(--accent-color); 
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
        transition: background-color 0.2s ease;
    }
    
    .calendar-day:hover {
        background: #f8f9fa;
    }
    
    .calendar-day.other-month { 
        background: #f8f9fa; 
        color: #999; 
    }
    
    .day-number { 
        font-weight: 600; 
        margin-bottom: 5px; 
        font-size: 14px;
    }
    
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
    
    .today { 
        background: #e7f3ff !important; 
        border: 2px solid var(--primary-color); 
    }
    
    .legend { 
        display: flex; 
        gap: 20px; 
        margin-top: 20px; 
        padding: 15px; 
        background: #f8f9fa; 
        border-radius: 10px; 
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
    
    .legend-khl { 
        background: #4ecdc4; 
    }
    
    /* Modal Styles */
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
        padding: 30px; 
        border-radius: 15px; 
        width: 80%; 
        max-width: 700px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
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
        font-size: 1.5rem; 
        font-weight: 600; 
        margin-bottom: 20px; 
        color: var(--primary-color); 
    }
    
    .karyawan-list { 
        max-height: 400px; 
        overflow-y: auto; 
    }
    
    .karyawan-item { 
        padding: 15px; 
        border-bottom: 1px solid #eee; 
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    .karyawan-item:last-child { 
        border-bottom: none; 
        margin-bottom: 0;
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
    
    @media (max-width: 768px) {
        .calendar-day { min-height: 80px; padding: 4px; font-size: 12px; }
        .khl-list { max-height: 60px; }
        .calendar-header { flex-direction: column; gap: 15px; }
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
            <li><a href="dashboard_direktur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li>
                    <li><a href="kalender_cuti_direktur.php">Kalender Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li>
                    <li><a href="kalender_khl_direktur.php">Kalender KHL</a></li>
                </ul>
            </li>
            <li><a href="#">Karyawan ▾</a>
                <ul>
                    <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
                </ul>
            </li>
            <li><a href="#">Pelamar ▾</a>
                <ul>
                    <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
                </ul>
            </li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_direktur.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, <?php echo htmlspecialchars($nama_direktur); ?>!</h1>
    <p class="direktur-title">Direktur Perusahaan</p>

    <div class="card">
        <h2 class="page-title">Kalender KHL Perusahaan</h2>
        
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">&larr; Bulan Sebelumnya</a>
                <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">Bulan Selanjutnya &rarr;</a>
            </div>
            <a href="kalender_khl_direktur.php" class="nav-btn">Bulan Ini</a>
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
                                    <?= htmlspecialchars($khl['nama_karyawan']) ?> (<?= htmlspecialchars($khl['divisi']) ?>)
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
                <div style="background: #e7f3ff; border: 2px solid var(--primary-color); width: 20px; height: 20px;"></div>
                <span>Hari Ini</span>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard_direktur.php" class="back-link">← Kembali ke Dashboard</a>
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
                        <small>Divisi: ${khl.divisi} | Projek: ${khl.projek}</small><br>
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