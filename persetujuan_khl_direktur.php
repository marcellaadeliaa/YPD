<?php
// FILE: persetujuan_khl_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI DATABASE
// ===========================================
session_start();

$server = "localhost";
$username = "root";
$password = "";
$database = "ypd_ibd";

$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// ===========================================
// BAGIAN 2: PEMROSESAN AKSI (Setujui / Tolak)
// ===========================================
if (isset($_GET['id']) && isset($_GET['action'])) {
    $khl_id = (int)$_GET['id'];
    $action = $_GET['action'];
    $new_status = '';

    if ($action === 'approve') {
        $new_status = 'Diterima';
    } elseif ($action === 'reject') {
        $new_status = 'Ditolak';
    }

    if (!empty($new_status)) {
        $stmt = mysqli_prepare($conn, "UPDATE pengajuan_khl SET status = ?, waktu_persetujuan = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $khl_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: persetujuan_khl_direktur.php");
            exit();
        } else {
            echo "<script>alert('Gagal memproses aksi: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// ===========================================
// BAGIAN 3: AMBIL DATA KHL + FILTER PENCARIAN
// ===========================================
$filter = "";
if (isset($_GET['cari']) && !empty($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $filter = "WHERE nama_karyawan LIKE '%$keyword%' 
               OR divisi LIKE '%$keyword%' 
               OR nama_proyek LIKE '%$keyword%'";
}

$sql = "
    SELECT 
        id, nama_karyawan, divisi, nama_proyek, tanggal_khl, jam_mulai_kerja, jam_akhir_kerja,
        tanggal_akhir_khl, jam_mulai_libur, jam_akhir_libur, status
    FROM 
        pengajuan_khl
    $filter
    ORDER BY 
        CASE 
            WHEN status NOT IN ('Diterima', 'Ditolak') THEN 1
            ELSE 2
        END,
        id DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query Gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan KHL | Yayasan Purba Danarta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #1E105E;
            --secondary-color: #8897AE;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-color-light: #fff;
            --text-color-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
            --success-bg: #d4edda;
            --success-text: #155724;
            --danger-bg: #f8d7da;
            --danger-text: #721c24;
            --pending-bg: #fff3cd;
            --pending-text: #856404;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
            min-height: 100vh;
        }
        header {
            background: var(--card-bg);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent-color);
            flex-wrap: wrap;
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
            gap: 30px;
        }
        nav li { position: relative; }
        nav a {
            text-decoration: none;
            color: var(--text-color-dark);
            font-weight: 600;
            padding: 8px 4px;
            display: block;
            transition: color 0.3s ease;
        }
        nav a:hover { color: var(--accent-color); }
        nav li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--card-bg);
            padding: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-light);
            min-width: 200px;
            z-index: 999;
        }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 20px; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; }

        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 5px 20px var(--shadow-light);
        }
        h2 { color: var(--primary-color); margin-top: 0; }

        /* Search bar */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .search-bar button, .search-bar a {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .search-bar button { background: var(--accent-color); color: white; }
        .search-bar a { background: #777; color: white; }

        /* Scroll horizontal */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            min-width: 1200px;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .data-table th { background: #f8f9fa; font-weight: 600; }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-menunggu { background: var(--pending-bg); color: var(--pending-text); }
        .status-diterima { background: var(--success-bg); color: var(--success-text); }
        .status-ditolak { background: var(--danger-bg); color: var(--danger-text); }
        .action-buttons { display: flex; gap: 10px; align-items: center; }
        .btn-action {
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-success { background-color: var(--success-text); }
        .btn-danger { background-color: var(--danger-text); }
        .action-icon { font-size: 24px; font-weight: bold; }
        .icon-success { color: var(--success-text); }
        .icon-danger { color: var(--danger-text); }
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
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li> 
                </ul> 
            </li> 
            <li><a href="#">KHL ▾</a> 
                <ul> 
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li> 
                    <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li> 
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
    <div class="card">
        <h2>Daftar Pengajuan KHL (Kerja Hari Libur)</h2>
        <p>Tinjau, setujui, atau tolak pengajuan kerja hari libur yang masuk.</p>

        <!-- Form Pencarian -->
        <form method="GET" action="persetujuan_khl_direktur.php" class="search-bar">
            <input type="text" name="cari" placeholder="Cari nama, divisi, atau proyek..." 
                   value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
            <button type="submit">Cari</button>
            <a href="persetujuan_khl_direktur.php">Reset</a>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Karyawan</th>
                        <th>Divisi</th>
                        <th>Proyek</th>
                        <th>Tanggal KHL</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Tanggal Lembur</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($khl = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($khl['id']) ?></td>
                                <td><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                                <td><?= htmlspecialchars($khl['divisi']) ?></td>
                                <td><?= htmlspecialchars($khl['nama_proyek']) ?></td>
                                <td><?= date('d-m-Y', strtotime($khl['tanggal_khl'])) ?></td>
                                <td><?= $khl['jam_mulai_kerja'] ?></td>
                                <td><?= $khl['jam_akhir_kerja'] ?></td>
                                <td><?= date('d-m-Y', strtotime($khl['tanggal_akhir_khl'])) ?></td>
                                <td><?= $khl['jam_mulai_libur'] ?></td>
                                <td><?= $khl['jam_akhir_libur'] ?></td>
                                <td>
                                    <?php
                                    $status = trim($khl['status']);
                                    if ($status == 'Diterima') {
                                        echo '<span class="status-badge status-diterima">Diterima</span>';
                                    } elseif ($status == 'Ditolak') {
                                        echo '<span class="status-badge status-ditolak">Ditolak</span>';
                                    } else {
                                        echo '<span class="status-badge status-menunggu">Menunggu Persetujuan</span>';
                                    }
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <?php
                                    if ($status == 'Diterima') {
                                        echo '<span class="action-icon icon-success" title="Telah Disetujui">✓</span>';
                                    } elseif ($status == 'Ditolak') {
                                        echo '<span class="action-icon icon-danger" title="Telah Ditolak">✗</span>';
                                    } else {
                                        echo '<a href="?id='.$khl['id'].'&action=approve" class="btn-action btn-success" onclick="return confirm(\'Setujui pengajuan ini?\');">Setujui</a>';
                                        echo '<a href="?id='.$khl['id'].'&action=reject" class="btn-action btn-danger" onclick="return confirm(\'Tolak pengajuan ini?\');">Tolak</a>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="12" style="text-align:center; padding:20px;">Tidak ada data pengajuan KHL.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php mysqli_close($conn); ?>
</body>
</html>
