<?php
// FILE: persetujuan_khl_direktur.php
// ===========================================
// BAGIAN 1: KONEKSI DAN PENGAMBILAN DATA KHL
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
    // Pastikan XAMPP MySQL sudah berjalan
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// --- Query Pengambilan Data KHL ---
$sql = "
    SELECT 
        id,
        nama_karyawan,
        divisi,
        tanggal_khl,     -- Tanggal KHL
        alasan,        
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

$role_title = "Direktur (Hanya Melihat Data)";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar KHL Karyawan | Yayasan Purba Danarta</title>
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
        
        /* Header dan Navigasi */
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
            gap: 30px; 
        }
        nav li { position: relative; }
        nav a { 
            text-decoration: none; 
            color: var(--text-color-dark); 
            font-weight: 600; 
            padding: 8px 4px; 
            display: block; 
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
        
        /* Konten Utama */
        main { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
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
        }
        
        /* Tabel */
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
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }
        .status-approved { background-color: #4CAF50; color: white; }
        .status-pending { background-color: #FFC107; color: #333; }
        .status-rejected { background-color: var(--danger-color); color: white; }
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
        <h2>Daftar Pengajuan KHL (<?= $role_title ?>)</h2>
        <p>Tampilan ini memberikan gambaran umum data KHL di seluruh perusahaan.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal KHL</th>
                    <th>Alasan (Keterangan)</th> 
                    <th>Status</th>
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
                        }
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($khl['id']) ?></td>
                            <td><?= htmlspecialchars($khl['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($khl['divisi']) ?></td>
                            <td><?= date('d-m-Y', strtotime($khl['tanggal_khl'])) ?></td>
                            <td><?= htmlspecialchars($khl['alasan']) ?></td>
                            <td><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">Tidak ada pengajuan KHL yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php mysqli_close($conn); ?>
</body>
</html>
