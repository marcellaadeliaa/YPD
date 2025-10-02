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
    $cuti_id    = $_GET['id']; // ID cuti dari URL
    $action     = $_GET['action'];
    $new_status = '';

    // Logika Status: Direktur menyetujui, status menjadi "Disetujui Direktur"
    if ($action === 'approve') {
        $new_status = 'Disetujui Direktur';
    } elseif ($action === 'reject') {
        $new_status = 'Ditolak';
    }

    if (!empty($new_status)) {
        // Gunakan prepared statement untuk keamanan
        $stmt = mysqli_prepare($conn, "UPDATE pengajuan_cuti SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $cuti_id);

        if (mysqli_stmt_execute($stmt)) {
            // Setelah aksi, redirect untuk menghilangkan parameter GET
            header("Location: persetujuan_cuti_direktur.php");
            exit();
        } else {
            // Gunakan skrip PHP untuk pesan kesalahan
            echo "<script>alert('Gagal memproses aksi: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// ===========================================
// BAGIAN 3: QUERY PENGAMBILAN DATA (PERBAIKAN FOKUS)
// Logika: Hanya ambil pengajuan yang statusnya masih menunggu persetujuan Direktur,
// yaitu yang BUKAN 'Disetujui Direktur' dan BUKAN 'Ditolak'.
// Ini memastikan cuti Direktur sendiri (yang otomatis 'Disetujui Direktur') tidak muncul.
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
    WHERE
        status NOT LIKE 'Disetujui Direktur'
    AND
        status NOT LIKE 'Ditolak'
    ORDER BY 
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
        /* ==================== */
        /* CSS STYLING */
        /* ==================== */
        :root {
            --primary-color: #1E105E;
            --accent-color: #4a3f81;
            --card-bg: #FFFFFF;
            --text-color-dark: #2e1f4f;
            --text-color-light: #fff;
            --shadow-light: rgba(0,0,0,0.15);
            --success-color: #28a745;
            --danger-color: #dc3545;
            --pending-color: #FFC107; /* Kuning/Orange untuk pending */
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            border-bottom: 2px solid var(--accent-color);
            flex-wrap: wrap;
        }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; transition: color 0.3s ease; }
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
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }

        .status-badge {
            display: inline-block; padding: 5px 10px; border-radius: 12px;
            font-size: 12px; font-weight: bold; white-space: nowrap;
        }
        /* Hanya perlu style untuk Pending karena Approved/Rejected sudah difilter di query */
        .status-pending  { background-color: var(--pending-color); color: #333; } 
        

        .action-buttons { display: flex; gap: 10px; }
        .btn-action { color: #fff; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 14px; text-align: center; }
        .btn-success { background-color: var(--success-color); }
        .btn-danger  { background-color: var(--danger-color); }

        /* Media Queries untuk Responsif */
        @media(max-width:768px){ 
            /* [Responsive CSS] */
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
        <p>Anda memiliki wewenang untuk Menyetujui atau Menolak pengajuan cuti yang telah diajukan.</p>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
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
                
                <?php while($cuti = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $status_text     = htmlspecialchars($cuti['status']);
                    // Semua yang lolos filter di query (Bagian 3) adalah 'pending'
                    $status_class = 'status-pending'; 
                    $show_action_buttons = true;

                    ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($cuti['id']) ?></td>
                        <td data-label="Nama Karyawan"><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                        <td data-label="Divisi"><?= htmlspecialchars($cuti['divisi']) ?></td>
                        <td data-label="Jenis Cuti"><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                        <td data-label="Tgl Mulai"><?= date('d-m-Y', strtotime($cuti['tanggal_mulai'])) ?></td>
                        <td data-label="Tgl Selesai"><?= date('d-m-Y', strtotime($cuti['tanggal_akhir'])) ?></td>
                        <td data-label="Status"><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                        <td data-label="Aksi" class="action-buttons">
                            <?php if ($show_action_buttons): ?>
                                <a href="?id=<?= $cuti['id'] ?>&action=approve" class="btn-action btn-success" onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI cuti ini?');">Setujui</a>
                                <a href="?id=<?= $cuti['id'] ?>&action=reject" class="btn-action btn-danger" onclick="return confirm('Apakah Anda yakin ingin MENOLAK cuti ini?');">Tolak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align:center; padding: 20px; border: 1px solid #eee; border-radius: 8px;">Tidak ada pengajuan cuti yang membutuhkan persetujuan Anda saat ini.</p>
        <?php endif; ?>

    </div>
</main>
<?php mysqli_close($conn); ?>
</body>
</html>