<?php
session_start();
require_once 'config.php';

// 🔒 Batasi hanya untuk direktur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

// Ambil data karyawan dari database
$sql = "SELECT id_karyawan, kode_karyawan, nama_lengkap, email, password, jabatan, divisi, role, no_telp, 
             sisa_cuti_tahunan, sisa_cuti_lustrum, status_aktif, created_at 
        FROM data_karyawan 
        ORDER BY kode_karyawan";
$result = $conn->query($sql);

$karyawan = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $karyawan[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Karyawan - Direktur</title>
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
        margin:0; 
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
        min-height:100vh; 
        color:#333; 
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
    /* === AKHIR PERBAIKAN HEADER DAN NAVIGASI === */

    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size:16px; margin-top:0; margin-bottom:30px; opacity:0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size:28px; font-weight:600; text-align:center; margin-bottom:30px; color:var(--primary-color); }

    .search-container { display:flex; gap:10px; margin-bottom:25px; align-items:center; }
    .search-box { flex-grow:1; position:relative; }
    .search-box input { width:100%; padding:12px 45px 12px 15px; border:1px solid #ccc; border-radius:8px; font-size:16px; box-sizing:border-box; }
    .search-icon { position:absolute; right:15px; top:50%; transform:translateY(-50%); color:#666; cursor:pointer; }

    .data-table { width:100%; border-collapse:collapse; font-size:14px; text-align:center; }
    .data-table th, .data-table td { padding:12px 10px; border-bottom:1px solid #ddd; vertical-align:middle; }
    .data-table th { background-color:#f8f9fa; font-weight:600; }
    .data-table tbody tr:hover { background-color:#f1f1f1; }

    .status-aktif { color:#28a745; font-weight:bold; }
    .status-non-aktif { color:#dc3545; font-weight:bold; }

    .no-results { text-align:center; padding:20px; color:#666; font-style:italic; }

    @media (max-width:768px) {
        /* Tambahan responsive untuk header */
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
        /* Akhir responsive header */
        
        .search-container { flex-wrap:wrap; }
        .data-table { display:block; overflow-x:auto; }
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
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?>!</h1>
    <p class="admin-title">Direktur</p>

    <div class="card">
        <h2 class="page-title">Data Karyawan</h2>

        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Cari Karyawan / Kode / Jabatan...">
                <i class="fas fa-search search-icon" id="searchButton"></i>
            </div>
        </div>

        <table class="data-table" id="employeeTable">
            <thead>
                <tr>
                    <th>Kode Karyawan</th>
                    <th>Nama Lengkap</th>
                    <th>Jabatan</th>
                    <th>Divisi</th>
                    <th>Role</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                    <th>Sisa Cuti Tahunan</th>
                    <th>Sisa Cuti Lustrum</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (empty($karyawan)): ?>
                    <tr><td colspan="10" class="no-results">Belum ada data karyawan</td></tr>
                <?php else: ?>
                    <?php foreach($karyawan as $data): ?>
                    <tr>
                        <td><?= htmlspecialchars($data['kode_karyawan']) ?></td>
                        <td><?= htmlspecialchars($data['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($data['jabatan']) ?></td>
                        <td><?= htmlspecialchars($data['divisi']) ?></td>
                        <td><?= htmlspecialchars($data['role']) ?></td>
                        <td><?= htmlspecialchars($data['no_telp']) ?></td>
                        <td><?= htmlspecialchars($data['email']) ?></td>
                        <td><?= htmlspecialchars($data['sisa_cuti_tahunan']) ?></td>
                        <td><?= htmlspecialchars($data['sisa_cuti_lustrum']) ?></td>
                        <td>
                            <span class="<?= $data['status_aktif']=='aktif' ? 'status-aktif' : 'status-non-aktif' ?>">
                                <?= ucfirst($data['status_aktif']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.getElementById('searchButton').addEventListener('click', performSearch);
document.getElementById('searchInput').addEventListener('keyup', function(event) {
    if (event.key === 'Enter') performSearch();
});

function performSearch() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr');
    let found = false;

    // Hapus pesan "Belum ada data karyawan" jika ada
    const initialNoData = document.querySelector('.no-results');
    if (initialNoData && initialNoData.textContent.includes('Belum ada data karyawan')) {
         // Jangan tampilkan pesan "Tidak ada hasil" jika ini adalah pesan awal
    } else {
        const existingNoResults = document.getElementById('noResults');
        if (existingNoResults) existingNoResults.remove();
    }


    rows.forEach(row => {
        // Lewati baris "Belum ada data karyawan"
        if (row.querySelector('.no-results') && row.querySelector('.no-results').textContent.includes('Belum ada data karyawan')) {
            return;
        }

        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
            found = true;
        } else {
            row.style.display = 'none';
        }
    });

    const noResults = document.getElementById('noResults');
    if (!found && term !== '') {
        // Cek apakah ada pesan "Belum ada data karyawan" di awal
        const initialRowCount = <?= count($karyawan) ?>;
        if (initialRowCount > 0) { // Hanya tampilkan pesan "Tidak ada hasil" jika memang ada data awal
            if (!noResults) {
                const tr = document.createElement('tr');
                tr.id = 'noResults';
                tr.innerHTML = `<td colspan="10" class="no-results">Tidak ada hasil untuk "${term}"</td>`;
                document.getElementById('tableBody').appendChild(tr);
            }
        }
    } else if (noResults) {
        noResults.remove();
    }
}
</script>

</body>
</html>