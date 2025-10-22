<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];

if (isset($_GET['message']) && isset($_GET['message_type'])) {
    $message = $_GET['message'];
    $message_type = $_GET['message_type'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['id_khl'])) {
        $id_khl = $_POST['id_khl'];
        $action = $_POST['action'];
        $alasan_penolakan = isset($_POST['alasan_penolakan']) ? trim($_POST['alasan_penolakan']) : '';

        $check_query = "SELECT * FROM data_pengajuan_khl WHERE id_khl = ? AND role != 'direktur'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $id_khl);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $khl_data = $check_result->fetch_assoc();

            if ($action == 'reject' && empty($alasan_penolakan)) {
                $message = "Harap berikan alasan penolakan";
                $message_type = "error";
            } else {
                if ($action == 'approve') {
                    $new_status = 'Disetujui';
                } else {
                    $new_status = 'Ditolak';
                }

                $update_query = "UPDATE data_pengajuan_khl SET status_khl = ?, alasan_penolakan = ? WHERE id_khl = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $new_status, $alasan_penolakan, $id_khl);

                if ($update_stmt->execute()) {
                    $pesan_sukses = $action == 'approve' ? "KHL berhasil disetujui" : "KHL berhasil ditolak";
                    header("Location: persetujuan_khl_direktur.php?message=$pesan_sukses&message_type=success");
                    exit();
                } else {
                    $message = "Gagal memperbarui status KHL";
                    $message_type = "error";
                }

                $update_stmt->close();
            }
        } else {
            $message = "Data KHL tidak ditemukan atau berasal dari direktur (otomatis disetujui)";
            $message_type = "error";
        }

        $check_stmt->close();
    }
}

