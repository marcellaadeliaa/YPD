<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

// --- PROSES PENCARIAN ---
$search_keyword = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_keyword = trim($_GET['search']);
}

// --- QUERY DENGAN PENCARIAN ---
$sql = "
    SELECT 
        dp.id, 
        dp.nama_lengkap, 
        dp.posisi_dilamar,
        COALESCE(rp.status_administratif, 'Tidak Diambil') as status_administratif,
        COALESCE(rp.status_wawancara, 'Tidak Diambil') as status_wawancara,
        COALESCE(rp.status_psikotes, 'Tidak Diambil') as status_psikotes,
        COALESCE(rp.status_kesehatan, 'Tidak Diambil') as status_kesehatan,
        COALESCE(rp.status_final, 'Tidak Diambil') as status_final
    FROM 
        data_pelamar dp
    LEFT JOIN 
        riwayat_pelamar rp ON dp.id = rp.pelamar_id
    WHERE 
        dp.status IN ('Diterima', 'Tidak Lolos')
";

// Tambahkan kondisi pencarian jika ada keyword
if (!empty($search_keyword)) {
    $sql .= " AND (dp.nama_lengkap LIKE ? OR dp.posisi_dilamar LIKE ?)";
}

$sql .= " ORDER BY dp.id DESC";

$query_pelamar = $conn->prepare($sql);

// Bind parameter jika ada pencarian
if (!empty($search_keyword)) {
    $search_term = "%$search_keyword%";
    $query_pelamar->bind_param("ss", $search_term, $search_term);
}

$query_pelamar->execute();
$riwayat_pelamar_result = $query_pelamar->get_result();

// --- TAMBAHKAN FUNGSI INI ---
function displayStatus($status) {
    switch($status) {
        case 'Lolos':
            return '<span style="color: #28a745; font-weight: bold;">Lolos</span>';
        case 'Tidak Lolos':
            return '<span style="color: #dc3545; font-weight: bold;">Tidak Lolos</span>';
        case 'Diterima':
            return '<span style="color: #28a745; font-weight: bold;">Diterima</span>';
        case 'Tidak Diambil':
        default:
            return '<span style="color: #6c757d; font-style: italic;">Tidak Diambil</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pelamar</title>
    <style>
        /* CSS Lengkap Anda akan ditaruh di sini */
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
        header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; flex-wrap:wrap; }
        .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
        .logo img { width: 140px; height: 50px; object-fit: contain; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; align-items: center; }
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
        .action-bar { display: flex; gap: 10px; margin-bottom: 25px; align-items: center; }
        .action-bar input[type="search"] { flex-grow: 1; padding: 10px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; }
        .action-bar button { padding: 10px 25px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; }
        .btn-cari { background-color: #4a3f81; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: center; }
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #ddd; vertical-align: middle; }
        .data-table .text-left { text-align: left; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .btn-aksi { display: inline-block; padding: 6px 12px; border-radius: 5px; text-decoration: none; color: #fff; font-weight: bold; font-size:14px; }
        .btn-lihat { background-color: #4a3f81; }
        .status-diterima { color: #28a745; font-weight: bold; }
        .status-tidak-lolos { color: #d9534f; font-weight: bold; }
        .status-tidak-diambil { color:grey; font-style:italic; }
        .no-data { text-align:center; padding: 20px; color: #777; }
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
        <h2 class="page-title">Riwayat Pelamar</h2>
        
        <form method="GET" action="" class="action-bar">
            <input type="search" name="search" placeholder="Cari riwayat pelamar..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn-cari">Cari</button>
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="riwayat_pelamar.php" style="padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 16px;">Reset</a>
            <?php endif; ?>
        </form>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-left">ID</th>
                        <th class="text-left">Nama Pelamar</th>
                        <th>Posisi</th>
                        <th>Administratif</th>
                        <th>Wawancara</th>
                        <th>Psikotes</th>
                        <th>Kesehatan</th>
                        <th>Status Final</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayat_pelamar_result->num_rows > 0): ?>
                        <?php while($row = $riwayat_pelamar_result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-left"><?= $row['id'] ?></td>
                                <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                                <td><?= displayStatus($row['status_administratif']) ?></td>
                                <td><?= displayStatus($row['status_wawancara']) ?></td>
                                <td><?= displayStatus($row['status_psikotes']) ?></td>
                                <td><?= displayStatus($row['status_kesehatan']) ?></td>
                                <td>
                                    <?php if ($row['status_final'] == 'Diterima'): ?>
                                        <span class="status-diterima">Diterima</span>
                                    <?php elseif ($row['status_final'] == 'Tidak Lolos'): ?>
                                        <span class="status-tidak-lolos">Tidak Lolos</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="no-data">Belum ada riwayat pelamar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>