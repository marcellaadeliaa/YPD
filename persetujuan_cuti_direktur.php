<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_direktur.php");
    exit();
}

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];

if (isset($_GET['message']) && isset($_GET['message_type'])) {
    $message = $_GET['message'];
    $message_type = $_GET['message_type'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['id_cuti'])) {
        $id_cuti = $_POST['id_cuti'];
        $action = $_POST['action'];
        $alasan = isset($_POST['alasan']) ? trim($_POST['alasan']) : null;
        
        $check_query = "SELECT id FROM data_pengajuan_cuti WHERE id = ? AND role != 'direktur' AND status = 'Menunggu Persetujuan'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $id_cuti);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            if ($action == 'approve') {
                $new_status = 'Disetujui';
                $alasan = null; 
                $message_success = "Cuti berhasil disetujui";
            } elseif ($action == 'reject') {
                if (empty($alasan)) {
                    header("Location: persetujuan_cuti_direktur.php?message=Harap berikan alasan penolakan&message_type=error");
                    exit();
                }
                $new_status = 'Ditolak';
                $message_success = "Cuti berhasil ditolak";
            } else {
                $message = "Tindakan tidak valid.";
                $message_type = "error";
            }

            if (isset($new_status)) {
                $update_query = "UPDATE data_pengajuan_cuti SET status = ?, alasan = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                
                if ($update_stmt === false) {
                    $message = "Gagal mempersiapkan statement update: " . htmlspecialchars($conn->error);
                    $message_type = "error";
                } else {
                    $update_stmt->bind_param("ssi", $new_status, $alasan, $id_cuti);
                    
                    if ($update_stmt->execute()) {
                        header("Location: persetujuan_cuti_direktur.php?message=" . urlencode($message_success) . "&message_type=success");
                        exit();
                    } else {
                        $message = "Gagal memperbarui status cuti: " . htmlspecialchars($update_stmt->error);
                        $message_type = "error";
                    }
                    $update_stmt->close();
                }
            }
        } else {
            $message = "Data pengajuan cuti tidak ditemukan atau sudah diproses.";
            $message_type = "error";
        }
        
        $check_stmt->close();
    }
}



