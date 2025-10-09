<?php
session_start();
require_once 'config.php';

// 🔒 Batasi hanya untuk direktur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

// Ambil semua data cuti yang sudah disetujui
$query = "
    SELECT nama_karyawan, divisi, jabatan, role, jenis_cuti, tanggal_mulai, tanggal_akhir, alasan 
    FROM data_pengajuan_cuti 
    WHERE status = 'Diterima'
";
$result = $conn->query($query);

$cutiData = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tanggalMulai = new DateTime($row['tanggal_mulai']);
        $tanggalAkhir = new DateTime($row['tanggal_akhir']);
        
        // Loop melalui setiap hari dari tanggal mulai hingga tanggal akhir
        $currentDate = clone $tanggalMulai;
        while ($currentDate <= $tanggalAkhir) {
            $cutiData[$currentDate->format('Y-m-d')][] = $row;
            $currentDate->modify('+1 day');
        }
    }
}

// Ambil tanggal hari ini
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
// Mengatur hari pertama di bulan (1=Senin, 7=Minggu)
$firstDayOfMonth = date('N', strtotime("$year-$month-01")); 
$today = date('Y-m-d');

function monthName($month) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $months[$month];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalender Cuti Direktur</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* CSS Variables */
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
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
        color: #333;
        min-height: 100vh;
        padding-bottom: 40px;
    }

    /* === PERBAIKAN HEADER DAN NAVIGASI DIMULAI DI SINI === */
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
        gap: 40px; /* Jarak antar tombol navigasi utama */
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
        padding: 15px 0; /* Padding vertikal pada kotak dropdown */
        border-radius: 8px; 
        box-shadow: 0 2px 10px var(--shadow-light); 
        min-width: 220px; 
        z-index: 999; 
    }
    
    nav li:hover > ul { 
        display: block; 
    }
    
    /* Jarak antar item di dalam dropdown */
    nav li ul li { 
        margin-bottom: 7px; 
        padding: 0; 
    }

    nav li ul li:last-child {
        margin-bottom: 0; 
    }
    
    nav li ul li a { 
        color: var(--text-color-dark); 
        font-weight: 400; 
        white-space: nowrap; 
        padding: 10px 25px; /* Padding yang lebih lega */
    }
    /* === PERBAIKAN HEADER DAN NAVIGASI SELESAI DI SINI === */

    main {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    h1 { color: var(--text-color-light); text-align: center; margin-bottom: 20px; }

    .calendar-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }

    .month-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .month-nav a {
        background: var(--primary-color);
        color: var(--text-color-light);
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
        transition: background 0.3s;
    }
    
    .month-nav a:hover {
        background: var(--accent-color);
    }

    .month-title {
        font-size: 22px;
        font-weight: 600;
        color: var(--primary-color);
    }

    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        text-align: center;
    }

    .day {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 12px;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        transition: 0.3s;
    }

    .day:hover {
        background: #eae8ff;
        transform: scale(1.03);
    }

    .day-number {
        font-weight: 600;
        color: var(--primary-color);
    }

    .cuti-count {
        background: #d1c4e9;
        color: var(--primary-color);
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 13px;
        margin-top: 8px;
        align-self: center;
    }

    .today {
        border: 2px solid var(--primary-color);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }

    .modal-content {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 25px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        position: relative;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .close-btn {
        position: absolute;
        top: 10px; right: 15px;
        font-size: 22px;
        cursor: pointer;
        color: #555;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th, td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
        text-align: left;
    }

    th { background: #f8f9fa; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        header { 
            flex-direction: column; 
            padding: 15px 20px; 
            gap: 15px; 
        }
    
        nav ul { 
            flex-direction: column; 
            gap: 10px; 
            width: 100%; 
        }
    
        nav li ul { 
            position: static; 
            box-shadow: none; 
            border: 1px solid #e0e0e0; 
            padding: 5px 0; 
        }
        
        nav li ul li a {
            padding: 8px 25px;
        }

        .calendar {
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .day {
            min-height: 60px;
            padding: 8px 4px;
        }
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
            <li><a href="dashboarddirektur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Semua Cuti</a></li>
                    <li><a href="riwayat_cuti_pribadi_direktur.php">Riwayat Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_direktur.php">Kalender Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat Semua KHL</a></li>
                    <li><a href="riwayat_khl_pribadi_direktur.php">Riwayat KHL Pribadi</a></li>
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
    <h1>Kalender Cuti Seluruh Karyawan</h1>

    <div class="calendar-card">
        <div class="month-nav">
            <?php 
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $prevYear = $month == 1 ? $year - 1 : $year;
            $nextMonth = $month == 12 ? 1 : $month + 1;
            $nextYear = $month == 12 ? $year + 1 : $year;
            ?>
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>"><i class="fa fa-chevron-left"></i></a>
            <div class="month-title"><?= monthName($month) . ' ' . $year ?></div>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>"><i class="fa fa-chevron-right"></i></a>
        </div>

        <div class="calendar">
            <div style="font-weight: 700; color: #1E105E; padding: 12px; background: #e0e0e0; border-radius: 10px;">Sen</div>
            <div style="font-weight: 700; color: #1E105E; padding: 12px; background: #e0e0e0; border-radius: 10px;">Sel</div>
            <div style="font-weight: 700; color: #1E105E; padding: 12px; background: #e0e0e0; border-radius: 10px;">Rab</div>
            <div style="font-weight: 700; color: #1E105E; padding: 12px; background: #e0e0e0; border-radius: 10px;">Kam</div>
            <div style="font-weight: 700; color: #1E105E; padding: 12px; background: #e0e0e0; border-radius: 10px;">Jum</div>
            <div style="font-weight: 700; color: #1E105E; padding: 12px; background: #e0e0e0; border-radius: 10px;">Sab</div>
            <div style="font-weight: 700; color: #dc3545; padding: 12px; background: #e0e0e0; border-radius: 10px;">Min</div>
            
            <?php
            // Isi kolom kosong untuk menggeser hari pertama
            for ($i = 1; $i < $firstDayOfMonth; $i++) echo "<div class='day' style='background: #f1f1f1; cursor: default;'></div>";
            
            for ($day = 1; $day <= $daysInMonth; $day++):
                $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $isToday = $date === $today ? 'today' : '';
                $count = isset($cutiData[$date]) ? count($cutiData[$date]) : 0;
            ?>
            <div 
                class="day <?= $isToday ?> <?= $count > 0 ? 'has-cuti' : '' ?>" 
                onclick="showDetail('<?= $date ?>')" 
                style="<?= $count > 0 ? 'background: #FFE0B2; border: 1px solid #FFB74D;' : '' ?>"
            >
                <div class="day-number"><?= $day ?></div>
                <?php if ($count > 0): ?>
                    <div class="cuti-count"><?= $count ?> cuti</div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<div class="modal" id="cutiModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Detail Cuti</h3>
        <div id="cutiList"></div>
    </div>
</div>

<script>
const cutiData = <?= json_encode($cutiData) ?>;
const monthNames = {
    1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
    5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
    9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
};

function formatDisplayDate(dateString) {
    const parts = dateString.split('-');
    const year = parts[0];
    const month = parseInt(parts[1], 10);
    const day = parseInt(parts[2], 10);
    return `${day} ${monthNames[month]} ${year}`;
}


function showDetail(date) {
    const modal = document.getElementById('cutiModal');
    const list = document.getElementById('cutiList');
    const modalTitle = document.getElementById('modalTitle');
    const data = cutiData[date];

    modalTitle.textContent = `Detail Cuti Tanggal ${formatDisplayDate(date)}`;

    if (!data || data.length === 0) {
        list.innerHTML = "<p style='text-align: center; color: #555; margin-top: 20px;'>Tidak ada karyawan cuti pada tanggal ini.</p>";
    } else {
        let html = `<table>
                        <tr><th>Nama</th><th>Divisi</th><th>Jabatan</th><th>Jenis Cuti</th><th>Alasan</th></tr>`;
        data.forEach(d => {
            html += `<tr>
                        <td>${d.nama_karyawan}</td>
                        <td>${d.divisi}</td>
                        <td>${d.jabatan}</td>
                        <td>${d.jenis_cuti}</td>
                        <td>${d.alasan}</td>
                     </tr>`;
        });
        html += `</table>`;
        list.innerHTML = html;
    }
    modal.style.display = "flex";
}

function closeModal() {
    document.getElementById('cutiModal').style.display = "none";
}

window.onclick = function(e) {
    if (e.target == document.getElementById('cutiModal')) closeModal();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('cutiModal').style.display === 'flex') {
        closeModal();
    }
});
</script>

</body>
</html>