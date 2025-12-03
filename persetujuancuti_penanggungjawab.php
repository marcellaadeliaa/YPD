<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$divisi_penanggung_jawab = $user['divisi'];
$nama_pj = $user['nama_lengkap'];

function hitungHariKerja($tanggal_mulai, $tanggal_akhir) {
    $jumlah_hari = 0;
    $current_date = new DateTime($tanggal_mulai);
    $end_date = new DateTime($tanggal_akhir);
    
    while ($current_date <= $end_date) {
        $day_of_week = $current_date->format('N'); 
        if ($day_of_week >= 1 && $day_of_week <= 5) { 
            $jumlah_hari++;
        }
        $current_date->modify('+1 day');
    }
    
    return $jumlah_hari;
}

if (isset($_GET['message']) && isset($_GET['message_type'])) {
    $message = $_GET['message'];
    $message_type = $_GET['message_type'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['id_cuti'])) {
        $id_cuti = $_POST['id_cuti'];
        $action = $_POST['action'];
        $alasan_penolakan = isset($_POST['alasan_penolakan']) ? trim($_POST['alasan_penolakan']) : '';
        
        $check_query = "SELECT dpc.*, dk.sisa_cuti_tahunan, dk.sisa_cuti_lustrum, dk.divisi, dk.role 
                          FROM data_pengajuan_cuti dpc 
                          JOIN data_karyawan dk ON dpc.kode_karyawan = dk.kode_karyawan 
                          WHERE dpc.id = ? AND dk.divisi = ? AND dk.role = 'karyawan'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $id_cuti, $divisi_penanggung_jawab);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $cuti_data = $check_result->fetch_assoc();
            
            if ($action == 'reject' && empty($alasan_penolakan)) {
                $message = "Harap berikan alasan penolakan";
                $message_type = "error";
            } else {
                if ($action == 'approve') {
                    $jumlah_hari = hitungHariKerja($cuti_data['tanggal_mulai'], $cuti_data['tanggal_akhir']);
                    
                    if ($cuti_data['jenis_cuti'] == 'Tahunan') {
                        if ($cuti_data['sisa_cuti_tahunan'] < $jumlah_hari) {
                            $message = "Sisa cuti tahunan tidak mencukupi. Sisa: " . $cuti_data['sisa_cuti_tahunan'] . " hari kerja, Butuh: " . $jumlah_hari . " hari kerja";
                            $message_type = "error";
                        }
                    } elseif ($cuti_data['jenis_cuti'] == 'Lustrum') {
                        if ($cuti_data['sisa_cuti_lustrum'] < $jumlah_hari) {
                            $message = "Sisa cuti lustrum tidak mencukupi. Sisa: " . $cuti_data['sisa_cuti_lustrum'] . " hari kerja, Butuh: " . $jumlah_hari . " hari kerja";
                            $message_type = "error";
                        }
                    }
                    
                    if (isset($message) && $message_type == 'error') {
                    } else {
                        $new_status = 'Diterima';
                        $waktu_persetujuan = date('Y-m-d H:i:s');
                        
                        $update_query = "UPDATE data_pengajuan_cuti SET status = ?, alasan = ?, waktu_persetujuan = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        
                        $alasan_update = $cuti_data['alasan']; 
                        
                        $update_stmt->bind_param("sssi", $new_status, $alasan_update, $waktu_persetujuan, $id_cuti);
                        
                        if ($update_stmt->execute()) {
                            header("Location: persetujuancuti_penanggungjawab.php?message=Cuti berhasil disetujui&message_type=success");
                            exit();
                        } else {
                            $message = "Gagal memperbarui status cuti";
                            $message_type = "error";
                        }
                        
                        $update_stmt->close();
                    }
                } else { 
                    $new_status = 'Ditolak';
                    $waktu_persetujuan = date('Y-m-d H:i:s');
                    
                    $update_query = "UPDATE data_pengajuan_cuti SET status = ?, alasan_penolakan = ?, waktu_persetujuan = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    
                    $update_stmt->bind_param("sssi", $new_status, $alasan_penolakan, $waktu_persetujuan, $id_cuti);
                    
                    if ($update_stmt->execute()) {
                        header("Location: persetujuancuti_penanggungjawab.php?message=Cuti berhasil ditolak&message_type=success");
                        exit();
                    } else {
                        $message = "Gagal memperbarui status cuti";
                        $message_type = "error";
                    }
                    
                    $update_stmt->close();
                }
            }
        } else {
            $message = "Anda tidak memiliki akses untuk mengubah cuti ini atau pengajuan bukan dari karyawan";
            $message_type = "error";
        }
        
        $check_stmt->close();
    }
}

$query = "SELECT dpc.*, dk.nama_lengkap, dk.divisi, dk.role, dk.sisa_cuti_tahunan, dk.sisa_cuti_lustrum
          FROM data_pengajuan_cuti dpc 
          JOIN data_karyawan dk ON dpc.kode_karyawan = dk.kode_karyawan 
          WHERE dk.divisi = ? AND dk.role = 'karyawan' AND dpc.status = 'Menunggu Persetujuan'
          ORDER BY dpc.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $divisi_penanggung_jawab);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Cuti - Penanggung Jawab</title>
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
            gap: 30px; 
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
            padding: 10px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px var(--shadow-light); 
            min-width: 200px; 
            z-index: 999; 
        }
        
        nav li:hover > ul { 
            display: block; 
        }
        
        nav li ul li a { 
            color: var(--text-color-dark); 
            font-weight: 400; 
            white-space: nowrap; 
            padding: 5px 20px; 
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
        
        .file-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }
        
        .sisa-cuti-info {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
            padding: 3px 8px;
            background: #f8f9fa;
            border-radius: 4px;
            display: inline-block;
        }
        
        .warning {
            color: #ff9800;
            font-weight: bold;
        }
        
        .danger {
            color: #f44336;
            font-weight: bold;
        }
        
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

        .hari-kerja-info {
            background: #e7f3ff;
            padding: 8px 12px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 0.8rem;
            border-left: 3px solid #2196F3;
        }

        .weekend-note {
            color: #666;
            font-size: 0.75rem;
            font-style: italic;
            margin-top: 3px;
        }
    </style>
