<?php
session_start();
require 'config.php';

$search_keyword = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_keyword = trim($_GET['search']);
}

if (isset($_POST['action_pengumuman'])) {
    header("Location: administrasi_pelamar.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    $newStatus = '';
    $currentStatus = $_GET['current_status'] ?? '';
    $custom_message = $_GET['message'] ?? '';
    
    $status_admin = NULL;
    $status_wawancara = NULL;
    $status_psikotes = NULL;
    $status_kesehatan = NULL;
    $status_final = NULL;
    
    if ($action == 'lolos') {
        $next_stage = $_GET['next_stage'] ?? '';

        switch ($currentStatus) {
            case 'Menunggu Proses':
                $newStatus = 'Seleksi Administratif';
                $status_admin = 'Lolos'; 
                if (empty($custom_message)) $custom_message = "Selamat! Lamaran Anda telah diterima dan masuk ke tahap seleksi administratif.";
                break;
                
            case 'Seleksi Administratif':
                $newStatus = 'Seleksi Wawancara';
                $status_admin = 'Lolos'; 
                $status_wawancara = 'Lolos'; 
                if (empty($custom_message)) $custom_message = "Selamat! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.";
                break;
                
            case 'Seleksi Wawancara':
                $status_admin = 'Lolos'; 
                $status_wawancara = 'Lolos'; 

                if ($next_stage == 'psikotes') {
                    $newStatus = 'Seleksi Psikotes';
                    $status_psikotes = 'Lolos'; 
                    if (empty($custom_message)) $custom_message = "Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes.";
                } elseif ($next_stage == 'kesehatan') {
                    $newStatus = 'Seleksi Kesehatan';
                    $status_kesehatan = 'Lolos'; 
                    if (empty($custom_message)) $custom_message = "Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Tes Kesehatan.";
                } elseif ($next_stage == 'keduanya') {
                    $newStatus = 'Seleksi Psikotes & Kesehatan';
                    $status_psikotes = 'Lolos'; 
                    if (empty($custom_message)) $custom_message = "Selamat! Anda lolos wawancara. Tahap selanjutnya adalah Psikotes dan Tes Kesehatan.";
                }
                break;
                
            case 'Seleksi Psikotes':
                $newStatus = 'Diterima';
                $status_admin = 'Lolos'; 
                $status_wawancara = 'Lolos'; 
                $status_psikotes = 'Lolos';
                $status_final = 'Diterima'; 
                if (empty($custom_message)) $custom_message = "Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.";
                break;
                
            case 'Seleksi Psikotes & Kesehatan':
                $newStatus = 'Seleksi Kesehatan';
                $status_admin = 'Lolos'; 
                $status_wawancara = 'Lolos';
                $status_psikotes = 'Lolos'; 
                $status_kesehatan = 'Lolos'; 
                if (empty($custom_message)) $custom_message = "Selamat! Anda lolos Psikotes. Tahap selanjutnya adalah Tes Kesehatan.";
                break;
                
            case 'Seleksi Kesehatan':
                $newStatus = 'Diterima';
                $status_admin = 'Lolos';
                $status_wawancara = 'Lolos';
                $status_psikotes = 'Lolos';
                $status_kesehatan = 'Lolos'; 
                $status_final = 'Diterima';
                if (empty($custom_message)) $custom_message = "Selamat! Anda telah lolos seluruh rangkaian seleksi dan dinyatakan DITERIMA.";
                break;
        }
        
        if ($newStatus) {
            $stmt = $conn->prepare("UPDATE data_pelamar SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $id);
            $stmt->execute();
            
            $stmt_riwayat = $conn->prepare("INSERT INTO riwayat_pelamar 
                (pelamar_id, status_administratif, status_wawancara, status_psikotes, status_kesehatan, status_final) 
                VALUES (?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                status_administratif = COALESCE(VALUES(status_administratif), status_administratif),
                status_wawancara = COALESCE(VALUES(status_wawancara), status_wawancara),
                status_psikotes = COALESCE(VALUES(status_psikotes), status_psikotes),
                status_kesehatan = COALESCE(VALUES(status_kesehatan), status_kesehatan),
                status_final = COALESCE(VALUES(status_final), status_final)");
            
            $stmt_riwayat->bind_param("isssss", $id, $status_admin, $status_wawancara, $status_psikotes, $status_kesehatan, $status_final);
            $stmt_riwayat->execute();
            
            $stmt_pengumuman = $conn->prepare("INSERT INTO pengumuman_pelamar (pelamar_id, tahap, pesan, tanggal) VALUES (?, ?, ?, CURDATE())");
            $stmt_pengumuman->bind_param("iss", $id, $currentStatus, $custom_message);
            $stmt_pengumuman->execute();
        }

    } elseif ($action == 'tidak') {
        $newStatus = 'Tidak Lolos';
        
        switch($currentStatus) {
            case 'Menunggu Proses':
            case 'Seleksi Administratif':
                $status_admin = 'Tidak Lolos';
                break;
            case 'Seleksi Wawancara':
                $status_wawancara = 'Tidak Lolos';
                break;
            case 'Seleksi Psikotes':
                $status_psikotes = 'Tidak Lolos';
                break;
            case 'Seleksi Psikotes & Kesehatan':
                $status_psikotes = 'Tidak Lolos';
                break;
            case 'Seleksi Kesehatan':
                $status_kesehatan = 'Tidak Lolos';
                break;
        }
        $status_final = 'Tidak Lolos';
        
        $stmt = $conn->prepare("UPDATE data_pelamar SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $id);
        $stmt->execute();
        
        $stmt_riwayat = $conn->prepare("INSERT INTO riwayat_pelamar 
            (pelamar_id, status_administratif, status_wawancara, status_psikotes, status_kesehatan, status_final) 
            VALUES (?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            status_administratif = COALESCE(VALUES(status_administratif), status_administratif),
            status_wawancara = COALESCE(VALUES(status_wawancara), status_wawancara),
            status_psikotes = COALESCE(VALUES(status_psikotes), status_psikotes),
            status_kesehatan = COALESCE(VALUES(status_kesehatan), status_kesehatan),
            status_final = COALESCE(VALUES(status_final), status_final)");
        
        $stmt_riwayat->bind_param("isssss", $id, $status_admin, $status_wawancara, $status_psikotes, $status_kesehatan, $status_final);
        $stmt_riwayat->execute();

        $pesan_tidak_lolos = "Maaf, Anda tidak lolos pada tahap seleksi ini. Terima kasih telah berpartisipasi.";
        
        $stmt_pengumuman = $conn->prepare("INSERT INTO pengumuman_pelamar (pelamar_id, tahap, pesan, tanggal) VALUES (?, ?, ?, CURDATE())");
        $stmt_pengumuman->bind_param("iss", $id, $currentStatus, $pesan_tidak_lolos);
        $stmt_pengumuman->execute();
    }
    
    header("Location: administrasi_pelamar.php");
    exit;
}

function getApplicantsByStatus($conn, $status, $search_keyword = '') {
    if (!empty($search_keyword)) {
        $sql = "SELECT id, nama_lengkap, posisi_dilamar, no_telp, email, status 
                FROM data_pelamar 
                WHERE status = ? AND (nama_lengkap LIKE ? OR posisi_dilamar LIKE ?)";
        $stmt = $conn->prepare($sql);
        $search_term = "%$search_keyword%";
        $stmt->bind_param("sss", $status, $search_term, $search_term);
    } else {
        $sql = "SELECT id, nama_lengkap, posisi_dilamar, no_telp, email, status 
                FROM data_pelamar 
                WHERE status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $status);
    }
    $stmt->execute();
    return $stmt->get_result();
}

$pengumuman_umum = $conn->query("SELECT * FROM pengumuman_umum WHERE status = 'active' ORDER BY tanggal DESC, id DESC");

$pelamarMenunggu = getApplicantsByStatus($conn, 'Menunggu Proses', $search_keyword);
$pelamarAdministrasi = getApplicantsByStatus($conn, 'Seleksi Administratif', $search_keyword);
$pelamarWawancara = getApplicantsByStatus($conn, 'Seleksi Wawancara', $search_keyword);
$pelamarPsikotes = getApplicantsByStatus($conn, 'Seleksi Psikotes', $search_keyword);
$pelamarKesehatan = getApplicantsByStatus($conn, 'Seleksi Kesehatan', $search_keyword);
$pelamarPsikotesKesehatan = getApplicantsByStatus($conn, 'Seleksi Psikotes & Kesehatan', $search_keyword);
$pelamarDiterima = getApplicantsByStatus($conn, 'Diterima', $search_keyword);
$pelamarTidakLolos = getApplicantsByStatus($conn, 'Tidak Lolos', $search_keyword);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrasi Pelamar</title>
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
        .btn-aksi { display: inline-block; padding: 6px 12px; margin: 0 2px; border-radius: 5px; text-decoration: none; color: #fff; font-weight: bold; border: none; font-size:14px; cursor:pointer; }
        .btn-lihat { background-color: #4a3f81; }
        .btn-lolos { background-color: #28a745; font-size:16px; padding: 4px 10px;}
        .btn-tidak { background-color: #d9534f; font-size:16px; padding: 4px 10px;}
        .section-title { font-size: 20px; font-weight: 600; color: #333; margin-top: 40px; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #4a3f81; }
        .no-data { text-align:center; padding: 20px; color: #777; }
        .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 50px auto; padding: 20px; width: 500px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div id="modalPengumuman" class="modal-backdrop">
    <div class="modal-content">
        <h3 style="color: #1E105E; margin-bottom: 15px;">Buat Pengumuman Kelolosan</h3>
        
        <form id="formPengumuman" method="GET" action="administrasi_pelamar.php">
            <input type="hidden" name="action" value="lolos">
            <input type="hidden" id="modalPelamarId" name="id">
            <input type="hidden" id="modalCurrentStatus" name="current_status">
            
            <div id="pilihanTahapSelanjutnya" style="display: none; margin-bottom: 15px; background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600;">Pilih Tahap Selanjutnya:</label>
                <div>
                    <input type="radio" id="stage_psikotes" name="next_stage" value="psikotes" checked>
                    <label for="stage_psikotes">Hanya Psikotes</label>
                </div>
                <div style="margin-top: 5px;">
                    <input type="radio" id="stage_kesehatan" name="next_stage" value="kesehatan">
                    <label for="stage_kesehatan">Hanya Tes Kesehatan</label>
                </div>
                <div style="margin-top: 5px;">
                    <input type="radio" id="stage_keduanya" name="next_stage" value="keduanya">
                    <label for="stage_keduanya">Psikotes & Tes Kesehatan</label>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Pesan Pengumuman:</label>
                <textarea name="message" placeholder="Contoh: Selamat! Anda lolos tahap seleksi..." 
                          style="width: 100%; height: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                <small style="color: #666;">Pesan ini akan muncul di dashboard pelamar. Boleh dikosongkan untuk pesan default.</small>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" onclick="tutupModal()" style="padding: 8px 15px; margin-right: 10px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Simpan & Loloskan</button>
            </div>
        </form>
    </div>
</div>

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
    <div class="card">
        <h2 class="page-title">Data Pelamar</h2>
        
        <div class="action-bar">
            <form method="GET" action="" style="display: flex; gap: 10px; flex-grow: 1; align-items: center;">
                <input type="search" name="search" placeholder="Cari pelamar..." 
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
                    style="flex-grow: 1; padding: 10px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px;">
                <button type="submit" class="btn-cari">Cari</button>
                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="administrasi_pelamar.php" style="padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 16px;">Reset</a>
                <?php endif; ?>
            </form>
            <button class="btn-hapus">Hapus</button>
        </div>

        <?php if(!empty($search_keyword)): ?>
            <div style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-radius: 5px;">
                Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search_keyword) ?>"</strong>
            </div>
        <?php endif; ?>
        
        <h3 class="section-title">⏳ Menunggu Proses</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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
                            <button onclick="bukaModal('<?= $row['id'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 class="section-title">📋 Seleksi Administratif</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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
                            <button onclick="bukaModal('<?= $row['id'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 class="section-title">💬 Seleksi Wawancara</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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
                            <button onclick="bukaModal('<?= $row['id'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 class="section-title">🧠 Seleksi Psikotes Saja</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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
                            <button onclick="bukaModal('<?= $row['id'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 class="section-title">🧠+🏥 Seleksi Psikotes (Selanjutnya Seleksi Kesehatan)</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
                    <th>Detail Data</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pelamarPsikotesKesehatan->num_rows > 0): ?>
                    <?php while($row = $pelamarPsikotesKesehatan->fetch_assoc()): ?>
                    <tr>
                        <td class="text-left"><?= $row['id'] ?></td>
                        <td class="text-left"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_dilamar']) ?></td>
                        <td><a href="detail_pelamar.php?id=<?= $row['id'] ?>" class="btn-aksi btn-lihat">Lihat Data</a></td>
                        <td>
                             <button onclick="bukaModal('<?= $row['id'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 class="section-title">🏥 Seleksi Kesehatan</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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
                            <button onclick="bukaModal('<?= $row['id'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="btn-aksi btn-lolos">Lolos</button>
                            <a href="?action=tidak&id=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn-aksi btn-tidak">Tidak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">Tidak ada pelamar pada tahap ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 class="section-title">🎉 Diterima</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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

        <h3 class="section-title">❌ Tidak Lolos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelamar</th>
                    <th>Posisi yang Dilamar</th>
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
function bukaModal(pelamarId, currentStatus, namaPelamar) {
    document.getElementById('modalPelamarId').value = pelamarId;
    document.getElementById('modalCurrentStatus').value = currentStatus;
    
    const pilihanTahapDiv = document.getElementById('pilihanTahapSelanjutnya');
    const textarea = document.querySelector('#formPengumuman textarea[name="message"]');
    
    pilihanTahapDiv.style.display = 'none';

    let defaultMessage = "";
    
    switch(currentStatus) {
        case 'Menunggu Proses':
            defaultMessage = `Selamat ${namaPelamar}! Lamaran Anda diterima dan masuk ke tahap seleksi administratif.`;
            break;
        case 'Seleksi Administratif':
            defaultMessage = `Selamat ${namaPelamar}! Anda lolos seleksi administratif. Tahap selanjutnya adalah wawancara.`;
            break;
        case 'Seleksi Wawancara':
            pilihanTahapDiv.style.display = 'block'; 
            defaultMessage = `Selamat ${namaPelamar}! Anda lolos tahap wawancara. Silakan lanjutkan ke tahap berikutnya.`;
            break;
        case 'Seleksi Psikotes':
            defaultMessage = `Selamat ${namaPelamar}! Anda lolos psikotes. Tahap selanjutnya adalah pemeriksaan kesehatan.`;
            break;
        case 'Seleksi Psikotes & Kesehatan':
            defaultMessage = `Selamat ${namaPelamar}! Anda telah lolos tahap Psikotes. Tahap selanjutnya adalah Tes Kesehatan.`;
            break;
        case 'Seleksi Kesehatan':
            defaultMessage = `Selamat ${namaPelamar}! Anda diterima sebagai karyawan tetap. Selamat bergabung!`;
            break;
    }
    
    textarea.value = "";
    textarea.placeholder = defaultMessage; 
    document.getElementById('modalPengumuman').style.display = 'block';
}

function tutupModal() {
    document.getElementById('modalPengumuman').style.display = 'none';
}

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