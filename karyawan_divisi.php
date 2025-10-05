    <?php
    session_start();
    require 'config.php';

    // 1. Cek sesi dan peran (role) pengguna
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
        header("Location: login_karyawan.php");
        exit();
    }

    // 2. Ambil data dari session
    $user = $_SESSION['user'];
    $nama_pj = $user['nama_lengkap'];
    $divisi_pj = $user['divisi'];
    $jabatan = "Penanggung Jawab Divisi " . $divisi_pj;

    // Hitung statistik untuk header (sama seperti dashboard)
    $stmt_cuti = $conn->prepare("SELECT COUNT(id) as total FROM pengajuan_cuti WHERE divisi = ? AND status = 'Menunggu'");
    $stmt_cuti->bind_param("s", $divisi_pj);
    $stmt_cuti->execute();
    $cuti_menunggu = $stmt_cuti->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_cuti->close();

    $stmt_khl = $conn->prepare("SELECT COUNT(id) as total FROM pengajuan_khl WHERE divisi = ? AND status = 'Menunggu'");
    $stmt_khl->bind_param("s", $divisi_pj);
    $stmt_khl->execute();
    $khl_menunggu = $stmt_khl->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_khl->close();

    $stmt_karyawan = $conn->prepare("SELECT COUNT(id_karyawan) as total FROM data_karyawan WHERE divisi = ? AND status_aktif = 'aktif'");
    $stmt_karyawan->bind_param("s", $divisi_pj);
    $stmt_karyawan->execute();
    $total_karyawan_divisi = $stmt_karyawan->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_karyawan->close();

    // 3. Ambil data karyawan dari database berdasarkan divisi penanggung jawab
    $karyawan_divisi = [];
    $sql = "SELECT id_karyawan, kode_karyawan, nama_lengkap, email, jabatan, role, no_telp, status_aktif 
            FROM data_karyawan 
            WHERE divisi = ? 
            ORDER BY nama_lengkap ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $divisi_pj);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $karyawan_divisi[] = $row;
        }
    }
    $stmt->close();
    $conn->close();
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Karyawan Divisi <?= htmlspecialchars($divisi_pj) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); }
            body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; color: var(--text-color-light); padding-bottom: 40px; }
            
            /* HEADER SAMA PERSIS DENGAN DASHBOARD */
            header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
            .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
            .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
            nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
            nav li { position: relative; }
            nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
            nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
            nav li:hover > ul { display: block; }
            nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; padding: 5px 20px; }
            
            main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
            .heading-section h1 { font-size: 2.5rem; margin: 0; color: #fff;}
            .heading-section p { font-size: 1.1rem; margin-top: 5px; opacity: 0.9; margin-bottom: 30px; color: #fff;}
            
            /* Card Styles */
            .card { background: var(--card-bg); color: var(--text-color-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 5px 20px var(--shadow-light); margin-bottom: 30px; }
            
            /* Search Styles */
            .search-container { display: flex; gap: 10px; margin-bottom: 25px; align-items: center; }
            .search-box { flex-grow: 1; position: relative; }
            .search-box input { width: 100%; padding: 12px 45px 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
            .search-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666; cursor: pointer; }
            
            /* Table Styles */
            .data-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: center; }
            .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #ddd; vertical-align:middle; }
            .data-table .text-left { text-align: left; }
            .data-table th { background-color: #f8f9fa; font-weight: 600; color: var(--text-color-dark); }
            .data-table tbody tr:hover { background-color: #f1f1f1; }
            .no-results { text-align: center; padding: 20px; color: #666; font-style: italic; }
            .status-aktif { color: #28a745; font-weight: bold; }
            .status-non-aktif { color: #dc3545; font-weight: bold; }
            
            @media (max-width: 768px) {
                .data-table { display: block; overflow-x: auto; }
            }
        </style>
    </head>
    <body>
        <!-- HEADER SAMA PERSIS DENGAN DASHBOARD -->
        <header>
            <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
            <nav>
                <ul>
                    <li><a href="dashboard_penanggungjawab.php">Beranda</a></li>
                    <li><a href="#">Cuti ▾</a>
                        <ul>
                            <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti</a></li>
                            <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Divisi</a></li>
                            <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                            <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
                            <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                        </ul>
                    </li>
                    <li><a href="#">KHL ▾</a>
                        <ul>
                            <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL</a></li>
                            <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Divisi</a></li>
                            <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                            <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
                            <li><a href="riwayat_khl_pribadi_penanggungjawab.php">Riwayat KHL Pribadi</a></li>
                        </ul>
                    </li>
                    <li><a href="karyawan_divisi.php">Karyawan Divisi</a></li>
                    <li><a href="#">Profil ▾</a>
                        <ul>
                            <li><a href="profil_penanggungjawab.php">Profil Saya</a></li>
                            <li><a href="logout2.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </header>
        
        <main>
            <div class="heading-section">
    <h1>Daftar Karyawan Divisi <?= htmlspecialchars($divisi_pj) ?></h1>
    <p>Halaman ini menampilkan seluruh data karyawan aktif yang berada di bawah pengawasan Anda.</p>
</div>

            <div class="card">
                <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 20px;">Data Karyawan Divisi <?= htmlspecialchars($divisi_pj) ?></h2>
                
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Cari karyawan di divisi ini...">
                        <i class="fas fa-search search-icon" id="searchButton"></i>
                    </div>
                </div>
                
                <table class="data-table" id="employeeTable">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Role</th>
                            <th>No. Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (empty($karyawan_divisi)): ?>
                            <tr>
                                <td colspan="7" class="no-results">Belum ada data karyawan di divisi ini</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($karyawan_divisi as $data): ?>
                            <tr>
                                <td><?= htmlspecialchars($data['kode_karyawan']); ?></td>
                                <td class='text-left'><?= htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($data['jabatan']); ?></td>
                                <td><?= htmlspecialchars($data['role']); ?></td>
                                <td><?= htmlspecialchars($data['no_telp']); ?></td>
                                <td><?= htmlspecialchars($data['email']); ?></td>
                                <td>
                                    <span class="<?= $data['status_aktif'] == 'aktif' ? 'status-aktif' : 'status-non-aktif'; ?>">
                                        <?= ucfirst($data['status_aktif']); ?>
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
            function performSearch() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                const tableRows = document.querySelectorAll('#tableBody tr');
                
                tableRows.forEach(row => {
                    // Pastikan tidak mencoba mencari di dalam baris "no results"
                    if (row.querySelector('td').colSpan === 7) return;

                    const rowText = row.textContent.toLowerCase();
                    if (rowText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Jalankan pencarian saat tombol search di-klik atau saat menekan Enter
            document.getElementById('searchButton').addEventListener('click', performSearch);
            document.getElementById('searchInput').addEventListener('keyup', function(event) {
                performSearch(); // Langsung cari saat mengetik
            });
        </script>
    </body>
    </html>