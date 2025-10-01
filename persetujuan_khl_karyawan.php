<?php
session_start();
require 'config.php';

// Halaman ini untuk direktur, jadi pastikan role-nya direktur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'direktur') {
    // header("Location: login.php");
    // exit();
}

// LOGIKA PERSETUJUAN / PENOLAKAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan = $_POST['id_pengajuan'];
    $status_baru = $_POST['status']; // Nilai: 'Disetujui Direktur' atau 'Ditolak'
    
    $stmt = $conn->prepare("UPDATE pengajuan_khl SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status_baru, $id_pengajuan);
    
    if ($stmt->execute()) {
        echo "<script>alert('Status pengajuan berhasil diperbarui.');</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status.');</script>";
    }
    
    $stmt->close();
    // Redirect untuk refresh halaman
    echo "<script>window.location.href='persetujuan_khl_karyawan.php';</script>";
    exit();
}

// MENGAMBIL DATA KHL DARI KARYAWAN (YANG PERLU PERSETUJUAN)
// Asumsi statusnya adalah 'Menunggu HRD'. Sesuaikan jika berbeda.
$query = "SELECT * FROM pengajuan_khl WHERE status = 'Menunggu HRD' ORDER BY created_at ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan KHL Karyawan</title>
    <style>
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#fff; } header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; flex-wrap:wrap; } .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; } .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; } nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; } nav li { position:relative; } nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; } nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; } nav li:hover > ul { display:block; } nav li ul li { padding:5px 20px; } nav li ul li a { color:#333; font-weight:400; white-space:nowrap; } main { max-width:1200px; margin:40px auto; padding:0 20px; } h1 { text-align:left; font-size:28px; margin-bottom:10px; color:#fff; } .card { background:#fff; color:#2e1f4f; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); display: flex; flex-direction: column; } .btn { display:inline-block; color:#fff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; text-align: center; border: none; cursor: pointer; } .btn:hover { opacity: 0.9; } .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; color: #2e1f4f; } .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; } .data-table th { background-color: #f8f9fa; font-weight: 600; } .data-table tbody tr:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>

<header>
    <div class="logo"> <img src="image/namayayasan.png" alt="Logo Yayasan"> <span>Yayasan Purba Danarta</span> </div> <nav> <ul> <li><a href="dashboard_direktur.php">Beranda</a></li> <li><a href="#">Cuti ▾</a> <ul> <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li> <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li> </ul> </li> <li><a href="#">KHL ▾</a> <ul> <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li> <li><a href="riwayat_khl_direktur.php">Riwayat KHL</a></li> </ul> </li> <li><a href="#">Karyawan ▾</a> <ul> <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li> <li><a href="data_direktur_pj.php">Data Direktur</a></li> </ul> </li> <li><a href="#">Profil ▾</a> <ul> <li><a href="profil_direktur.php">Profil Direktur</a></li> <li><a href="logout2.php">Logout</a></li> </ul> </li> </ul> </nav>
</header>

<main>
    <h1>Persetujuan KHL Karyawan</h1>
    <p style="color:#fff; margin-bottom: 20px; opacity: 0.9;">Daftar pengajuan KHL dari karyawan yang perlu Anda tinjau.</p>
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal KHL</th>
                    <th>Alasan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                            <td><?= htmlspecialchars($row['divisi']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_khl'])) ?></td>
                            <td><?= htmlspecialchars($row['alasan']) ?></td>
                            <td>
                                <form action="persetujuan_khl_karyawan.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id_pengajuan" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="status" value="Disetujui Direktur">
                                    <button type="submit" class="btn" style="background-color: green;">Setujui</button>
                                </form>
                                <form action="persetujuan_khl_karyawan.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id_pengajuan" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="status" value="Ditolak">
                                    <button type="submit" class="btn" style="background-color: red;">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada pengajuan KHL dari karyawan yang perlu disetujui.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>