$query = "SELECT * FROM data_pengajuan_khl WHERE role != 'direktur' AND status_khl = 'pending' ORDER BY id_khl DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Persetujuan KHL - Direktur</title>
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
    main { max-width:1600px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { text-align:left; font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    
    /* Container untuk tabel scrollable */
    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-top: 20px;
        border: 2px solid #34377c;
        border-radius: 8px;
        background: white;
    }
    
    .data-table { 
        width: 100%;
        min-width: 1300px; /* Diperkecil karena hapus 1 kolom */
        border-collapse: collapse; 
        font-size: 14px; 
    }
    
    .data-table th, .data-table td { 
        padding: 12px 15px; 
        text-align: left; 
        border: 1px solid #ddd;
        white-space: nowrap;
    }
    
    .data-table th { 
        background-color: #4a3f81; 
        font-weight: 600; 
        color: white;
        border-bottom: 2px solid #34377c;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .data-table td {
        background-color: white;
    }
    
    .data-table tbody tr:hover { 
        background-color: #f1f1f1; 
    }
    
    .data-table tbody tr:nth-child(even) td {
        background-color: #f8f9fa;
    }
    
    .data-table tbody tr:nth-child(even):hover td {
        background-color: #e9ecef;
    }
    
    /* Kolom dengan teks panjang bisa wrap */
    .data-table td.proyek-cell,
    .data-table td.alasan-penolakan-cell {
        white-space: normal;
        min-width: 200px;
        max-width: 300px;
        word-wrap: break-word;
    }
    
    /* Styling untuk status */
    .status-pending { color: #ffc107; font-weight: 600; }
    .status-disetujui { color: #28a745; font-weight: 600; }
    .status-ditolak { color: #d9534f; font-weight: 600; }
    
    /* Styling untuk tombol aksi */
    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        color: #fff;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s;
        margin: 2px;
    }
    
    .btn-approve {
        background: #28a745;
    }
    
    .btn-approve:hover {
        background: #218838;
    }
    
    .btn-reject {
        background: #dc3545;
    }
    
    .btn-reject:hover {
        background: #c82333;
    }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    /* Styling untuk kolom proyek */
    .proyek-cell {
        max-width: 300px;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    /* Styling untuk alasan penolakan */
    .alasan-penolakan-cell { 
        color: #d9534f; 
        font-style: italic;
        font-size: 13px;
        max-width: 300px;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    /* Message styling */
    .message {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid transparent;
    }
    
    .success {
        background: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }
    
    .error {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    
    /* Scrollbar styling */
    .table-container::-webkit-scrollbar {
        height: 12px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 0 0 6px 6px;
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: #4a3f81;
        border-radius: 6px;
    }
    
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #3a3162;
    }
    
    /* Indikator scroll */
    .scroll-indicator {
        text-align: center;
        color: #666;
        font-size: 12px;
        margin-top: 5px;
        font-style: italic;
    }
    
    /* Modal styling */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
    }
    
    .modal-content {
        background: #fff;
        color: #333;
        width: 90%;
        max-width: 500px;
        margin: 15% auto;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .modal h3 {
        color: #1E105E;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }
    
    .modal label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }
    
    .modal textarea {
        width: 100%;
        height: 120px;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        resize: vertical;
        font-family: inherit;
        font-size: 14px;
    }
    
    .modal textarea:focus {
        outline: none;
        border-color: #4a3f81;
    }
    
    .modal-actions {
        margin-top: 20px;
        text-align: right;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        cursor: pointer;
        transition: opacity 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-cancel {
        background: #6c757d;
    }
    
    .btn-cancel:hover {
        background: #545b62;
    }
    
    .btn-reject-modal {
        background: #dc3545;
    }
    
    .btn-reject-modal:hover {
        background: #c82333;
    }
    
    @media (max-width: 768px) { 
        .data-table { font-size: 12px; } 
        .data-table th, .data-table td { padding: 8px 10px; }
        .card { padding: 20px; }
        main { max-width: 1400px; }
        
        .data-table {
            min-width: 1100px; /* Diperkecil untuk mobile */
        }
        
        .table-container::-webkit-scrollbar {
            height: 14px;
        }
        
        .modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }
    
    @media (max-width: 480px) {
        .data-table {
            min-width: 900px; /* Diperkecil untuk mobile kecil */
        }
        
        .data-table th, .data-table td {
            padding: 6px 8px;
            font-size: 11px;
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
                    <li><a href="riwayat_pelamar_direktur.php">Riwayat Pelamar</a></li>
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
    <h1>Welcome, <?php echo htmlspecialchars($nama_direktur); ?>!</h1>
    <p class="admin-title">Direktur</p>

    <div class="card"> 
        <h2 class="page-title">Persetujuan Kehadiran Harian Lepas (KHL)</h2> 
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Container untuk tabel scrollable -->
        <div class="table-container">
            <table class="data-table"> 
                <thead> 
                    <tr> 
                        <th>No</th> 
                        <th>Kode Karyawan</th> 
                        <th>Divisi</th> 
                        <th>Jabatan</th> 
                        <th>Role</th> 
                        <th>Proyek</th> 
                        <th>Tanggal KHL</th> 
                        <th>Jam Kerja</th> 
                        <th>Tanggal Cuti KHL</th> 
                        <th>Jam Cuti</th> 
                        <th>Status</th>
                        <th>Alasan Penolakan</th>
                        <th>Aksi</th>
                    </tr> 
                </thead> 
                <tbody> 
                    <?php if ($result->num_rows > 0): ?> 
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?> 
                            <tr> 
                                <td style="text-align: center;"><?= $no++ ?></td> 
                                <td><?= htmlspecialchars($row['kode_karyawan'] ?? '') ?></td> 
                                <td><?= htmlspecialchars($row['divisi'] ?? '') ?></td> 
                                <td><?= htmlspecialchars($row['jabatan'] ?? '') ?></td> 
                                <td><?= htmlspecialchars(ucfirst($row['role'] ?? '')) ?></td> 
                                <td class="proyek-cell"><?= htmlspecialchars($row['proyek'] ?? '') ?></td> 
                                <td><?= htmlspecialchars($row['tanggal_khl'] ?? '') ?></td> 
                                <td><?= htmlspecialchars(($row['jam_mulai_kerja'] ?? '') . ' - ' . ($row['jam_akhir_kerja'] ?? '')) ?></td> 
                                <td><?= htmlspecialchars($row['tanggal_cuti_khl'] ?? '') ?></td> 
                                <td><?= htmlspecialchars(($row['jam_mulai_cuti_khl'] ?? '') . ' - ' . ($row['jam_akhir_cuti_khl'] ?? '')) ?></td> 
                                <td style="text-align: center;">
                                    <span class="status-pending"><?= htmlspecialchars($row['status_khl'] ?? '') ?></span>
                                </td>
                                <td class="alasan-penolakan-cell">
                                    <?= !empty($row['alasan_penolakan']) ? htmlspecialchars($row['alasan_penolakan']) : '<span style="color:#999;">-</span>' ?>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_khl" value="<?= $row['id_khl'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-action btn-approve" onclick="return confirm('Setujui data KHL ini?')">Setujui</button>
                                    </form>
                                    <button class="btn-action btn-reject" onclick="openRejectModal(<?= $row['id_khl'] ?>)">Tolak</button>
                                </td>
                            </tr> 
                        <?php endwhile; ?> 
                    <?php else: ?> 
                        <tr><td colspan="13" class="no-data">Tidak ada pengajuan KHL yang menunggu persetujuan.</td></tr> 
                    <?php endif; ?> 
                </tbody> 
            </table> 
        </div>
        
        <div class="scroll-indicator">
            ← Geser untuk melihat lebih banyak data →
        </div>
    </div>
</main>

<!-- Modal untuk penolakan KHL -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3>Alasan Penolakan KHL</h3>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="id_khl" id="modalKhlId">
            <input type="hidden" name="action" value="reject">
            <label for="alasan_penolakan">Alasan Penolakan:</label>
            <textarea name="alasan_penolakan" id="alasan_penolakan" 
                     placeholder="Masukkan alasan penolakan KHL..." required></textarea>
            <div class="modal-actions">
                <button type="button" onclick="closeRejectModal()" class="btn btn-cancel">Batal</button>
                <button type="submit" class="btn btn-reject-modal">Tolak KHL</button>
            </div>
        </form>
    </div>
</div>

<script>
// Script untuk menangani scroll horizontal dengan keyboard
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.querySelector('.table-container');
    
    // Fungsi untuk scroll dengan keyboard
    tableContainer.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            tableContainer.scrollLeft -= 100;
            e.preventDefault();
        } else if (e.key === 'ArrowRight') {
            tableContainer.scrollLeft += 100;
            e.preventDefault();
        }
    });
    
    // Fokus pada container tabel agar bisa di-scroll dengan keyboard
    tableContainer.setAttribute('tabindex', '0');
});

// Modal functions
function openRejectModal(id) {
    document.getElementById('modalKhlId').value = id;
    document.getElementById('rejectModal').style.display = 'block';
    document.getElementById('alasan_penolakan').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('alasan_penolakan').value = '';
}

window.onclick = function(e) {
    const modal = document.getElementById('rejectModal');
    if (e.target === modal) {
        closeRejectModal();
    }
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    const alasan = document.getElementById('alasan_penolakan').value.trim();
    if (!alasan) {
        e.preventDefault();
        alert('Harap berikan alasan penolakan!');
        document.getElementById('alasan_penolakan').focus();
        return false;
    }
    return confirm('Apakah Anda yakin ingin menolak data KHL ini?');
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>