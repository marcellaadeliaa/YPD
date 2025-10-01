<?php
session_start();

// --- INFORMASI PENANGGUNG JAWAB (Placeholder) ---
$nama_pj = "Budi Santoso";
$divisi_pj = "Training"; 

// --- DUMMY DATA CUTI (disesuaikan dengan gambar terakhir) ---
$semua_data_pengajuan = [
    [
        'id' => 1,
        'nama_karyawan' => 'Andi',
        'divisi' => 'Training', 
        'tanggal_pengajuan' => '2025-10-01',
        'jenis' => 'Cuti',
        'status' => 'Diterima' // Status Diterima
    ],
    [
        'id' => 2,
        'nama_karyawan' => 'Eko',
        'divisi' => 'Training',
        'tanggal_pengajuan' => '2025-10-08',
        'jenis' => 'Cuti',
        'status' => 'Ditolak' // Status Ditolak
    ],
    [
        'id' => 3,
        'nama_karyawan' => 'Fani',
        'divisi' => 'Training',
        'tanggal_pengajuan' => '2025-10-15',
        'jenis' => 'Cuti',
        'status' => 'Menunggu' // Status Menunggu
    ],
];

// --- LOGIKA UTAMA: Filter data hanya untuk CUTI di divisi Penanggung Jawab ---
$data_cuti_divisi = [];
foreach ($semua_data_pengajuan as $pengajuan) {
    if ($pengajuan['jenis'] === 'Cuti' && $pengajuan['divisi'] === $divisi_pj) {
        $data_cuti_divisi[] = $pengajuan;
    }
}

// Mengatur default ke Oktober 2025 agar data baru terlihat
$month = isset($_GET['month']) ? (int)$_GET['month'] : 10;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;

// Logika navigasi kalender
$prev_month = $month - 1; $prev_year = $year;
$next_month = $month + 1; $next_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
if ($next_month > 12) { $next_month = 1; $next_year++; }

$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$first_day_of_week = date('w', $first_day);
$first_day_of_week = $first_day_of_week == 0 ? 6 : $first_day_of_week - 1;

// Mengelompokkan data cuti yang sudah difilter berdasarkan tanggal
$cuti_by_date = [];
foreach ($data_cuti_divisi as $cuti) {
    $date = $cuti['tanggal_pengajuan'];
    if (!isset($cuti_by_date[$date])) { $cuti_by_date[$date] = []; }
    $cuti_by_date[$date][] = $cuti;
}

$month_names = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalender Cuti Divisi - <?= htmlspecialchars($divisi_pj) ?></title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background: #FFFFFF; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#2e1f4f; font-weight:600; padding:8px 4px; display:block; }
    nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1 { color: #fff; text-align:left; font-size:28px; margin-bottom:30px; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
    .calendar-title { font-size: 24px; font-weight: 600; color: #1E105E; }
    .nav-btn { padding: 8px 16px; background: #4a3f81; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; }
    .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #ddd; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
    .calendar-day-header { background: #4a3f81; color: white; padding: 15px; text-align: center; font-weight: 600; }
    .calendar-day { background: white; min-height: 120px; padding: 8px; position: relative; cursor: pointer; }
    .day-number { font-weight: 600; margin-bottom: 5px; }
    
    /* [PERUBAHAN] CSS untuk status yang berbeda */
    .cuti-indicator { color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; display: block; margin-bottom: 3px; }
    .status-diterima { background-color: #28a745; }
    .status-ditolak { background-color: #dc3545; }
    .status-menunggu { background-color: #ffc107; color: #333; }

    .today { background: #e7f3ff !important; border: 2px solid #4a3f81; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 5% auto; padding: 20px; border-radius: 10px; width: 80%; max-width: 600px; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .modal-title { font-size: 20px; font-weight: 600; margin-bottom: 15px; color: #1E105E; }
    .karyawan-list { max-height: 400px; overflow-y: auto; }
    .karyawan-item { padding: 10px; border-bottom: 1px solid #eee; }
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
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
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
    <h1>Kalender Cuti Divisi <?= htmlspecialchars($divisi_pj) ?></h1>

    <div class="card">
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">← Sebelumnya</a>
                <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">Berikutnya →</a>
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
            
            <?php for ($i = 0; $i < $first_day_of_week; $i++): ?><div class="calendar-day"></div><?php endfor; ?>
            
            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                <?php
                $current_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                $is_today = $current_date == date('Y-m-d');
                $has_cuti = isset($cuti_by_date[$current_date]);
                ?>
                <div class="calendar-day <?= $is_today ? 'today' : '' ?>" onclick="showDetails('<?= $current_date ?>')">
                    <div class="day-number"><?= $day ?></div>
                    <?php if ($has_cuti): ?>
                        <?php foreach ($cuti_by_date[$current_date] as $item): ?>
                            <?php
                                // [PERUBAHAN] Menentukan class CSS berdasarkan status
                                $status_class = 'status-menunggu'; // Default
                                if ($item['status'] === 'Diterima') {
                                    $status_class = 'status-diterima';
                                } elseif ($item['status'] === 'Ditolak') {
                                    $status_class = 'status-ditolak';
                                }
                            ?>
                            <span class="cuti-indicator <?= $status_class ?>">
                                <?= htmlspecialchars($item['nama_karyawan']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close">×</span>
        <div class="modal-title" id="modalTitle">Detail Pengajuan</div>
        <div class="karyawan-list" id="karyawanList"></div>
    </div>
</div>

<script>
    const modal = document.getElementById('detailModal');
    const closeBtn = document.querySelector('.close');
    const modalTitle = document.getElementById('modalTitle');
    const karyawanList = document.getElementById('karyawanList');
    
    const dataByDate = <?= json_encode($cuti_by_date) ?>;
    
    function showDetails(date) {
        const itemsOnDate = dataByDate[date] || [];
        if (itemsOnDate.length === 0) { return; }
        
        const dateObj = new Date(date + 'T00:00:00');
        modalTitle.textContent = `Detail Cuti - ${dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
        
        let html = '';
        itemsOnDate.forEach(item => {
            // [PERUBAHAN] Menambahkan warna pada status di modal
            let statusColor = '#ffc107'; // Menunggu (kuning)
            if (item.status === 'Diterima') {
                statusColor = '#28a745'; // Hijau
            } else if (item.status === 'Ditolak') {
                statusColor = '#dc3545'; // Merah
            }

            html += `
                <div class="karyawan-item">
                    <strong>${item.nama_karyawan}</strong><br>
                    <small>Jenis Pengajuan: ${item.jenis}</small><br>
                    <small>Status: <span style="color: ${statusColor}; font-weight: bold;">${item.status}</span></small>
                </div>
            `;
        });
        
        karyawanList.innerHTML = html;
        modal.style.display = 'block';
    }
    
    closeBtn.onclick = function() { modal.style.display = 'none'; }
    window.onclick = function(event) { if (event.target == modal) { modal.style.display = 'none'; } }
</script>

</body>
</html>