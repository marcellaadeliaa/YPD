<?php
// FILE: persetujuan_cuti_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI DAN PENGAMBILAN DATA
// ===========================================

// --- Konfigurasi Database ---
$server = "localhost";
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
    $cuti_id    = $_GET['id'];
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
        id, nama_karyawan, divisi, jenis_cuti, tanggal_mulai, tanggal_akhir, alasan, status
    FROM 
        pengajuan_cuti 
    ORDER BY 
        CASE 
            WHEN status NOT IN ('Disetujui Direktur', 'Ditolak') THEN 1
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
    <title>Daftar Cuti Karyawan | Yayasan Purba Danarta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #1E105E;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-color-dark: #2e1f4f;
            --text-color-light: #fff;
            --shadow-light: rgba(0,0,0,0.15);
            --success-bg: #d4edda;
            --success-text: #155724;
            --danger-bg: #f8d7da;
            --danger-text: #721c24;
            --pending-bg: #fff3cd;
            --pending-text: #856404;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; padding-bottom: 40px; }
        header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent-color); flex-wrap: wrap; box-shadow: 0 4px 15px var(--shadow-light); }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; transition: color 0.3s ease; }
        nav a:hover { color: var(--accent-color); }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 20px; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; }
        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); color: var(--text-color-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 5px 20px var(--shadow-light); }
        h2 { margin-top: 0; color: var(--primary-color); }
        .table-responsive { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; white-space: nowrap; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .status-menunggu { background-color: var(--pending-bg); color: var(--pending-text); } 
        .status-disetujui { background-color: var(--success-bg); color: var(--success-text); }
        .status-ditolak { background-color: var(--danger-bg); color: var(--danger-text); }
        .action-buttons { display: flex; gap: 10px; align-items: center; }
        .btn-action { color: #fff; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 14px; text-align: center; }
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
        <h2>Daftar Pengajuan Cuti</h2>
        <p>Tinjau, setujui, atau tolak pengajuan cuti yang masuk.</p>
        
        <div class="table-responsive">
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
                            <tr>
                                <td><?= htmlspecialchars($cuti['id']) ?></td>
                                <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                                <td><?= htmlspecialchars($cuti['divisi']) ?></td>
                                <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                                <td><?= date('d-m-Y', strtotime($cuti['tanggal_mulai'])) ?></td>
                                <td><?= date('d-m-Y', strtotime($cuti['tanggal_akhir'])) ?></td>
                                <td>
                                    <?php
                                        // ### LOGIKA STATUS BARU ###
                                        $status_clean = trim($cuti['status']); // Membersihkan spasi tak terlihat
                                        $status_text = htmlspecialchars($status_clean);
                                        $status_class = '';

                                        if ($status_text == 'Disetujui Direktur') {
                                            $status_class = 'status-disetujui';
                                            $status_text = 'Disetujui';
                                        } elseif ($status_text == 'Ditolak') {
                                            $status_class = 'status-ditolak';
                                        } else {
                                            $status_class = 'status-menunggu';
                                            $status_text = 'Menunggu Persetujuan';
                                        }
                                    ?>
                                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td class="action-buttons">
                                    <?php
                                        // ### LOGIKA AKSI BARU ###
                                        $current_status = trim($cuti['status']); // Membersihkan spasi tak terlihat

                                        if ($current_status == 'Disetujui Direktur') {
                                            // Jika sudah disetujui, tampilkan ikon centang
                                            echo '<span class="action-icon icon-success" title="Telah Disetujui">✓</span>';
                                        } elseif ($current_status == 'Ditolak') {
                                            // Jika sudah ditolak, tampilkan ikon silang
                                            echo '<span class="action-icon icon-danger" title="Telah Ditolak">✗</span>';
                                        } else {
                                            // Jika status masih menunggu, tampilkan tombol
                                            echo '<a href="?id='.$cuti['id'].'&action=approve" class="btn-action btn-success" onclick="return confirm(\'Anda yakin ingin MENYETUJUI cuti ini?\');">Setujui</a>';
                                            echo '<a href="?id='.$cuti['id'].'&action=reject" class="btn-action btn-danger" onclick="return confirm(\'Anda yakin ingin MENOLAK cuti ini?\');">Tolak</a>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 20px;">Tidak ada data pengajuan cuti.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php mysqli_close($conn); ?>
</body>
</html>