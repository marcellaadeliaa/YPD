<?php
session_start();
// require 'config.php'; // Anda bisa aktifkan ini jika sudah mengambil data dari DB

// --- LOGIKA UNTUK MENANGANI AKSI APPROVE/REJECT ---
// Ini adalah contoh bagaimana Anda bisa menangani aksi dari tombol.
$pesan_aksi = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    if ($action == 'approve') {
        // Di sini Anda akan menjalankan query UPDATE untuk mengubah status menjadi 'Disetujui'
        // CONTOH: $conn->query("UPDATE pengajuan_cuti SET status = 'Disetujui' WHERE id = $id");
        $pesan_aksi = "Pengajuan Cuti ID #$id telah disetujui.";
    } elseif ($action == 'reject') {
        // Di sini Anda akan menjalankan query UPDATE untuk mengubah status menjadi 'Ditolak'
        // CONTOH: $conn->query("UPDATE pengajuan_cuti SET status = 'Ditolak' WHERE id = $id");
        $pesan_aksi = "Pengajuan Cuti ID #$id telah ditolak.";
    }
}


// --- DUMMY DATA (DATA CONTOH) ---
// Ganti bagian ini dengan query SELECT dari tabel pengajuan cuti Anda
$daftar_cuti = [
    [
        'id' => 1,
        'kode_karyawan' => '11223344',
        'nama_karyawan' => 'Xue',
        'divisi' => 'Admin SDM',
        'jenis_cuti' => 'Cuti Tahunan',
        'mulai_cuti' => '2025-10-05',
        'selesai_cuti' => '2025-10-07',
        'keterangan' => 'Keperluan keluarga di luar kota.',
        'status' => 'Menunggu Persetujuan'
    ],
    [
        'id' => 2,
        'kode_karyawan' => '11223355',
        'nama_karyawan' => 'Adel',
        'divisi' => 'Keuangan',
        'jenis_cuti' => 'Cuti Sakit',
        'mulai_cuti' => '2025-09-28',
        'selesai_cuti' => '2025-09-29',
        'keterangan' => 'Sakit demam, surat dokter terlampir.',
        'status' => 'Disetujui'
    ],
    [
        'id' => 3,
        'kode_karyawan' => '11223366',
        'nama_karyawan' => 'Budi Santoso',
        'divisi' => 'Operasional',
        'jenis_cuti' => 'Cuti Tahunan',
        'mulai_cuti' => '2025-11-01',
        'selesai_cuti' => '2025-11-02',
        'keterangan' => 'Acara pernikahan saudara.',
        'status' => 'Ditolak'
    ],
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administrasi Cuti - Admin SDM</title>
<style>
/* === Impor Style dari Halaman Dashboard Admin === */
body {
  margin:0;
  font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%);
  min-height:100vh;
  color:#333; /* Warna teks default untuk konten di dalam kartu */
}
header {
  background:rgba(255,255,255,1);
  padding:20px 40px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:2px solid #34377c;
  flex-wrap:wrap;
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
  width: 50px; height: 50px; object-fit: contain; border-radius: 50%;
}
nav ul {
  list-style:none; margin:0; padding:0; display:flex; gap:30px;
}
nav li { position:relative; }
nav a {
  text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block;
}
nav li ul {
  display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0;
  border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999;
}
nav li:hover > ul { display:block; }
nav li ul li { padding:5px 20px; }
nav li ul li a {
  color:#333; font-weight:400; white-space:nowrap;
}
main {
  max-width:1400px; /* Lebarkan kontainer untuk tabel */
  margin:40px auto;
  padding:0 20px;
}
h1, p.admin-title { color: #fff; } /* Warna teks untuk di luar kartu */
h1 {
  text-align:left; font-size:28px; margin-bottom:10px;
}
p.admin-title {
  font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9;
}
.card {
  background:#fff;
  color:#2e1f4f;
  border-radius:20px;
  padding:30px 40px;
  box-shadow:0 2px 10px rgba(0,0,0,0.15);
}

/* === Style Baru Khusus Halaman Administrasi === */
.page-title {
    font-size: 24px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
    color: #1E105E;
}
.action-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    align-items: center;
}
.action-bar input[type="search"] {
    flex-grow: 1;
    padding: 10px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
}
.action-bar button {
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    transition: opacity 0.3s;
}
.action-bar button:hover { opacity: 0.85; }
.btn-cari { background-color: #4a3f81; }
.btn-hapus { background-color: #d9534f; }

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.data-table th, .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.data-table th { background-color: #f8f9fa; font-weight: 600; }
.data-table tbody tr:hover { background-color: #f1f1f1; }

/* Style untuk status */
.status { padding: 5px 10px; border-radius: 15px; font-weight: 600; font-size: 12px; color: #fff; text-align:center; }
.status-pending { background-color: #f0ad4e; }
.status-approved { background-color: #5cb85c; }
.status-rejected { background-color: #d9534f; }

/* Style untuk tombol aksi */
.action-links a {
    display: inline-block;
    padding: 6px 10px;
    margin-right: 5px;
    border-radius: 5px;
    text-decoration: none;
    color: #fff;
    font-weight: bold;
}
.action-approve { background-color: #28a745; }
.action-reject { background-color: #d9534f; }

.pesan-sukses {
    padding: 15px;
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
</head>
<body>

<header>
  <div class="logo">
    <img src="https://yt3.googleusercontent.com/ytc/AIdro_k21dE_e_T4s2-9e5aB2H3-_hDUa8sGAky5TTsD=s900-c-k-c0x00ffffff-no-rj" alt="Logo Yayasan">
    <span>Yayasan Purba Danarta</span>
  </div>
    <nav>
        <ul>
        <li><a href="dashboardadmin.php">Beranda</a></li>
        <li><a href="#">Cuti ▾</a>
            <ul>
            <li><a href="administrasi_cuti.php">Administrasi Cuti</a></li>
            <li><a href="riwayat_cuti_pegawai.php">Riwayat Cuti</a></li>
            <li><a href="kalender_cuti.php">Kalender Cuti</a></li>
            <li><a href="daftar_sisa_cuti.php">Sisa Cuti Karyawan</a></li>
            </ul>
        </li>
        <li><a href="#">KHL ▾</a>
            <ul>
                <li><a href="administrasi_khl.php">Administrasi KHL</a></li>
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
        <li><a href="#">Karyawan ▾</a></li>
            <ul>
                <li><a href="data_karyawan.php">Data Karyawan</a></li>
            </ul>
        <li><a href="#">Profil ▾</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, Xue!</h1>
    <p class="admin-title">Admin Divisi XXXX</p>

    <div class="card">
        <h2 class="page-title">Administrasi Cuti</h2>

        <?php if ($pesan_aksi): ?>
            <div class="pesan-sukses"><?= $pesan_aksi ?></div>
        <?php endif; ?>
        
        <div class="action-bar">
            <input type="search" placeholder="Cari berdasarkan nama atau NIK...">
            <button class="btn-cari">Cari</button>
            <button class="btn-hapus">Hapus</button>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Kode</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Jenis Cuti</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($daftar_cuti as $cuti): ?>
                <tr>
                    <td><?= htmlspecialchars($cuti['kode_karyawan']) ?></td>
                    <td><?= htmlspecialchars($cuti['nama_karyawan']) ?></td>
                    <td><?= htmlspecialchars($cuti['divisi']) ?></td>
                    <td><?= htmlspecialchars($cuti['jenis_cuti']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cuti['mulai_cuti'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($cuti['selesai_cuti'])) ?></td>
                    <td><?= htmlspecialchars($cuti['keterangan']) ?></td>
                    <td>
                        <?php 
                        $status_class = '';
                        if ($cuti['status'] == 'Menunggu Persetujuan') $status_class = 'status-pending';
                        elseif ($cuti['status'] == 'Disetujui') $status_class = 'status-approved';
                        elseif ($cuti['status'] == 'Ditolak') $status_class = 'status-rejected';
                        ?>
                        <span class="status <?= $status_class ?>"><?= htmlspecialchars($cuti['status']) ?></span>
                    </td>
                    <td class="action-links">
                        <a href="?action=approve&id=<?= $cuti['id'] ?>" class="action-approve" title="Setujui">✓</a>
                        <a href="?action=reject&id=<?= $cuti['id'] ?>" class="action-reject" title="Tolak">✗</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>