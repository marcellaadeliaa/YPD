<?php
session_start();
require 'config.php'; 

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['karyawan_id'])) {
    $karyawan_id = $_POST['karyawan_id'];
    $sisa_tahunan = (int)$_POST['sisa_cuti_tahunan'];
    $sisa_lustrum = (int)$_POST['sisa_cuti_lustrum'];

    $stmt_update = $conn->prepare("UPDATE data_karyawan SET sisa_cuti_tahunan = ?, sisa_cuti_lustrum = ? WHERE id_karyawan = ?");
    $stmt_update->bind_param("iii", $sisa_tahunan, $sisa_lustrum, $karyawan_id);
    
    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = "Sisa cuti berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui sisa cuti.";
    }
    $stmt_update->close();
    
    header("Location: daftar_sisa_cuti.php");
    exit();
}

$search_query = $_GET['search'] ?? '';

$sql = "SELECT id_karyawan, kode_karyawan, nama_lengkap, divisi, role, sisa_cuti_tahunan, sisa_cuti_lustrum FROM data_karyawan";

if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";
    $sql .= " WHERE nama_lengkap LIKE ? OR kode_karyawan LIKE ? OR divisi LIKE ? OR role LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$filtered_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Sisa Cuti Karyawan - Admin SDM</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 130px; height: 50px; object-fit: contain; }
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
    
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
    .filter-row { display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .filter-group.search-group { flex-grow: 1; min-width: 300px; }
    
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; }
    .btn-cari { background-color: #4a3f81; }
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; color: #333; position: sticky; top: 0; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    
    .sisa-banyak { color: #28a745; font-weight: 600; }
    .sisa-sedikit { color: #ffc107; font-weight: 600; }
    .sisa-habis { color: #dc3545; font-weight: 600; }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    .filter-info { 
        background: #e7f3ff; 
        padding: 10px 15px; 
        border-radius: 6px; 
        margin-bottom: 15px; 
        font-size: 14px; 
        border-left: 4px solid #4a3f81;
    }
    
    .action-cell { display: flex; gap: 5px; }
    .btn-action { padding: 6px 12px; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; color: #fff; cursor: pointer; }
    .btn-edit { background-color: #17a2b8; }
    .btn-edit:hover { background-color: #138496; }
    
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 5% auto; padding: 30px; border-radius: 10px; width: 80%; max-width: 500px; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover { color: #000; }
    
    .modal-title { font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #1E105E; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #333; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    
    .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4a3f81; }
    .info-box h4 { margin: 0 0 10px 0; color: #1E105E; }
    .info-box p { margin: 5px 0; font-size: 14px; }
    
    @media (max-width: 768px) {
        .filter-row { flex-direction: column; }
        .filter-group { width: 100%; }
        .action-bar { flex-direction: column; }
        .btn { width: 100%; }
        .action-cell { flex-direction: column; }
        .data-table { font-size: 12px; }
        .data-table th, .data-table td { padding: 8px 10px; }
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
          <ul>
                <li><a href="logout2.php">Logout</a></li>
            </ul>
        </li>
        </ul>
    </nav>
</header>

<main>
    <div class="card">
        <h2 class="page-title">Daftar Sisa Cuti Karyawan</h2>
        
        <div class="info-box">
            <h4>📋 Informasi Kuota Cuti</h4>
            <p><strong>Cuti Tahunan:</strong> Kuota awal 12 hari per tahun (Tidak untuk Karyawan Baru).</p>
            <p><strong>Cuti Lustrum:</strong> Kuota bervariasi per karyawan.</p>
            <p>Untuk karyawan baru, belum mendapat jatah untuk cuti tahunan dan lustrum.</p>
            <p>Anda dapat menyesuaikan sisa cuti karyawan melalui tombol "Edit Sisa Cuti".</p>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="daftar_sisa_cuti.php">
                <div class="filter-row">
                    <div class="filter-group search-group">
                        <label for="search">Cari Karyawan (Nama/Kode/Divisi/Role)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan nama, kode, divisi, atau role..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Cari</button>
                    <a href="daftar_sisa_cuti.php" class="btn btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <?php if (!empty($search_query)): ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong> Pencarian: '<?= htmlspecialchars($search_query) ?>'
                <span style="float: right; color: #666;">
                    Data ditemukan: <?= count($filtered_data) ?>
                </span>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Kode</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Role</th>
                    <th>Sisa Cuti Tahunan</th>
                    <th>Sisa Cuti Lustrum</th>
                    <th>Total Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_data)): ?>
                    <?php foreach($filtered_data as $karyawan): 
                        $total_sisa = $karyawan['sisa_cuti_tahunan'] + $karyawan['sisa_cuti_lustrum'];
                        
                        if ($total_sisa >= 8) {
                            $status_class = 'sisa-banyak';
                            $status_text = 'Banyak';
                        } elseif ($total_sisa >= 3) {
                            $status_class = 'sisa-sedikit';
                            $status_text = 'Sedikit';
                        } else {
                            $status_class = 'sisa-habis';
                            $status_text = 'Habis';
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($karyawan['kode_karyawan']) ?></td>
                        <td><?= htmlspecialchars($karyawan['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($karyawan['divisi']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($karyawan['role'])) ?></td>
                        <td><?= htmlspecialchars($karyawan['sisa_cuti_tahunan']) ?> hari</td>
                        <td><?= htmlspecialchars($karyawan['sisa_cuti_lustrum']) ?> hari</td>
                        <td>
                            <strong class="<?= $status_class ?>"><?= $total_sisa ?> hari</strong>
                        </td>
                        <td>
                            <span class="<?= $status_class ?>"><?= $status_text ?></span>
                        </td>
                        <td class="action-cell">
                            <button class="btn-action btn-edit" onclick='editSisaCuti(<?= json_encode($karyawan) ?>)'>Edit Sisa Cuti</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="no-data">Tidak ada data karyawan yang ditemukan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="editCutiModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editCutiModal')">&times;</span>
        <div class="modal-title">Edit Sisa Cuti</div>
        <form id="editCutiForm" method="POST" action="daftar_sisa_cuti.php">
            <input type="hidden" id="editKaryawanId" name="karyawan_id">
            <div class="form-group">
                <label for="editNamaKaryawan">Nama Karyawan</label>
                <input type="text" id="editNamaKaryawan" readonly style="background-color: #e9ecef;">
            </div>
            <div class="form-group">
                <label for="editCutiTahunan">Sisa Cuti Tahunan</label>
                <input type="number" id="editCutiTahunan" name="sisa_cuti_tahunan" min="0" max="100" required>
            </div>
            <div class="form-group">
                <label for="editCutiLustrum">Sisa Cuti Lustrum</label>
                <input type="number" id="editCutiLustrum" name="sisa_cuti_lustrum" min="0" max="100" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-reset" onclick="closeModal('editCutiModal')">Batal</button>
                <button type="submit" class="btn btn-cari">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
    
    function editSisaCuti(karyawan) {
        document.getElementById('editKaryawanId').value = karyawan.id_karyawan;
        document.getElementById('editNamaKaryawan').value = karyawan.nama_lengkap;
        document.getElementById('editCutiTahunan').value = karyawan.sisa_cuti_tahunan;
        document.getElementById('editCutiLustrum').value = karyawan.sisa_cuti_lustrum;
        openModal('editCutiModal');
    }
</script>

</body>
</html>