<?php
// FILE: persetujuan_khl_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI DAN PENGAMBILAN DATA KHL
// ===========================================

// Pastikan session_start() ditambahkan
session_start();

// --- Konfigurasi Database ---
$server = "localhost";
$username = "root";
$password = "";
$database = "ypd_ibd";

// Membuat koneksi
$conn = mysqli_connect($server, $username, $password, $database);

// Memeriksa koneksi
if (!$conn) {
    // Pastikan XAMPP MySQL sudah berjalan
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Data Direktur (Placeholder) - Sebaiknya diambil dari session
$nama_direktur = "Direktur XYZ"; 
$jabatan = "Direktur";

// --- Query Pengambilan Data KHL ---
// Ambil data yang "Menunggu Direktur" dan juga data riwayat lainnya (opsional)
$sql = "
    SELECT 
        id,
        nama_karyawan,
        divisi,
        tanggal_khl,
        nama_proyek,
        status
    FROM 
        pengajuan_khl 
    ORDER BY 
        id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan KHL | Yayasan Purba Danarta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* ======================================= */
        /* CSS INI DIAMBIL DARI dashboard_direktur.php */
        /* ======================================= */
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE; /* Baru */
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF; 
            --text-color-light: #fff; /* Baru */
            --text-color-dark: #2e1f4f; 
            --shadow-light: rgba(0,0,0,0.15); 
            --shadow-strong: rgba(0,0,0,0.25); /* Baru */
            --success-color: #4CAF50; /* Disesuaikan */
            --danger-color: #dc3545; 
            --info-color: #FFC107; /* Untuk pending */
        } 
        body { 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh; 
            color: var(--text-color-light); 
            padding-bottom: 40px; 
        } 
        
        /* HEADER & NAVIGASI */
        header { 
            background: var(--card-bg); 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid var(--accent-color); /* Ditambahkan dari dashboard */
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
        nav li ul li a { 
            color: var(--text-color-dark); 
            font-weight: 400; 
            white-space: nowrap; 
        }

        /* KONTEN UTAMA */
        main { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        .welcome-section {
            text-align: left;
            margin-bottom: 30px;
        }
        .welcome-section h1 {
            font-size: 2.5rem;
            margin: 0;
            color: var(--text-color-light);
        }
        .welcome-section p {
            font-size: 1.1rem;
            margin-top: 5px;
            opacity: 0.9;
            color: var(--text-color-light);
        }
        .card { 
            background: var(--card-bg); 
            color: var(--text-color-dark); 
            border-radius: 20px; 
            padding: 30px 40px; 
            box-shadow: 0 5px 20px var(--shadow-light); 
        }
        h2 { 
            margin-top: 0; 
            color: var(--primary-color); 
            margin-bottom: 5px;
        }
        .card > p {
            margin-top: 0;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        /* TABEL */
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        } 
        .data-table th, .data-table td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
        } 
        .data-table th { background-color: #f8f9fa; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }
        .status-approved { background-color: var(--success-color); color: white; }
        .status-pending { background-color: var(--info-color); color: #333; }
        .status-rejected { background-color: var(--danger-color); color: white; }
        
        /* Tombol Aksi */
        .action-btn {
            background: var(--accent-color);
            color: var(--text-color-light);
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .action-btn:hover {
            background-color: #352d5c;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            header{
                flex-direction: column; 
                align-items: flex-start;
                padding: 20px;
            } 
            nav ul{
                flex-direction: column; 
                gap: 10px; 
                width: 100%; 
                margin-top: 15px;
            } 
            nav li ul { 
                position: static; 
                border: none; 
                box-shadow: none; 
                padding-left: 20px; 
            }
            .data-table, .data-table tbody, .data-table tr, .data-table td {
                display: block;
                width: 100%;
            }
            .data-table thead { display: none; }
            .data-table tr { 
                margin-bottom: 10px; 
                border: 1px solid #ddd; 
                border-radius: 8px; 
                overflow: hidden; 
            }
            .data-table td { 
                text-align: right; 
                padding-left: 50%; 
                position: relative; 
            }
            .data-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px);
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
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
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li>
                    <li><a href="pengajuan_cuti_direktur.php">Pengajuan Cuti</a></li>
                </ul>
            </li>
            <li style="border-bottom: 2px solid var(--accent-color);"><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php" style="color: var(--accent-color); font-weight: 700;">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li>
                    <li><a href="pengajuan_khl_direktur.php">Pengajuan KHL</a></li>
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
    <div class="welcome-section">
        <h1>Persetujuan KHL</h1>
        <p>Anda login sebagai <?= htmlspecialchars($jabatan) ?></p>
    </div>
    
    <div class="card">
        <h2>Daftar Pengajuan KHL</h2>
        <p>Lihat dan berikan persetujuan untuk pengajuan KHL.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal KHL</th>
                    <th>Nama Proyek</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($khl = mysqli_fetch_assoc($result)): 
                        $status_text = htmlspecialchars($khl['status']);
                        $status_class = 'status-pending';
                        if (strpos($status_text, 'Disetujui') !== false) {
                            $status_class = 'status-approved';
                        } elseif (strpos($status_text, 'Ditolak') !== false) {
                            $status_class = 'status-rejected';
                        } elseif (strpos($status_text, 'Menunggu Direktur') !== false) {
                            $status_class = 'status-pending';
                        }
                        
                        // Menentukan tombol aksi
                        $action_button = '';
                        if (strpos($status_text, 'Menunggu Direktur') !== false) {
                            // Anda harus membuat file detail_khl_direktur.php
                            $action_button = '<a href="detail_khl_direktur.php?id=' . $khl['id'] . '" class="action-btn">Lihat/Aksi</a>';
                        } else {
                            $action_button = '—'; // Tidak ada aksi untuk yang sudah diproses
                        }
                    ?>
                        <tr>
                            <td data-label="ID"><?= htmlspecialchars($khl['id']) ?></td>
                            <td data-label="Nama Karyawan"><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                            <td data-label="Divisi"><?= htmlspecialchars($khl['divisi']) ?></td>
                            <td data-label="Tanggal KHL"><?= date('d-m-Y', strtotime($khl['tanggal_khl'])) ?></td>
                            <td data-label="Nama Proyek"><?= htmlspecialchars($khl['nama_proyek']) ?></td>
                            <td data-label="Status"><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                            <td data-label="Aksi"><?= $action_button ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">Tidak ada pengajuan KHL yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php mysqli_close($conn); ?>
</body>
</html>