</head>
<body>
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
        <h1>Persetujuan Cuti Karyawan</h1>
        <p>Kelola pengajuan cuti dari karyawan divisi <?php echo htmlspecialchars($divisi_penanggung_jawab); ?></p>
    </div>
    
    <div class="container">
        <div class="info-divisi">
            <strong>Divisi Penanggung Jawab:</strong> <?php echo htmlspecialchars($divisi_penanggung_jawab); ?> | 
            <strong>Nama:</strong> <?php echo htmlspecialchars($nama_pj); ?>
            <div class="weekend-note">📝 Catatan: Sabtu & Minggu tidak terhitung sebagai hari cuti</div>
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
                        <th>Role</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th>Alasan</th>
                        <th>File Surat Dokter</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $jumlah_hari_kerja = hitungHariKerja($row['tanggal_mulai'], $row['tanggal_akhir']);
                        $total_hari_kalender = (strtotime($row['tanggal_akhir']) - strtotime($row['tanggal_mulai'])) / (60 * 60 * 24) + 1;
                        
                        $warning_class = '';
                        if ($row['jenis_cuti'] == 'Tahunan' && $row['sisa_cuti_tahunan'] < $jumlah_hari_kerja) {
                            $warning_class = 'danger';
                        } elseif ($row['jenis_cuti'] == 'Lustrum' && $row['sisa_cuti_lustrum'] < $jumlah_hari_kerja) {
                            $warning_class = 'danger';
                        } elseif ($row['jenis_cuti'] == 'Tahunan' && $row['sisa_cuti_tahunan'] - $jumlah_hari_kerja < 3) {
                            $warning_class = 'warning';
                        } elseif ($row['jenis_cuti'] == 'Lustrum' && $row['sisa_cuti_lustrum'] - $jumlah_hari_kerja < 2) {
                            $warning_class = 'warning';
                        }
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['kode_karyawan']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['nama_karyawan']); ?>
                                <div class="cuti-details">
                                    <strong>Periode:</strong> <?php echo $total_hari_kalender; ?> hari kalender<br>
                                    <div class="hari-kerja-info">
                                        <strong>Hari Kerja:</strong> <?php echo $jumlah_hari_kerja; ?> hari (Senin-Jumat)
                                    </div>
                                    <?php if ($row['jenis_cuti'] == 'Tahunan'): ?>
                                        <strong>Sisa Cuti Tahunan:</strong> <span class="<?php echo $warning_class; ?>"><?php echo $row['sisa_cuti_tahunan']; ?> hari</span>
                                    <?php elseif ($row['jenis_cuti'] == 'Lustrum'): ?>
                                        <strong>Sisa Cuti Lustrum:</strong> <span class="<?php echo $warning_class; ?>"><?php echo $row['sisa_cuti_lustrum']; ?> hari</span>
                                    <?php else: ?>
                                        <strong>Jenis Cuti:</strong> <?php echo htmlspecialchars($row['jenis_cuti']); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo str_replace(' ', '-', $row['role']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($row['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_mulai']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_akhir']); ?></td>
                            <td><?php echo htmlspecialchars($row['alasan']); ?></td>
                            <td>
                                <?php if (!empty($row['file_surat_dokter'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['file_surat_dokter']); ?>" class="file-link" target="_blank">Lihat File</a>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-pending"><?php echo htmlspecialchars($row['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_cuti" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-approve" onclick="return confirm('Setujui cuti ini? Sisa cuti akan dikurangi <?php echo $jumlah_hari_kerja; ?> hari kerja (Senin-Jumat).')">Setujui</button>
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
                <p>Tidak ada pengajuan cuti yang menunggu persetujuan dari <strong>karyawan</strong> divisi <?php echo htmlspecialchars($divisi_penanggung_jawab); ?> saat ini.</p>
                <p><small>Semua pengajuan cuti telah diproses. <a href="riwayatcuti_penanggungjawab.php" style="color: var(--primary-color);">Lihat riwayat cuti</a></small></p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="riwayatcuti_penanggungjawab.php" class="back-link">📋 Lihat Riwayat Cuti Divisi</a>
            <a href="dashboard_penanggungjawab.php" class="back-link">← Kembali ke Dashboard</a>
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
                <label for="alasan_penolakan">Berikan alasan penolakan:</label>
                <textarea name="alasan_penolakan" id="alasan_penolakan" placeholder="Masukkan alasan penolakan cuti..." required></textarea>
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
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('alasan_penolakan').value = '';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('rejectModal');
        if (event.target === modal) {
            closeRejectModal();
        }
    }
    
    document.getElementById('rejectForm').addEventListener('submit', function(e) {
        const alasan = document.getElementById('alasan_penolakan').value.trim();
        if (!alasan) {
            e.preventDefault();
            alert('Harap berikan alasan penolakan');
            return false;
        }
        return confirm('Apakah Anda yakin ingin menolak cuti ini?');
    });

    document.querySelectorAll('.btn-approve').forEach(button => {
        button.addEventListener('click', function(e) {
            const row = this.closest('tr');
            const warningElement = row.querySelector('.danger');
            if (warningElement) {
                if (!confirm('PERINGATAN: Sisa cuti tidak mencukupi! Apakah Anda yakin ingin menyetujui cuti ini?')) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>