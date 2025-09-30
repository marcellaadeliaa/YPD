<?php
session_start();
require 'config.php';

// --- Mengambil semua pelamar yang sudah selesai proses seleksi ---
// Yaitu yang statusnya 'Diterima' atau 'Tidak Lolos'
$query = $conn->prepare("
    SELECT id, nama_lengkap, posisi_dilamar, no_telp, email, status
    FROM data_pelamar 
    WHERE status = 'Diterima' OR status = 'Tidak Lolos'
    ORDER BY id DESC
");
$query->execute();
$riwayat_pelamar = $query->get_result();

// --- Fungsi untuk menentukan status setiap tahapan ---
function getStageStatus($finalStatus) {
    if ($finalStatus == 'Diterima') {
        return 'Lolos';
    }
    // Jika tidak lolos, kita tidak tahu di tahap mana dia gagal, jadi tampilkan strip
    return '-';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Pelamar - Admin SDM</title>
<style>
    /* Menggunakan style yang konsisten dari halaman admin sebelumnya */
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
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
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
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
</style>
</head>
<body>

<header>
    <div class="logo">
        <img src="https://yt3.googleusercontent.com/ytc/AIdro_k21dE_e_T4s2-9e5aB2H3-_hDUa8sGAky5TTsD=s900-c-k-c0x00ffffff-no-rj" alt="Logo Yayasan">
        <span>Yayasan Purba Danarta</span>
    </div>
    <nav>
        <ul>
        <li><a href="dashboardadmin.php">Beranda</a></li>
        <li><a href="#">Cuti ▾</a>
            <ul>
            <li><a href="administrasi_cuti.php">Administrasi Cuti</a></li>
            <li><a href="#">Riwayat Cuti Pegawai</a></li>
            </ul>
        </li>
        <li><a href="#">KHL ▾</a>
            <ul>
                <li><a href="administrasi_khl.php">Administrasi KHL</a></li>
                <li><a href="#">Riwayat KHL Pegawai</a></li>
            </ul>
        </li>
        <li><a href="#">Lamaran Kerja ▾</a>
            <ul>
                <li><a href="administrasi_pelamar.php">Administrasi Pelamar</a></li>
                <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
            </ul>
        </li>
        <li><a href="#">Karyawan ▾</a></li>
        <li><a href="#">Profil ▾</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, Cell!</h1>
    <p class="admin-title">Administrator</p>

    <div class="card">
        <h2 class="page-title">Riwayat Pelamar</h2>
        
        <div class="action-bar">
            <input type="search" placeholder="Cari riwayat pelamar...">
            <button class="btn-cari">Cari</button>
            <button class="btn-hapus">Hapus</button>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-left">ID</th>
                        <th class="text-left">Nama Pelamar</th>
                        <th>Divisi</th>
                        <th>No. Telepon</th>
                        <th class="text-left">Alamat Email</th>
                        <th>Detail Data</th>
                        <th>Administratif</th>
                        <th>Wawancara</th>
                        <th>Psikotes</th>
                        <th>Kesehatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayat_pelamar->num_rows > 0): ?>
                        <?php while($row = $riwayat_pelamar->fetch_assoc()): ?>
                            <?php
                                // Menentukan status untuk setiap tahap berdasarkan status final
                                $stage_status = getStageStatus($row['status']);
                            ?>
                            <tr>
                                <td class="text-left"><?= $row['id'] ?></td>
                                <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                                <td><?= htmlspecialchars($row['no_telp']) ?></td>
                                <td class="text-left"><?= htmlspecialchars($row['email']) ?></td>
                                <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-lihat">Lihat Data</a></td>
                                <td><?= $stage_status ?></td>
                                <td><?= $stage_status ?></td>
                                <td><?= $row['posisi_dilamar'] == 'divisi1' ? 'Tidak Ada' : $stage_status ?></td>
                                <td><?= $row['posisi_dilamar'] == 'divisi1' ? 'Tidak Ada' : $stage_status ?></td>
                                <td>
                                    <?php if ($row['status'] == 'Diterima'): ?>
                                        <span class="status-diterima">Diterima</span>
                                    <?php else: ?>
                                        <span class="status-tidak-lolos">Tidak Lolos</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11" style="text-align:center; padding: 20px;">Belum ada riwayat pelamar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</main>

</body>
</html>