<?php
// === BAGIAN LOGIKA BACKEND (PHP) ===

// 1. Memulai session dan menyertakan config (Asumsi Anda memiliki file config.php)
session_start();
// require 'config.php'; 

// 2. Ambil data Direktur yang sedang login (Menggunakan data dummy/session placeholder)
$nama_direktur = $_SESSION['nama_lengkap'] ?? 'Budi Direktur'; 
$jabatan_direktur = $_SESSION['jabatan'] ?? 'Direktur Utama'; 

// 3. LOGIKA UNTUK MENGAMBIL DATA PENGAJUAN CUTI YANG MENUNGGU PERSETUJUAN DIREKTUR
//    *Hapus placeholder di bawah dan ganti dengan kode koneksi dan query SQL Anda yang sebenarnya*

// **Contoh Struktur Query yang Dibutuhkan:**
/*
    $query_pengajuan = "
        SELECT 
            pc.id, dk.nama_lengkap, dk.divisi, pc.jenis_cuti, pc.tanggal_mulai, 
            DATEDIFF(pc.tanggal_akhir, pc.tanggal_mulai) + 1 AS jumlah_hari, 
            pc.status_sdm // Kolom untuk status dari SDM
        FROM 
            pengajuan_cuti pc
        JOIN 
            data_karyawan dk ON pc.kode_pegawai = dk.kode_karyawan 
        WHERE 
            pc.status = 'Menunggu Direktur' 
        ORDER BY 
            pc.created_at ASC
    ";
    $result = $conn->query($query_pengajuan);
    $data_pengajuan = $result->fetch_all(MYSQLI_ASSOC);
*/

// Data Dummy (Hanya untuk menampilkan tampilan UI dengan data)
$data_pengajuan = [
    [
        'id' => 101,
        'nama_lengkap' => 'Budi Santoso',
        'divisi' => 'Marketing',
        'jenis_cuti' => 'Cuti Tahunan',
        'tanggal_mulai' => '2025-10-10',
        'tanggal_akhir' => '2025-10-14',
        'jumlah_hari' => 5,
        'status_sdm' => 'Disetujui SDM',
    ],
    [
        'id' => 102,
        'nama_lengkap' => 'Citra Dewi',
        'divisi' => 'Keuangan',
        'jenis_cuti' => 'Cuti Sakit',
        'tanggal_mulai' => '2025-10-05',
        'tanggal_akhir' => '2025-10-07',
        'jumlah_hari' => 3,
        'status_sdm' => 'Menunggu SDM', // Seharusnya tidak muncul, ini hanya contoh.
    ],
];
// *End of Data Dummy*

// 4. LOGIKA UNTUK MENANGANI AKSI PERSETUJUAN/PENOLAKAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $cuti_id = $_POST['cuti_id'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    
    // Tempatkan kode untuk update status di database di sini
    // Contoh:
    /*
    $new_status = ($action === 'approve') ? 'Disetujui Direktur' : 'Ditolak Direktur';
    $stmt = $conn->prepare("UPDATE pengajuan_cuti SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $cuti_id);
    $stmt->execute();
    
    // Redirect untuk mencegah resubmission
    header("Location: persetujuan_cuti_direktur.php");
    exit();
    */
}

