<?php
session_start();
require 'config.php';

// --- LOGIKA UNTUK PENGUMUMAN UMUM ---
if (isset($_POST['action_pengumuman'])) {
    if ($_POST['action_pengumuman'] == 'tambah') {
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $tanggal = $_POST['tanggal'];
        
        $stmt = $conn->prepare("INSERT INTO pengumuman_umum (judul, isi, tanggal, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sss", $judul, $isi, $tanggal);
        $stmt->execute();
        
    } elseif ($_POST['action_pengumuman'] == 'edit') {
        $id = $_POST['id'];
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $tanggal = $_POST['tanggal'];
        
        $stmt = $conn->prepare("UPDATE pengumuman_umum SET judul = ?, isi = ?, tanggal = ? WHERE id = ?");
        $stmt->bind_param("sssi", $judul, $isi, $tanggal, $id);
        $stmt->execute();
        
    } elseif ($_POST['action_pengumuman'] == 'hapus') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM pengumuman_umum WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    header("Location: administrasi_pelamar.php");
    exit;
}

// --- LOGIKA UNTUK UPDATE STATUS PELAMAR ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    $newStatus = '';

    if ($action == 'lolos') {
    $currentStatus = $_GET['current_status'];
    $custom_message = $_GET['message'] ?? '';
    
    switch ($currentStatus) {
        case 'Menunggu Proses':
            $newStatus = 'Seleksi Administratif';
            if (empty($custom_message)) {
                $custom_message = "Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.";
            }
            break;

        case 'Seleksi Administratif':
            $newStatus = 'Seleksi Wawancara';
            if (empty($custom_message)) {
                $custom_message = "Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.";
            }
            break;

        case 'Seleksi Wawancara':
            // PERBAIKAN: Semua pelamar dari wawancara lanjut ke psikotes
            $newStatus = 'Seleksi Psikotes';
            if (empty($custom_message)) {
                $custom_message = "Selamat! Anda lolos tahap wawancara. Tahap selanjutnya adalah psikotes.";
            }
            break;

        case 'Seleksi Psikotes':
            $newStatus = 'Seleksi Kesehatan';
            if (empty($custom_message)) {
                $custom_message = "Selamat! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.";
            }
            break;

        case 'Seleksi Kesehatan':
            $newStatus = 'Diterima';
            if (empty($custom_message)) {
                $custom_message = "Selamat! Anda diterima sebagai karyawan tetap. Selamat bergabung!";
            }
            break;
    }
    
        
        if ($newStatus) {
            // Update status pelamar
            $stmt = $conn->prepare("UPDATE data_pelamar SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $id);
            $stmt->execute();
            
            // Simpan pengumuman
            $stmt = $conn->prepare("INSERT INTO pengumuman_pelamar (pelamar_id, tahap, pesan, tanggal) VALUES (?, ?, ?, CURDATE())");
            $stmt->bind_param("iss", $id, $currentStatus, $custom_message);
            $stmt->execute();
        }

    } elseif ($action == 'tidak') {
        $stmt = $conn->prepare("UPDATE data_pelamar SET status = 'Tidak Lolos' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Beri pengumuman tidak lolos
        $pesan_tidak_lolos = "Maaf, Anda tidak lolos pada tahap seleksi. Terima kasih telah berpartisipasi.";
        $stmt = $conn->prepare("INSERT INTO pengumuman_pelamar (pelamar_id, tahap, pesan, tanggal) VALUES (?, 'Tidak Lolos', ?, CURDATE())");
        $stmt->bind_param("is", $id, $pesan_tidak_lolos);
        $stmt->execute();
    }
    
    header("Location: administrasi_pelamar.php");
    exit;
}

// --- FUNGSI UNTUK MENGAMBIL DATA PELAMAR ---
function getApplicantsByStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT id, nama_lengkap, posisi_dilamar, no_telp, email FROM data_pelamar WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result();
}

// Ambil data pengumuman umum
$pengumuman_umum = $conn->query("SELECT * FROM pengumuman_umum WHERE status = 'active' ORDER BY tanggal DESC, id DESC");

