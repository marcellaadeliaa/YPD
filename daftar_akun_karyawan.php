<?php
session_start();

// Inisialisasi data karyawan di session jika belum ada, sesuai dengan data dari gambar
if (!isset($_SESSION['karyawan'])) {
    $_SESSION['karyawan'] = array(
        array(
            'kode' => 'YPD001',
            'nama' => 'Pico',
            'divisi' => 'Direksi',
            'role' => 'direktur',
            'telepon' => '081234567890',
            'email' => 'pico.dir@ypd.com'
        ),
        array(
            'kode' => 'YPD002',
            'nama' => 'Cell',
            'divisi' => 'SDM',
            'role' => 'admin',
            'telepon' => '081234567891',
            'email' => 'cell.sdm@ypd.com'
        ),
        array(
            'kode' => 'YPD101',
            'nama' => 'Adrian',
            'divisi' => 'Training',
            'role' => 'karyawan',
            'telepon' => '081234567892',
            'email' => 'adrian.karyawan@ypd.com'
        ),
        array(
            'kode' => 'YPD003',
            'nama' => 'Ria',
            'divisi' => 'Training',
            'role' => 'penanggung jawab',
            'telepon' => '081234567893',
            'email' => 'ria.direksi@ypd.com'
        ),
        array(
            'kode' => 'YPD004',
            'nama' => 'Dani',
            'divisi' => 'Keuangan',
            'role' => 'karyawan',
            'telepon' => '081234567894',
            'email' => 'dani.pj@ypd.com'
        ),
        array(
            'kode' => 'YPD005',
            'nama' => 'Budi',
            'divisi' => 'Konsultasi',
            'role' => 'penanggung jawab',
            'telepon' => '12345677654',
            'email' => 'budibudi@gmail.com'
        ),
        array(
            'kode' => 'YPD006',
            'nama' => 'Cici',
            'divisi' => 'Wisma',
            'role' => 'penanggung jawab',
            'telepon' => '918347914',
            'email' => 'cicici@gmail.com'
        ),
        array(
            'kode' => 'YPD007',
            'nama' => 'Dian',
            'divisi' => 'SDM',
            'role' => 'penanggung jawab',
            'telepon' => '5981731412',
            'email' => 'didi@gmail.com'
        ),
        array(
            'kode' => 'YPD008',
            'nama' => 'Jasmine',
            'divisi' => 'Sekretariat',
            'role' => 'penanggung jawab',
            'telepon' => '123415654312',
            'email' => 'minminja@gmail.com'
        ),
        array(
            'kode' => 'YPD009',
            'nama' => 'Mega',
            'divisi' => 'Keuangan',
            'role' => 'penanggung jawab',
            'telepon' => '12347358642879',
            'email' => 'gamega@gmail.com'
        )
    );
}