// $conn->close(); // Tutup koneksi jika dibuka di atas
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Cuti Direktur</title>
    <style>
        /* CSS yang sama seperti sebelumnya untuk konsistensi */
        body {
          margin:0;
          font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
          min-height:100vh;
          color:#fff;
        }
        header {
          background:#fff;
          padding:20px 40px;
          display:flex;
          justify-content:space-between;
          align-items:center;
          border-bottom:2px solid #34377c;
          flex-wrap:nowrap;
        }
        .logo {
          display:flex;
          align-items:center;
          gap:16px;
          font-weight:500;
          font-size:20px;
          color:#2e1f4f;
        }
        .logo img {
          width: 50px;
          height: 50px;
          object-fit: contain;
          border-radius: 50%;
        }
        nav ul {
          list-style:none;
          margin:0;
          padding:0;
          display:flex;
          gap:30px;
        }
        nav li { position:relative; }
        nav a {
          text-decoration:none;
          color:#333;
          font-weight:600;
          padding:8px 4px;
          display:block;
        }
        nav li ul {
          display:none;
          position:absolute;
          top:100%;
          left:0;
          background:#fff;
          padding:10px 0;
          border-radius:8px;
          box-shadow:0 2px 8px rgba(0,0,0,.15);
          min-width:200px;
          z-index:999;
        }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a {
          color:#333;
          font-weight:400;
          white-space:nowrap;
        }
        main {
          max-width:1200px;
          margin:40px auto;
          padding:0 20px;
        }
        h1 {
          text-align:left;
          font-size:28px;
          margin-bottom:10px;
          color:#fff;
        }
        .card {
          background:#fff;
          color:#2e1f4f;
          border-radius:20px;
          padding:30px 40px;
          box-shadow:0 2px 10px rgba(0,0,0,0.15);
          display: flex;
          flex-direction: column;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            color: #2e1f4f;
        }
        .data-table th, .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .data-table tbody tr:hover { background-color: #f1f1f1; }
        
        .btn {
          display:inline-block;
          color:#fff;
          padding:8px 14px;
          border-radius:6px;
          text-decoration:none;
          font-weight:600;
          font-size:13px;
          text-align: center;
          border: none;
          cursor: pointer;
          margin-right:5px;
        }
        .btn-approve {
            background-color: #28a745; /* Green */
        }
        .btn-reject {
            background-color: #dc3545; /* Red */
        }
        .btn-detail {
            background-color: #007bff; /* Blue */
        }
        .user-section {
          display: flex;
          align-items: center;
          gap: 15px;
        }
        .welcome-text {
          color: #2e1f4f;
          font-weight: 600;
        }
        .btn-pengajuan {
          background: #2e1f4f;
          color: #fff !important;
          padding: 10px 20px;
          border-radius: 8px;
          text-decoration: none;
          font-weight: 600;
          display: inline-flex;
          align-items: center;
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
          <li><a href="dashboard_direktur.php">Beranda</a></li>
          <li><a href="#">Cuti ▾</a>
            <ul>
              <li><a href="persetujuan_cuti_direktur.php" style="font-weight: 700;">Persetujuan Cuti</a></li>
              <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li>
            </ul>
          </li>
          <li><a href="#">KHL ▾</a>
            <ul>
              <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
              <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li>
            </ul>
          </li>
          <li><a href="#">Karyawan ▾</a>
            <ul>
              <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
            </ul>
          </li>
          <li><a href="#">Profil ▾</a></li>
        </ul>
      </nav>

      <div class="user-section">
        <span class="welcome-text">Welcome <?= htmlspecialchars($nama_direktur) ?>, <?= htmlspecialchars($jabatan_direktur) ?></span>
        <a href="pengajuan_cuti.php" class="btn-pengajuan">Pengajuan Cuti</a>
      </div>
    </header>

    <main>
        <h1>Daftar Persetujuan Cuti </h1>
        <p style="color:#fff; margin-bottom: 20px; opacity: 0.9;">Tinjau dan berikan persetujuan untuk pengajuan cuti yang telah diajukan.</p>
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama </th>
                        <th>Divisi</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Jumlah Hari</th>
                        <th>Status SDM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data_pengajuan)): ?>
                        <?php foreach($data_pengajuan as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['divisi']) ?></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']) ?></td>
                                
                                <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?></td>
                                <td><?= htmlspecialchars($row['jumlah_hari']) ?> Hari</td>
                                <td><?= htmlspecialchars($row['status_sdm']) ?></td>
                                <td>
                                    <form method="POST" action="persetujuan_cuti_direktur.php" style="display:inline;">
                                        <input type="hidden" name="cuti_id" value="<?= htmlspecialchars($row['id']) ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-approve" onclick="return confirm('Yakin ingin MENYETUJUI cuti ini?');">Setujui</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-reject" onclick="return confirm('Yakin ingin MENOLAK cuti ini?');">Tolak</button>
                                    </form>
                                    <a href="detail_cuti.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-detail">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tidak ada pengajuan cuti yang menunggu persetujuan Direktur saat ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>