$pelamarMenunggu = getApplicantsByStatus($conn, 'Menunggu Proses');
$pelamarAdministrasi = getApplicantsByStatus($conn, 'Seleksi Administratif');
$pelamarWawancara = getApplicantsByStatus($conn, 'Seleksi Wawancara');
$pelamarPsikotes = getApplicantsByStatus($conn, 'Seleksi Psikotes');
$pelamarKesehatan = getApplicantsByStatus($conn, 'Seleksi Kesehatan');
$pelamarDiterima = getApplicantsByStatus($conn, 'Diterima');
$pelamarTidakLolos = getApplicantsByStatus($conn, 'Tidak Lolos');

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administrasi Pelamar - Admin SDM</title>
<style>
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
    .btn-aksi { display: inline-block; padding: 6px 12px; margin: 0 2px; border-radius: 5px; text-decoration: none; color: #fff; font-weight: bold; border: none; font-size:14px; cursor:pointer; }
    .btn-lihat { background-color: #4a3f81; }
    .btn-lolos { background-color: #28a745; font-size:16px; padding: 4px 10px;}
    .btn-tidak { background-color: #d9534f; font-size:16px; padding: 4px 10px;}
    .section-title { font-size: 20px; font-weight: 600; color: #333; margin-top: 40px; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #4a3f81; }
    .no-data { text-align:center; padding: 20px; color: #777; }
    
    /* Modal styles untuk input pengumuman */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    .modal-content {
        background: white;
        margin: 50px auto;
        padding: 20px;
        width: 500px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
</style>
</head>
<body>

<!-- Modal untuk input pengumuman pelamar -->
<div id="modalPengumuman" class="modal-backdrop">
    <div class="modal-content">
        <h3 style="color: #1E105E; margin-bottom: 15px;">Buat Pengumuman</h3>
        
        <form id="formPengumuman" method="GET" action="administrasi_pelamar.php">
            <input type="hidden" name="action" value="lolos">
            <input type="hidden" id="modalPelamarId" name="id">
            <input type="hidden" id="modalCurrentStatus" name="current_status">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Pesan Pengumuman:</label>
                <textarea name="message" placeholder="Contoh: Selamat! Anda lolos tahap seleksi..." 
                          style="width: 100%; height: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                <small style="color: #666;">Pesan ini akan muncul di dashboard pelamar</small>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" onclick="tutupModal()" style="padding: 8px 15px; margin-right: 10px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Simpan & Loloskan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengumuman Umum -->
<div id="modalEditPengumuman" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <h3 style="color: #1E105E; margin-bottom: 15px;">Edit Pengumuman</h3>
        <form id="formEditPengumuman" method="POST">
            <input type="hidden" name="action_pengumuman" value="edit">
            <input type="hidden" id="editPengumumanId" name="id">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Judul:</label>
                    <input type="text" id="editJudul" name="judul" required 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Tanggal:</label>
                    <input type="date" id="editTanggal" name="tanggal" required 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Isi Pengumuman:</label>
                <textarea id="editIsi" name="isi" required 
                          style="width: 100%; height: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" onclick="tutupModalEdit()" style="padding: 8px 15px; margin-right: 10px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

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
          <ul>
                <li><a href="logout2.php">Logout</a></li>
            </ul>
        </li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, Cell!</h1>
    <p class="admin-title">Administrator</p>

    <!-- Data Pelamar -->
    <div class="card">
        <h2 class="page-title">Data Pelamar</h2>
        
        <div class="action-bar"> 
            <input type="search" placeholder="Cari pelamar..."> 
            <button class="btn-cari">Cari</button> 
            <button class="btn-hapus">Hapus</button> 
        </div>
        
        <!-- TAHAP 1: MENUNGGU PROSES -->
        <h3 class="section-title">⏳ Menunggu Proses</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pelamarMenunggu->num_rows > 0): ?>
                    <?php while($row = $pelamarMenunggu->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td>
                            <button onclick="bukaModal('<?= $row['id'] ?>', 'Menunggu Proses', '<?= $row['nama_lengkap'] ?>')" 
                                    class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TAHAP 2: SELEKSI ADMINISTRATIF -->
        <h3 class="section-title">📋 Seleksi Administratif</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pelamarAdministrasi->num_rows > 0): ?>
                    <?php while($row = $pelamarAdministrasi->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td>
                            <button onclick="bukaModal('<?= $row['id'] ?>', 'Seleksi Administratif', '<?= $row['nama_lengkap'] ?>')" 
                                    class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TAHAP 3: WAWANCARA -->
        <h3 class="section-title">💬 Seleksi Wawancara</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pelamarWawancara->num_rows > 0): ?>
                    <?php while($row = $pelamarWawancara->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td>
                            <button onclick="bukaModal('<?= $row['id'] ?>', 'Seleksi Wawancara', '<?= $row['nama_lengkap'] ?>')" 
                                    class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TAHAP 4: PSIKOTES -->
        <h3 class="section-title">🧠 Seleksi Psikotes</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                 <?php if ($pelamarPsikotes->num_rows > 0): ?>
                    <?php while($row = $pelamarPsikotes->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td>
                            <button onclick="bukaModal('<?= $row['id'] ?>', 'Seleksi Psikotes', '<?= $row['nama_lengkap'] ?>')" 
                                    class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TAHAP 5: KESEHATAN -->
        <h3 class="section-title">🏥 Seleksi Kesehatan</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                 <?php if ($pelamarKesehatan->num_rows > 0): ?>
                    <?php while($row = $pelamarKesehatan->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td>
                            <button onclick="bukaModal('<?= $row['id'] ?>', 'Seleksi Kesehatan', '<?= $row['nama_lengkap'] ?>')" 
                                    class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- PELAMAR DITERIMA -->
        <h3 class="section-title">🎉 Diterima</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                 <?php if ($pelamarDiterima->num_rows > 0): ?>
                    <?php while($row = $pelamarDiterima->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td><span style="color: #28a745; font-weight: bold;">DITERIMA</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar yang diterima.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- PELAMAR TIDAK LOLOS -->
        <h3 class="section-title">❌ Tidak Lolos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Divisi</th>
                    <th>Detail Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                 <?php if ($pelamarTidakLolos->num_rows > 0): ?>
                    <?php while($row = $pelamarTidakLolos->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td><span style="color: #dc3545; font-weight: bold;">TIDAK LOLOS</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar yang tidak lolos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// Fungsi untuk modal pelamar
// Fungsi untuk modal pelamar
function bukaModal(pelamarId, currentStatus, namaPelamar) {
    document.getElementById('modalPelamarId').value = pelamarId;
    document.getElementById('modalCurrentStatus').value = currentStatus;
    
    // Set pesan default berdasarkan tahap
    const textarea = document.querySelector('#formPengumuman textarea[name="message"]');
    let defaultMessage = "";
    
    switch(currentStatus) {
        case 'Menunggu Proses':
            defaultMessage = `Selamat ${namaPelamar}! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.`;
            break;
        case 'Seleksi Administratif':
            defaultMessage = `Selamat ${namaPelamar}! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.`;
            break;
        case 'Seleksi Wawancara':
            defaultMessage = `Selamat ${namaPelamar}! Anda lolos tahap wawancara. Tahap selanjutnya adalah psikotes.`;
            break;
        case 'Seleksi Psikotes':
            defaultMessage = `Selamat ${namaPelamar}! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.`;
            break;
        case 'Seleksi Kesehatan':
            defaultMessage = `Selamat ${namaPelamar}! Anda diterima sebagai karyawan tetap. Selamat bergabung!`;
            break;
    }
    
    textarea.value = defaultMessage;
    document.getElementById('modalPengumuman').style.display = 'block';
}
function tutupModal() {
    document.getElementById('modalPengumuman').style.display = 'none';
}

// Fungsi untuk modal edit pengumuman umum
function editPengumuman(id, judul, isi, tanggal) {
    document.getElementById('editPengumumanId').value = id;
    document.getElementById('editJudul').value = judul;
    document.getElementById('editIsi').value = isi;
    document.getElementById('editTanggal').value = tanggal;
    document.getElementById('modalEditPengumuman').style.display = 'block';
}

function tutupModalEdit() {
    document.getElementById('modalEditPengumuman').style.display = 'none';
}

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('modalPengumuman');
    const modalEdit = document.getElementById('modalEditPengumuman');
    
    if (event.target == modal) {
        tutupModal();
    }
    if (event.target == modalEdit) {
        tutupModalEdit();
    }
}
</script>
</body>
</html>