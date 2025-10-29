<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

$sql = "SELECT id_karyawan, kode_karyawan, nama_lengkap, email, jabatan, divisi, role, no_telp, status_aktif, deleted_at, deleted_by 
        FROM data_karyawan 
        WHERE is_deleted = 1 
        ORDER BY deleted_at DESC";
$result = $conn->query($sql);

$karyawan_terhapus = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $karyawan_terhapus[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Data Karyawan Terhapus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
        .page-title { font-size: 30px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
        .backup-info { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: center; }
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #ddd; vertical-align:middle; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }
        .data-table tbody tr { background-color: #fff8f8; }
        .action-cell { display: flex; gap: 10px; justify-content: center; }
        .action-btn { padding: 6px 12px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; border: none; }
        .btn-restore { background-color: #2ecc71; color: white; }
        .btn-back { 
            background-color: #3498db; 
            color: white; 
            margin-bottom: 20px; 
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }
        .btn-restore {
            cursor: pointer !important;
            pointer-events: auto !important;
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
        <a href="data_karyawan.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Data Karyawan
        </a>
        
        <div class="card">
            <h2 class="page-title">Backup Data Karyawan Terhapus</h2>
            
            <div class="backup-info">
                <i class="fas fa-info-circle"></i> 
                Data berikut adalah karyawan yang telah dihapus dan tersimpan sebagai backup.
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Karyawan</th>
                        <th>Nama Lengkap</th>
                        <th>Jabatan</th>
                        <th>Divisi</th>
                        <th>Role</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                        <th>Dihapus Pada</th>
                        <th>Dihapus Oleh</th> 
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($karyawan_terhapus)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 20px; color: #666;">
                                Tidak ada data karyawan yang dihapus
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($karyawan_terhapus as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['kode_karyawan']); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($data['jabatan']); ?></td>
                            <td><?php echo htmlspecialchars($data['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($data['role']); ?></td>
                            <td><?php echo htmlspecialchars($data['no_telp']); ?></td>
                            <td>
                                <span style="color: #dc3545; font-weight: bold;">
                                    <?php echo ucfirst($data['status_aktif']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($data['deleted_at'])); ?></td>
                            <td>
                                <?php 
                                if (!empty($data['deleted_by'])) {
                                    $admin_sql = "SELECT nama_lengkap FROM data_karyawan WHERE kode_karyawan = ?";
                                    $admin_stmt = $conn->prepare($admin_sql);
                                    $admin_stmt->bind_param("s", $data['deleted_by']);
                                    $admin_stmt->execute();
                                    $admin_result = $admin_stmt->get_result();
                                    
                                    if ($admin_result->num_rows > 0) {
                                        $admin_data = $admin_result->fetch_assoc();
                                        echo htmlspecialchars($admin_data['nama_lengkap']) . " (" . $data['deleted_by'] . ")";
                                    } else {
                                        echo htmlspecialchars($data['deleted_by']);
                                    }
                                    $admin_stmt->close();
                                } else {
                                    echo "<span style='color: #666; font-style: italic;'>Tidak tercatat</span>";
                                }
                                ?>
                            </td>
                            <td class='action-cell'>
                                <button class='action-btn btn-restore' 
                                        onclick="restoreKaryawan(<?php echo $data['id_karyawan']; ?>, '<?php echo addslashes(htmlspecialchars($data['nama_lengkap'])); ?>')">
                                    <i class="fas fa-undo"></i> Restore
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function restoreKaryawan(id, nama) {
            if (confirm('Apakah Anda yakin ingin mengembalikan data karyawan "' + nama + '"?')) {
                window.location.href = 'restore_karyawan.php?id=' + id;
            }
        }
        
        console.log('restoreKaryawan function loaded:', typeof restoreKaryawan);
        
        document.querySelectorAll('.btn-restore').forEach((btn, index) => {
            console.log('Restore button', index, ':', btn);
            btn.addEventListener('click', function(e) {
                console.log('Button clicked:', e.target);
            });
        });
    </script>
</body>
</html>