$query = "SELECT * FROM data_pengajuan_cuti WHERE role != 'direktur' AND status = 'Menunggu Persetujuan' ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Cuti - Direktur</title>
    <style>
        :root { 
            --primary-color: #1E105E; 
            --secondary-color: #8897AE; 
            --accent-color: #4a3f81; 
            --card-bg: #FFFFFF; 
            --text-color-light: #fff; 
            --text-color-dark: #2e1f4f; 
            --shadow-light: rgba(0,0,0,0.15); 
        }
        
        body { 
            margin: 0; 
            font-family: 'Segoe UI', sans-serif; 
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh; 
            color: var(--text-color-light); 
            padding-bottom: 40px; 
        }
        
        header { 
            background: var(--card-bg); 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 15px var(--shadow-light); 
        }
        
        .logo { 
            display: flex; 
            align-items: center; 
            gap: 16px; 
            font-weight: 500; 
            font-size: 20px; 
            color: var(--text-color-dark); 
        }
        
        .logo img { 
            width: 50px; 
            height: 50px; 
            object-fit: contain; 
            border-radius: 50%; 
        }
        
        nav ul { 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            gap: 40px; 
        }
        
        nav li { 
            position: relative; 
        }
        
        nav a { 
            text-decoration: none; 
            color: var(--text-color-dark); 
            font-weight: 600; 
            padding: 8px 4px; 
            display: block; 
        }
        
        nav li ul { 
            display: none; 
            position: absolute; 
            top: 100%; 
            left: 0; 
            background: var(--card-bg); 
            padding: 15px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px var(--shadow-light); 
            min-width: 220px; 
            z-index: 999; 
        }
        
        nav li:hover > ul { 
            display: block; 
        }
        
        nav li ul li { 
            margin-bottom: 7px; 
            padding: 0; 
        }

        nav li ul li:last-child {
            margin-bottom: 0; 
        }
        
        nav li ul li a { 
            color: var(--text-color-dark); 
            font-weight: 400; 
            white-space: nowrap; 
            padding: 10px 25px; 
        }
        
        main { 
            max-width: 1400px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        
        .heading-section h1 { 
            font-size: 2.5rem; 
            margin: 0; 
            color: #fff;
        }
        
        .heading-section p { 
            font-size: 1.1rem; 
            margin-top: 5px; 
            opacity: 0.9; 
            margin-bottom: 30px; 
            color: #fff;
        }
        
        .container {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px var(--shadow-light);
            margin-top: 20px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px var(--shadow-light);
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-pending {
            color: #ff9800;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-approve {
            background-color: #4caf50;
            color: white;
        }
        
        .btn-reject {
            background-color: #f44336;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1rem;
        }
        
        .info-divisi {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
            color: var(--text-color-dark);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            background: #f0f0f0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .role-badge {
            background: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .role-karyawan {
            background: #d4edda;
            color: #155724;
        }
        
        .role-penanggung-jawab {
            background: #cce7ff;
            color: #004085;
        }
        
        .cuti-details {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 0.85rem;
        }
        
        .cuti-details strong {
            color: var(--primary-color);
        }

        .time-info {
            background: #e7f3ff;
            padding: 8px 12px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 0.8rem;
            border-left: 3px solid #2196F3;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--text-color-dark);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color-dark);
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            box-sizing: border-box;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-submit {
            background-color: #f44336;
            color: white;
        }

        .jenis-cuti-info {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .file-info {
            background: #fff3cd;
            padding: 8px 12px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 0.8rem;
            border-left: 3px solid #ffc107;
        }

        .file-link {
            color: #007bff;
            text-decoration: none;
        }

        .file-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
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
    <div class="heading-section">
        <h1>Persetujuan Cuti Karyawan</h1>
        <p>Kelola pengajuan cuti dari semua karyawan</p>
    </div>
    
    <div class="container">
        <div class="info-divisi">
            <strong>Role:</strong> Direktur | 
            <strong>Nama:</strong> <?php echo htmlspecialchars($nama_direktur); ?>
            <div style="color: #666; font-size: 0.9rem; margin-top: 5px;">
                💡 Info: Cuti dari direktur langsung diterima tanpa perlu persetujuan
            </div>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Karyawan</th>
                        <th>Nama Karyawan</th>
                        <th>Divisi</th>
                        <th>Role</th>
                        <th>Jenis Cuti</th>
                        <th>Periode Cuti</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['kode_karyawan']); ?></td>
                            <td>
                                <div class="jenis-cuti-info"><?php echo htmlspecialchars($row['nama_karyawan']); ?></div>
                                <div class="cuti-details">
                                    <strong>Jabatan:</strong> <?php echo htmlspecialchars($row['jabatan']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo str_replace(' ', '-', $row['role']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($row['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="jenis-cuti-info"><?php echo htmlspecialchars($row['jenis_cuti']); ?></div>
                                <?php if (!empty($row['file_surat_dokter'])): ?>
                                    <div class="file-info">
                                        <strong>File:</strong> 
                                        <a href="<?php echo htmlspecialchars($row['file_surat_dokter']); ?>" class="file-link" target="_blank">Lihat Surat Dokter</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="time-info">
                                    <strong>Mulai:</strong> <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?><br>
                                    <strong>Selesai:</strong> <?php echo date('d/m/Y', strtotime($row['tanggal_akhir'])); ?><br>
                                    <strong>Total:</strong> 
                                    <?php 
                                        $start = new DateTime($row['tanggal_mulai']);
                                        $end = new DateTime($row['tanggal_akhir']);
                                        $interval = $start->diff($end);
                                        echo ($interval->days + 1) . ' hari';
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="cuti-details">
                                    <?php echo nl2br(htmlspecialchars($row['alasan'])); ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-pending"><?php echo htmlspecialchars($row['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Anda yakin ingin menyetujui cuti ini?')">
                                        <input type="hidden" name="id_cuti" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-approve">Setujui</button>
                                    </form>
                                    <button type="button" class="btn btn-reject" onclick="openRejectModal(<?php echo $row['id']; ?>)">Tolak</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>Tidak ada pengajuan cuti yang menunggu persetujuan saat ini.</p>
                <p><small>Semua pengajuan cuti telah diproses. <a href="riwayat_cuti_direktur.php" style="color: var(--primary-color);">Lihat riwayat cuti</a></small></p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="riwayat_cuti_direktur.php" class="back-link">📋 Lihat Riwayat Semua Cuti</a>
            <a href="dashboarddirektur.php" class="back-link">← Kembali ke Dashboard</a>
        </div>
    </div>
</main>

<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Alasan Penolakan Cuti</h3>
        </div>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="id_cuti" id="modalCutiId">
            <input type="hidden" name="action" value="reject">
            <div class="form-group">
                <label for="alasan">Berikan alasan penolakan:</label>
                <textarea name="alasan" id="alasan" placeholder="Masukkan alasan penolakan cuti..." required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn btn-submit">Tolak Cuti</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(cutiId) {
        document.getElementById('modalCutiId').value = cutiId;
        document.getElementById('rejectModal').style.display = 'block';
        document.getElementById('alasan').focus();
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('alasan').value = '';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('rejectModal');
        if (event.target === modal) {
            closeRejectModal();
        }
    }
    
    document.getElementById('rejectForm').addEventListener('submit', function(e) {
        const alasan = document.getElementById('alasan').value.trim();
        if (!alasan) {
            e.preventDefault();
            alert('Harap berikan alasan penolakan.');
            return false;
        }
        if (!confirm('Apakah Anda yakin ingin menolak cuti ini?')) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>