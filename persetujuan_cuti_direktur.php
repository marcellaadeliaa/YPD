<?php
// FILE: persetujuan_cuti_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI DAN PENGAMBILAN DATA
// ===========================================

// --- Konfigurasi Database ---
$server   = "localhost";
$username = "root";
$password = "";
$database = "ypd_ibd";

// Membuat koneksi
$conn = mysqli_connect($server, $username, $password, $database);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// ===========================================
// BAGIAN 2: PEMROSESAN AKSI (Setujui/Tolak)
// ===========================================

if (isset($_GET['id']) && isset($_GET['action'])) {
    $cuti_id    = $_GET['id']; // ID cuti dari URL
    $action     = $_GET['action'];
    $new_status = '';

    if ($action === 'approve') {
        $new_status = 'Disetujui Direktur';
    } elseif ($action === 'reject') {
        $new_status = 'Ditolak';
    }

    if (!empty($new_status)) {
        $stmt = mysqli_prepare($conn, "UPDATE pengajuan_cuti SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $cuti_id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: persetujuan_cuti_direktur.php");
            exit();
        } else {
            echo "<script>alert('Gagal memproses aksi: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// ===========================================
// BAGIAN 3: QUERY PENGAMBILAN DATA
// ===========================================

$sql = "
    SELECT 
        id,
        nama_karyawan,
        divisi,
        jenis_cuti,
        tanggal_mulai,
        tanggal_akhir,
        alasan,
        status
    FROM 
        pengajuan_cuti 
    ORDER BY 
        id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Gagal: " . mysqli_error($conn));
}

$role_title = "";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Cuti Karyawan | Yayasan Purba Danarta</title>
    <style>
        :root {
            --primary-color: #1E105E;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-color-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
            min-height: 100vh;
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
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav a:hover { color: var(--accent-color); }
        nav li ul {
            display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg);
            padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light);
            min-width: 200px; z-index: 999;
        }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 20px; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; }

        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); color: var(--text-color-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 5px 20px var(--shadow-light); }
        h2 { margin-top: 0; color: var(--primary-color); }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background-color: #f8f9fa; }

        .status-badge {
            display: inline-block; padding: 5px 10px; border-radius: 12px;
            font-size: 12px; font-weight: bold; white-space: nowrap;
        }
        .status-approved { background-color: #4CAF50; color: white; }
        .status-pending  { background-color: #FFC107; color: #333; }
        .status-rejected { background-color: var(--danger-color); color: white; }

        .action-buttons { display: flex; gap: 10px; }
        .btn-action { color: #fff; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 14px; text-align: center; }
        .btn-success { background-color: var(--success-color); }
        .btn-danger  { background-color: var(--danger-color); }

        .final-action-icon { font-size: 1.2em; display: block; text-align: center; }
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
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayat_cuti_karyawan.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuan_cuti_pribadi.php">Ajukan Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayat_khl_karyawan.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuan_khl_pribadi.php">Ajukan KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_list.php">Karyawan</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_direktur.php">Profil Saya</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<main>
    <div class="card">
        <h2>Daftar Pengajuan Cuti</h2>
        <p>Anda memiliki wewenang untuk Menyetujui atau Menolak pengajuan cuti yang telah diajukan.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Jenis Cuti</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($cuti = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $status_text  = htmlspecialchars($cuti['status']);
                        $status_class = 'status-pending';
                        $is_approved_by_direktur = (strpos($status_text, 'Disetujui Direktur') !== false);
                        $is_rejected             = (strpos($status_text, 'Ditolak') !== false);

                        if ($is_approved_by_direktur) {
                            $status_class = 'status-approved';
                        } elseif ($is_rejected) {
                            $status_class = 'status-rejected';
                        }

                        $show_action_buttons = (!$is_approved_by_direktur && !$is_rejected);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($cuti['id']) ?></td>
                            <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($cuti['divisi']) ?></td>
                            <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                            <td><?= date('d-m-Y', strtotime($cuti['tanggal_mulai'])) ?></td>
                            <td><?= date('d-m-Y', strtotime($cuti['tanggal_akhir'])) ?></td>
                            <td><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                            <td class="action-buttons">
                                <?php if ($show_action_buttons): ?>
                                    <a href="?id=<?= $cuti['id'] ?>&action=approve" class="btn-action btn-success">Setujui</a>
                                    <a href="?id=<?= $cuti['id'] ?>&action=reject" class="btn-action btn-danger">Tolak</a>
                                <?php elseif ($is_approved_by_direktur): ?>
                                    <span class="final-action-icon">✅</span>
                                <?php elseif ($is_rejected): ?>
                                    <span class="final-action-icon">❌</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">Tidak ada pengajuan cuti yang ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php mysqli_close($conn); ?>
</body>
</html>
