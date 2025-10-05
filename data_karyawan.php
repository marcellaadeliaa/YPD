<?php
session_start();
require_once 'config.php';

// Ambil data karyawan dari database menggunakan MySQLi
$sql = "SELECT id_karyawan, kode_karyawan, nama_lengkap, email, password, jabatan, divisi, role, no_telp, status_aktif, created_at 
        FROM data_karyawan 
        ORDER BY kode_karyawan";
$result = $conn->query($sql);

$karyawan = [];
if ($result->num_rows > 0) {
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
    <title>Data Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Anda tetap sama */
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
        header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
        .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
        .logo img { width: 140px; height: 50px; object-fit: contain; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
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
        .btn-hapus { background-color: #d9534f; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: center; }
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #ddd; vertical-align:middle; }
        .data-table .text-left { text-align: left; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .btn-lihat { display: inline-block; padding: 6px 12px; border-radius: 5px; text-decoration: none; color: #fff; font-weight: bold; background-color: #4a3f81; }
        .status-diterima { color: #28a745; font-weight: bold; }
        .status-tidak-lolos { color: #d9534f; font-weight: bold; }
        
        /* Tambahan untuk halaman data karyawan */
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            align-items: center;
        }
        
        .search-box {
            flex-grow: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-search {
            background-color: #3498db;
            color: white;
        }
        
        .btn-tambah {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .action-cell {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        
        .btn-lihat {
            background-color: #9b59b6;
            color: white;
        }
        
        .btn-edit {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-hapus-small {
            background-color: #e74c3c;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }
        
        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .status-aktif {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-non-aktif {
            color: #dc3545;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .search-container {
                flex-wrap: wrap;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
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
            <h2 class="page-title">Data Karyawan</h2>
            <!-- Pesan Sukses -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Cari Karyawan / Kode / Jabatan...">
                    <i class="fas fa-search search-icon" id="searchButton"></i>
                </div>
                <a href="tambah_karyawan.php" class="btn btn-tambah">
                    <i class="fas fa-plus"></i> Tambah Karyawan
                </a>
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
                        <th>Password</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($karyawan)): ?>
                        <tr>
                            <td colspan="10" class="no-results">Belum ada data karyawan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($karyawan as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['kode_karyawan']); ?></td>
                            <td class='text-left'><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($data['jabatan']); ?></td>
                            <td><?php echo htmlspecialchars($data['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($data['role']); ?></td>
                            <td><?php echo htmlspecialchars($data['no_telp']); ?></td>
                            <td><?php echo htmlspecialchars($data['email']); ?></td>
                            <td><?php echo htmlspecialchars($data['password']); ?></td>
                            <td>
                                <span class="<?php echo $data['status_aktif'] == 'aktif' ? 'status-aktif' : 'status-non-aktif'; ?>">
                                    <?php echo ucfirst($data['status_aktif']); ?>
                                </span>
                            </td>
                            <td class='action-cell'>
                                <button class='action-btn btn-lihat' data-id='<?php echo $data['id_karyawan']; ?>'>Lihat</button>
                                <button class='action-btn btn-edit' data-id='<?php echo $data['id_karyawan']; ?>'>Edit</button>
                                <button class='action-btn btn-hapus-small' data-id='<?php echo $data['id_karyawan']; ?>' data-nama='<?php echo htmlspecialchars($data['nama_lengkap']); ?>'>Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Fungsi pencarian yang lebih canggih
        document.getElementById('searchButton').addEventListener('click', performSearch);
        document.getElementById('searchInput').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        });

        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tableRows = document.querySelectorAll('#tableBody tr');
            let found = false;

            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                    found = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Tampilkan pesan jika tidak ada hasil
            if (!found && searchTerm !== '') {
                if (!document.getElementById('noResults')) {
                    const noResults = document.createElement('tr');
                    noResults.id = 'noResults';
                    noResults.innerHTML = `<td colspan="10" class="no-results">Tidak ada hasil untuk "${searchTerm}"</td>`;
                    document.getElementById('tableBody').appendChild(noResults);
                }
            } else {
                const noResults = document.getElementById('noResults');
                if (noResults) {
                    noResults.remove();
                }
            }
        }

        // Fungsi untuk tombol aksi
        document.querySelectorAll('.action-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent;
                const employeeId = this.getAttribute('data-id');
                const employeeName = this.getAttribute('data-nama');
                
                if (action === 'Hapus') {
                    if (confirm(`Apakah Anda yakin ingin menghapus data karyawan ${employeeName}?`)) {
                        // Redirect ke halaman hapus
                        window.location.href = `hapus_karyawan.php?id=${employeeId}`;
                    }
                } else if (action === 'Lihat') {
                    // Redirect ke halaman detail karyawan
                    window.location.href = `detail_karyawan.php?id=${employeeId}`;
                } else if (action === 'Edit') {
                    // Redirect ke halaman edit karyawan
                    window.location.href = `edit_karyawan.php?id=${employeeId}`;
                }
            });
        });
        
        // Reset pencarian ketika input dikosongkan
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value === '') {
                const tableRows = document.querySelectorAll('#tableBody tr');
                tableRows.forEach(row => {
                    row.style.display = '';
                });
                const noResults = document.getElementById('noResults');
                if (noResults) {
                    noResults.remove();
                }
            }
        });
    </script>
</body>
</html>