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
    WHERE status = 'diterima'
";
$result = $conn->query($query);

$cutiData = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tanggalMulai = new DateTime($row['tanggal_mulai']);
        $tanggalAkhir = new DateTime($row['tanggal_akhir']);
        while ($tanggalMulai <= $tanggalAkhir) {
            $cutiData[$tanggalMulai->format('Y-m-d')][] = $row;
            $tanggalMulai->modify('+1 day');
        }
    }
}

// Ambil tanggal hari ini
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
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
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(180deg, #1E105E 0%, #8897AE 100%);
        color: #333;
        min-height: 100vh;
    }

    header {
        background: #fff;
        padding: 20px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #34377c;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 16px;
        font-weight: 500;
        font-size: 20px;
        color: #2e1f4f;
    }

    .logo img {
        width: 50px;
        height: 50px;
        object-fit: contain;
        border-radius: 50%;
    }

    nav ul {
        list-style: none;
        display: flex;
        gap: 30px;
        margin: 0;
        padding: 0;
    }

    nav li { position: relative; }
    nav a { text-decoration: none; color: #333; font-weight: 600; }

    nav li ul {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        padding: 10px 0;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
        min-width: 200px;
    }

    nav li:hover > ul { display: block; }
    nav li ul li { padding: 5px 20px; }

    main {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    h1 { color: #fff; text-align: center; margin-bottom: 20px; }

    .calendar-card {
        background: #fff;
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
        background: #1E105E;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
    }

    .month-title {
        font-size: 22px;
        font-weight: 600;
        color: #1E105E;
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
        color: #1E105E;
    }

    .cuti-count {
        background: #d1c4e9;
        color: #1E105E;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 13px;
        margin-top: 8px;
        align-self: center;
    }

    .today {
        border: 2px solid #1E105E;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
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
    }

    th { background: #f8f9fa; }
</style>
</head>
<body>

<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
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
            <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>"><i class="fa fa-chevron-left"></i></a>
            <div class="month-title"><?= monthName($month) . ' ' . $year ?></div>
            <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>"><i class="fa fa-chevron-right"></i></a>
        </div>

        <div class="calendar">
            <?php
            for ($i = 1; $i < $firstDayOfMonth; $i++) echo "<div></div>";
            for ($day = 1; $day <= $daysInMonth; $day++):
                $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $isToday = $date === $today ? 'today' : '';
                $count = isset($cutiData[$date]) ? count($cutiData[$date]) : 0;
            ?>
            <div class="day <?= $isToday ?>" onclick="showDetail('<?= $date ?>')">
                <div class="day-number"><?= $day ?></div>
                <?php if ($count > 0): ?>
                    <div class="cuti-count"><?= $count ?> cuti</div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<!-- Modal -->
<div class="modal" id="cutiModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3>Detail Cuti</h3>
        <div id="cutiList"></div>
    </div>
</div>

<script>
const cutiData = <?= json_encode($cutiData) ?>;

function showDetail(date) {
    const modal = document.getElementById('cutiModal');
    const list = document.getElementById('cutiList');
    const data = cutiData[date];

    if (!data) {
        list.innerHTML = "<p>Tidak ada karyawan cuti pada tanggal ini.</p>";
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
</script>

</body>
</html>
