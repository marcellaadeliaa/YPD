<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'direktur') {
    header("Location: login.php");
    exit();
}

// Mengambil semua riwayat cuti dari database
$query_riwayat_cuti = "SELECT * FROM pengajuan_cuti ORDER BY created_at DESC";
$result_riwayat_cuti = $conn->query($query_riwayat_cuti);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Cuti Direktur</title>
    <style>
        /* CSS yang sama dengan dashboard direktur untuk konsistensi */
        <?php include 'dashboarddirektur.php'; ?>
    </style>
</head>
<body>
    <header>
      </header>

    <main>
        <h1>Riwayat Cuti Seluruh Karyawan</h1>
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Divisi</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th>Alasan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_riwayat_cuti->num_rows > 0): ?>
                        <?php while($row = $result_riwayat_cuti->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                                <td><?= htmlspecialchars($row['divisi']) ?></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_akhir'])) ?></td>
                                <td><?= htmlspecialchars($row['alasan']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Tidak ada riwayat cuti.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>