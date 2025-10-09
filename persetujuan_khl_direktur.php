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
    :root {
        --primary-color: #1E105E;
        --secondary-color: #8897AE;
        --accent-color: #4a3f81;
        --card-bg: #fff;
        --text-dark: #2e1f4f; 
        --text-light: #fff;
        --shadow-light: rgba(0,0,0,0.15);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%);
        min-height: 100vh;
        color: var(--text-dark); 
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
        color: var(--text-dark); 
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
        color: var(--text-dark); 
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
        color: var(--text-dark); 
        font-weight: 400; 
        white-space: nowrap; 
        padding: 10px 25px;
    }

    main {
        max-width: 1300px;
        margin: 40px auto;
        background: var(--card-bg);
        color: var(--text-dark);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    h1 {
        color: var(--primary-color);
        margin-bottom: 10px;
        font-size: 28px;
    }

    .subtitle {
        color: #666;
        margin-bottom: 20px;
        font-size: 16px;
    }

    .director-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 25px;
        border-left: 4px solid var(--accent-color);
    }

    .director-info strong {
        color: var(--primary-color);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: var(--card-bg);
    }

    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        text-align: left;
    }

    th {
        background-color: var(--primary-color);
        color: var(--text-light);
        font-weight: 600;
        position: sticky;
        top: 0;
    }

    tr:hover {
        background: #f8f9fa;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        padding: 6px 12px;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        color: #fff;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        margin: 2px;
    }

    .btn-approve {
        background: #28a745;
    }

    .btn-approve:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    .btn-reject {
        background: #dc3545;
    }

    .btn-reject:hover {
        background: #c82333;
        transform: translateY(-1px);
    }

    .btn-cancel {
        background: #6c757d;
    }

    .btn-cancel:hover {
        background: #545b62;
    }

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

    .no-data {
        text-align: center;
        color: #6c757d;
        padding: 40px 20px;
        font-style: italic;
        background: #f8f9fa;
        border-radius: 10px;
        margin-top: 20px;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        backdrop-filter: blur(5px);
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
        color: var(--primary-color);
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
        border-color: var(--accent-color);
    }

    .modal-actions {
        margin-top: 20px;
        text-align: right;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        header { 
            flex-direction: column; 
            padding: 15px 20px; 
            gap: 15px; 
        }
    
        nav ul { 
            flex-direction: column; 
            gap: 10px; 
            width: 100%; 
        }
    
        nav li ul { 
            position: static; 
            box-shadow: none; 
            border: 1px solid #e0e0e0; 
            padding: 5px 0; 
        }
        
        nav li ul li a {
            padding: 8px 25px;
        }

        main {
            margin: 15px;
            padding: 20px;
        }

        table {
            display: block;
            overflow-x: auto;
        }

        .modal-content {
            width: 95%;
            margin: 10% auto;
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
    <h1>Persetujuan Kehadiran Harian Lepas (KHL)</h1>
    <p class="subtitle">Kelola dan verifikasi pengajuan KHL dari seluruh karyawan.</p>

    <div class="director-info">
        <strong>Direktur:</strong> <?php echo htmlspecialchars($nama_direktur); ?>
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
                    <th>Divisi</th>
                    <th>Jabatan</th>
                    <th>Role</th>
                    <th>Proyek</th>
                    <th>Tanggal KHL</th>
                    <th>Jam Kerja</th>
                    <th>Tanggal Cuti KHL</th>
                    <th>Jam Cuti</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['kode_karyawan']); ?></td>
                    <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                    <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['role'])); ?></td>
                    <td><?php echo htmlspecialchars($row['proyek']); ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal_khl']); ?></td>
                    <td><?php echo htmlspecialchars($row['jam_mulai_kerja'] . ' - ' . $row['jam_akhir_kerja']); ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal_cuti_khl']); ?></td>
                    <td><?php echo htmlspecialchars($row['jam_mulai_cuti_khl'] . ' - ' . $row['jam_akhir_cuti_khl']); ?></td>
                    <td><span class="status-pending"><?php echo htmlspecialchars($row['status_khl']); ?></span></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_khl" value="<?php echo $row['id_khl']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-approve" onclick="return confirm('Setujui data KHL ini?')">Setujui</button>
                        </form>
                        <button class="btn btn-reject" onclick="openRejectModal(<?php echo $row['id_khl']; ?>)">Tolak</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <p>Tidak ada pengajuan KHL yang menunggu persetujuan.</p>
        </div>
    <?php endif; ?>
</main>

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
                <button type="submit" class="btn btn-reject">Tolak KHL</button>
            </div>
        </form>
    </div>
</div>

<script>
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