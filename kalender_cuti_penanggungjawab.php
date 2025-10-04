<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') { header("Location: login_karyawan.php"); exit(); }
$divisi_pj = $_SESSION['user']['divisi'];

$cuti_by_date = [];
$sql = "SELECT nama_karyawan, tgl_mulai, tgl_selesai, status FROM pengajuan_cuti WHERE divisi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $divisi_pj);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $current_date = new DateTime($row['tgl_mulai']);
    $end_date = new DateTime($row['tgl_selesai']);
    while ($current_date <= $end_date) {
        $date_key = $current_date->format('Y-m-d');
        if (!isset($cuti_by_date[$date_key])) { $cuti_by_date[$date_key] = []; }
        $cuti_by_date[$date_key][] = ['nama_karyawan' => $row['nama_karyawan'], 'status' => $row['status']];
        $current_date->modify('+1 day');
    }
}
$stmt->close();
$conn->close();

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$prev_month = $month - 1; $prev_year = $year; if($prev_month < 1){$prev_month=12;$prev_year--;}
$next_month = $month + 1; $next_year = $year; if($next_month > 12){$next_month=1;$next_year++;}
$first_day=mktime(0,0,0,$month,1,$year);
$days_in_month=date('t',$first_day);
$first_day_of_week=date('w',$first_day);
$first_day_of_week=$first_day_of_week==0?6:$first_day_of_week-1;
$month_names=[1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kalender Cuti Divisi</title>
    <style>
        body{margin:0;font-family:'Segoe UI',sans-serif;background:linear-gradient(180deg,#1E105E 0%,#8897AE 100%);min-height:100vh;color:#333}header{background:#fff;padding:20px 40px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 15px rgba(0,0,0,.15)}.logo{display:flex;align-items:center;gap:16px;font-weight:500;font-size:20px;color:#2e1f4f}.logo img{width:50px;height:50px;object-fit:contain;border-radius:50%}nav ul{list-style:none;margin:0;padding:0;display:flex;gap:30px}nav li{position:relative}nav a{text-decoration:none;color:#2e1f4f;font-weight:600;padding:8px 4px;display:block}nav li ul{display:none;position:absolute;top:100%;left:0;background:#fff;padding:10px 0;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.15);min-width:200px;z-index:999}nav li:hover>ul{display:block}nav li ul li{padding:5px 20px}nav li ul li a{color:#333;font-weight:400;white-space:nowrap}main{max-width:1400px;margin:40px auto;padding:0 20px}.card{background:#fff;border-radius:20px;padding:30px 40px;box-shadow:0 2px 10px rgba(0,0,0,.15)}.calendar-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:15px;background:#f8f9fa;border-radius:10px}.calendar-title{font-size:24px;font-weight:600;color:#1E105E}.nav-btn{padding:8px 16px;background:#4a3f81;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;text-decoration:none}.calendar{display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#ddd;border:1px solid #ddd;border-radius:10px;overflow:hidden}.calendar-day-header{background:#4a3f81;color:#fff;padding:15px;text-align:center;font-weight:600}.calendar-day{background:#fff;min-height:120px;padding:8px;position:relative;cursor:pointer}.day-number{font-weight:600;margin-bottom:5px}.cuti-indicator{color:#fff;padding:3px 8px;border-radius:4px;font-size:11px;display:block;margin-bottom:3px}.status-diterima{background-color:#28a745}.status-ditolak{background-color:#dc3545}.status-menunggu{background-color:#ffc107;color:#333}.today{background:#e7f3ff!important;border:2px solid #4a3f81}.modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,.5)}.modal-content{background-color:#fff;margin:5% auto;padding:20px;border-radius:10px;width:80%;max-width:600px}.close{color:#aaa;float:right;font-size:28px;font-weight:700;cursor:pointer}.modal-title{font-size:20px;font-weight:600;margin-bottom:15px;color:#1E105E}.karyawan-list{max-height:400px;overflow-y:auto}.karyawan-item{padding:10px;border-bottom:1px solid #eee}
    </style>
</head>
<body>
<header></header>
<main>
    <div class="card">
        <div class="calendar-header">
             <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">← Sebelumnya</a>
            <span class="calendar-title"><?= $month_names[$month] ?> <?= $year ?></span>
            <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">Berikutnya →</a>
        </div>
        <div class="calendar">
            <div class="calendar-day-header">Sen</div><div class="calendar-day-header">Sel</div><div class="calendar-day-header">Rab</div><div class="calendar-day-header">Kam</div><div class="calendar-day-header">Jum</div><div class="calendar-day-header">Sab</div><div class="calendar-day-header">Min</div>
            <?php for($i=0; $i<$first_day_of_week; $i++): ?><div class="calendar-day"></div><?php endfor; ?>
            <?php for($day=1; $day<=$days_in_month; $day++): 
                $current_date = date('Y-m-d', mktime(0,0,0,$month,$day,$year));
                $has_cuti = isset($cuti_by_date[$current_date]);
            ?>
            <div class="calendar-day" onclick="showDetails('<?=$current_date?>')">
                <div class="day-number"><?=$day?></div>
                <?php if($has_cuti): foreach($cuti_by_date[$current_date] as $item): ?>
                <span class="cuti-indicator status-<?= strtolower($item['status']) ?>"><?= htmlspecialchars($item['nama_karyawan']) ?></span>
                <?php endforeach; endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</main>
<div id="detailModal" class="modal">
    <div class="modal-content"><span class="close">×</span><div id="modalTitle" class="modal-title"></div><div id="karyawanList" class="karyawan-list"></div></div>
</div>
<script>
    const modal=document.getElementById('detailModal'),closeBtn=document.querySelector('.close'),modalTitle=document.getElementById('modalTitle'),karyawanList=document.getElementById('karyawanList'),dataByDate=<?=json_encode($cuti_by_date)?>;function showDetails(e){const t=dataByDate[e]||[];if(0===t.length)return;const a=new Date(e+"T00:00:00");modalTitle.textContent=`Detail Cuti - ${a.toLocaleDateString("id-ID",{day:"numeric",month:"long",year:"numeric"})}`;let l="";t.forEach(e=>{let t="#ffc107";"Diterima"===e.status?t="#28a745":"Ditolak"===e.status&&(t="#dc3545"),l+=`<div class="karyawan-item"><strong>${e.nama_karyawan}</strong><br><small>Status: <span style="color:${t};font-weight:bold">${e.status}</span></small></div>`}),karyawanList.innerHTML=l,modal.style.display="block"}closeBtn.onclick=function(){modal.style.display="none"},window.onclick=function(e){e.target==modal&&(modal.style.display="none")};
</script>
</body>
</html>