$karyawan = $_SESSION['karyawan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
        header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
        .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
        .logo img { width: 50px; height: 50px; object-fit: contain; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
        nav li { position:relative; }
        nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
        nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
        main { max-width:1400px; margin:40px auto; padding:0 20px; }
        .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
        .search-container { display: flex; gap: 10px; margin-bottom: 25px; align-items: center; }
        .search-box { flex-grow: 1; position: relative; }
        .search-box input { width: 100%; padding: 12px 45px 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
        .search-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666; cursor: pointer; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; text-decoration: none; }
        .btn-tambah { background-color: #2ecc71; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: center; }
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #ddd; vertical-align:middle; }
        .data-table .text-left { text-align: left; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        .action-cell { display: flex; gap: 10px; justify-content: center; }
        .action-btn { padding: 6px 12px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; border: none; text-decoration: none; color: white; display: inline-block; }
        .btn-lihat { background-color: #9b59b6; }
        .btn-edit { background-color: #f39c12; }
        .btn-hapus-small { background-color: #e74c3c; }
        .action-btn:hover { opacity: 0.9; transform: scale(1.05); }
        .welcome-section { margin-bottom: 20px; }
        .welcome-section h1 { color: #fff; font-size: 28px; margin-bottom: 5px; }
        .welcome-section h2 { color: #fff; opacity: 0.9; font-size: 20px; margin-bottom: 20px; font-weight: 400; }
        .no-results { text-align: center; padding: 20px; color: #666; font-style: italic; }
        .success-message { background-color: #d4edda; color: #155724; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        @media (max-width: 768px) {
            .search-container { flex-wrap: wrap; }
            .data-table { display: block; overflow-x: auto; white-space: nowrap;}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="image/namayayasan.png" alt="Logo Yayasan"> <span>Yayasan Purba Danarta</span>
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
                        <li><a href="daftar_akun_karyawan.php">Data Karyawan</a></li>
                    </ul>
                </li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="welcome-section">
            <h1>Welcome, Cell!</h1> <h2>Daftar Akun Karyawan</h2>
        </div>
        
        <div class="card">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan kode, nama, divisi, dll...">
                    <i class="fas fa-search search-icon" id="searchButton"></i>
                </div>
                <a href="tambah_akun_karyawan.php" class="btn btn-tambah">
                    <i class="fas fa-plus"></i> Tambah Akun Karyawan
                </a>
            </div>
            
            <table class="data-table" id="employeeTable">
                <thead>
                    <tr>
                        <th>No. Induk Karyawan</th>
                        <th>Nama Lengkap</th>
                        <th>Divisi / Posisi</th>
                        <th>Role</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($karyawan)): ?>
                        <tr>
                            <td colspan="7" class="no-results">Belum ada data karyawan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($karyawan as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['kode']); ?></td>
                            <td class='text-left'><?php echo htmlspecialchars($data['nama']); ?></td>
                            <td><?php echo htmlspecialchars($data['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($data['role']); ?></td>
                            <td><?php echo htmlspecialchars($data['telepon']); ?></td>
                            <td><?php echo htmlspecialchars($data['email']); ?></td>
                            <td class='action-cell'>
                                <a href="detail_karyawan.php?id=<?php echo $data['kode']; ?>" class='action-btn btn-lihat'>Lihat</a>
                                <a href="edit_karyawan.php?id=<?php echo $data['kode']; ?>" class='action-btn btn-edit'>Edit</a>
                                <button class='action-btn btn-hapus-small' data-id='<?php echo $data['kode']; ?>' data-nama='<?php echo $data['nama']; ?>'>Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Fungsi pencarian
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tableRows = document.querySelectorAll('#tableBody tr');
            let found = false;

            tableRows.forEach(row => {
                // Sembunyikan pesan 'no-results' jika ada
                if (row.querySelector('.no-results')) {
                    row.style.display = 'none';
                    return;
                }
                
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                    found = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Hapus pesan 'tidak ditemukan' yang lama
            const oldNoResults = document.getElementById('noResultsRow');
            if (oldNoResults) {
                oldNoResults.remove();
            }

            // Tampilkan pesan jika tidak ada hasil
            if (!found) {
                const noResults = document.createElement('tr');
                noResults.id = 'noResultsRow';
                noResults.innerHTML = `<td colspan="7" class="no-results">Tidak ada hasil ditemukan untuk "${document.getElementById('searchInput').value}"</td>`;
                document.getElementById('tableBody').appendChild(noResults);
            }
        }

        document.getElementById('searchButton').addEventListener('click', performSearch);
        document.getElementById('searchInput').addEventListener('keyup', function(event) {
            performSearch(); // Langsung cari saat mengetik
        });
        
        // Fungsi untuk tombol hapus
        document.querySelectorAll('.btn-hapus-small').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                const employeeName = this.getAttribute('data-nama');
                
                if (confirm(`Apakah Anda yakin ingin menghapus data karyawan ${employeeName}?`)) {
                    // Di aplikasi nyata, ini akan mengarah ke skrip penghapusan
                    // window.location.href = `hapus_karyawan.php?id=${employeeId}`;
                    alert(`Data untuk ${employeeName} (ID: ${employeeId}) akan dihapus. (Ini adalah simulasi)`);
                    
                    // Untuk simulasi di halaman ini, kita bisa menghapus barisnya
                    this.closest('tr').remove();
                }
            });
        });
    </script>
</body>
</html>