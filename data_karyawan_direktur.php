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
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:#fff; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width:50px; height:50px; object-fit:contain; border-radius:50%; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
    nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size:16px; margin-top:0; margin-bottom:30px; opacity:0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size:28px; font-weight:600; text-align:center; margin-bottom:30px; color:#1E105E; }

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
            <li><a href="dashboard_direktur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_karyawan.php">Persetujuan Cuti</a></li>
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

    rows.forEach(row => {
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
        if (!noResults) {
            const tr = document.createElement('tr');
            tr.id = 'noResults';
            tr.innerHTML = `<td colspan="10" class="no-results">Tidak ada hasil untuk "${term}"</td>`;
            document.getElementById('tableBody').appendChild(tr);
        }
    } else if (noResults) {
        noResults.remove();
    }
}
</script>

</body>
</html>
