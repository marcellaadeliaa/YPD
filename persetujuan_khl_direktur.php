<?php
session_start();
require 'config.php';

// Simulasi user direksi (untuk keperluan development)
// Dalam produksi, bagian ini harus diganti dengan sistem login yang sesungguhnya
$_SESSION['user'] = [
    'role' => 'direksi',
    'nama_lengkap' => 'Direksi Perusahaan',
    'kode_karyawan' => 'DIR001'
];

$user = $_SESSION['user'];
$nama_direksi = $user['nama_lengkap'];

// Proses persetujuan/tolak KHL
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['id_khl'])) {
        $id_khl = $_POST['id_khl'];
        $action = $_POST['action'];
        $alasan_penolakan = isset($_POST['alasan_penolakan']) ? trim($_POST['alasan_penolakan']) : '';
        
        // Validasi apakah KHL tersebut ada
        $check_query = "SELECT * FROM data_pengajuan_khl WHERE id_khl = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $id_khl);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Jika menolak, validasi alasan penolakan
            if ($action == 'reject' && empty($alasan_penolakan)) {
                $message = "Harap berikan alasan penolakan";
                $message_type = "error";
            } else {
                // Update status KHL
                $new_status = ($action == 'approve') ? 'disetujui' : 'ditolak';
                
                $update_query = "UPDATE data_pengajuan_khl SET status_khl = ?, alasan_penolakan = ? WHERE id_khl = ?";
                $update_stmt = $conn->prepare($update_query);
                
                if ($action == 'approve') {
                    $update_stmt->bind_param("ssi", $new_status, $alasan_penolakan, $id_khl);
                } else {
                    $update_stmt->bind_param("ssi", $new_status, $alasan_penolakan, $id_khl);
                }
                
                if ($update_stmt->execute()) {
                    // Redirect ke riwayat setelah berhasil
                    header("Location: riwayat_khl_direktur.php?status=" . ($action == 'approve' ? 'disetujui' : 'ditolak'));
                    exit();
                } else {
                    $message = "Gagal memperbarui status KHL";
                    $message_type = "error";
                }
                
                $update_stmt->close();
            }
        } else {
            $message = "KHL tidak ditemukan";
            $message_type = "error";
        }
        
        $check_stmt->close();
    }
}

// Ambil data KHL dengan status pending dari semua karyawan (karyawan dan penanggung jawab)
$query = "SELECT dpk.*, dk.nama_lengkap, dk.divisi, dk.role 
          FROM data_pengajuan_khl dpk 
          JOIN data_karyawan dk ON dpk.kode_karyawan = dk.kode_karyawan 
          WHERE dpk.status_khl = 'pending'
          ORDER BY dpk.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan KHL - Direksi</title>
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
        
        .role-penanggung-jawab {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .role-direksi {
            background: #f8d7da;
            color: #721c24;
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
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboard_direksi.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direksi.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti Perusahaan</a></li>
                    <li><a href="kalender_cuti_direksi.php">Kalender Cuti Perusahaan</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat KHL Perusahaan</a></li>
                    <li><a href="kalender_khl_direksi.php">Kalender KHL Perusahaan</a></li>
                </ul>
            </li>
            <li><a href="data_karyawan_direksi.php">Data Karyawan</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_direksi.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <div class="heading-section">
        <h1>Persetujuan Kerja Hari Libur (KHL)</h1>
        <p>Kelola pengajuan KHL dari seluruh karyawan perusahaan</p>
    </div>
    
    <div class="container">
        <div class="info-divisi">
            <strong>Peran:</strong> Direksi | 
            <strong>Nama:</strong> <?php echo htmlspecialchars($nama_direksi); ?>
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
                        <th>Proyek</th>
                        <th>Tanggal KHL</th>
                        <th>Jam Kerja</th>
                        <th>Tanggal Cuti KHL</th>
                        <th>Jam Cuti KHL</th>
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
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo str_replace(' ', '-', $row['role']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($row['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['proyek']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_khl']); ?></td>
                            <td><?php echo htmlspecialchars($row['jam_mulai_kerja'] . ' - ' . $row['jam_akhir_kerja']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_cuti_khl']); ?></td>
                            <td><?php echo htmlspecialchars($row['jam_mulai_cuti_khl'] . ' - ' . $row['jam_akhir_cuti_khl']); ?></td>
                            <td>
                                <span class="status-pending">Menunggu</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_khl" value="<?php echo $row['id_khl']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-approve" onclick="return confirm('Setujui KHL ini?')">Setujui</button>
                                    </form>
                                    <button type="button" class="btn btn-reject" onclick="openRejectModal(<?php echo $row['id_khl']; ?>)">Tolak</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>Tidak ada pengajuan KHL yang menunggu persetujuan dari seluruh karyawan perusahaan saat ini.</p>
                <p><small>Semua pengajuan KHL telah diproses. <a href="riwayat_khl_direktur.php" style="color: var(--primary-color);">Lihat riwayat KHL</a></small></p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="riwayat_khl_direktur.php" class="back-link">📋 Lihat Riwayat KHL Perusahaan</a>
            <a href="dashboard_direksi.php" class="back-link">← Kembali ke Dashboard</a>
        </div>
    </div>
</main>

<!-- Modal untuk penolakan -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Alasan Penolakan KHL</h3>
        </div>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="id_khl" id="modalKhlId">
            <input type="hidden" name="action" value="reject">
            <div class="form-group">
                <label for="alasan_penolakan">Berikan alasan penolakan:</label>
                <textarea name="alasan_penolakan" id="alasan_penolakan" placeholder="Masukkan alasan penolakan KHL..." required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn btn-submit">Tolak KHL</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(khlId) {
        document.getElementById('modalKhlId').value = khlId;
        document.getElementById('rejectModal').style.display = 'block';
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('alasan_penolakan').value = '';
    }
    
    // Tutup modal ketika klik di luar modal
    window.onclick = function(event) {
        const modal = document.getElementById('rejectModal');
        if (event.target === modal) {
            closeRejectModal();
        }
    }
    
    // Validasi form penolakan
    document.getElementById('rejectForm').addEventListener('submit', function(e) {
        const alasan = document.getElementById('alasan_penolakan').value.trim();
        if (!alasan) {
            e.preventDefault();
            alert('Harap berikan alasan penolakan');
            return false;
        }
        return confirm('Apakah Anda yakin ingin menolak KHL ini?');
    });
</script>
</body>
</html>

<?php
// Tutup koneksi
$stmt->close();
$conn->close();
?>