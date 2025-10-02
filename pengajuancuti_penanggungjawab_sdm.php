<?php
session_start();
$nama_pj = "Dian";
$kode_pj = "SDM-001";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan Cuti Pribadi - SDM</title>
    <style>
        :root { --primary-color: #1E105E; --secondary-color: #8897AE; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; display: flex; flex-direction: column; }
        header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; padding: 5px 20px; white-space: nowrap; }
        main { padding: 40px 20px; display: flex; justify-content: center; align-items: center; flex-grow: 1; }
        .form-container { background: var(--card-bg); color: var(--text-color-dark); padding: 40px; border-radius: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.2); width: 100%; max-width: 500px; }
        .form-container h2 { text-align: center; margin-top: 0; margin-bottom: 10px; color: var(--primary-color); }
        .form-container p { text-align: center; margin-bottom: 30px; color: #666; }
        .form-container label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-container input, .form-container select, .form-container textarea { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #ccc; box-sizing: border-box; font-size: 16px; }
        .form-container input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .form-container button { width: 100%; padding: 15px; background: var(--accent-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; }
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
            <li><a href="dashboardpenanggungjawab_sdm.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab_sdm.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab_sdm.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab_sdm.php">Ajukan Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_penanggungjawab_sdm.php">Kalender Cuti Divisi</a></li>
                    <li><a href="riwayat_cuti_pribadi_penanggungjawab_sdm.php">Riwayat Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab_sdm.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab_sdm.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab_sdm.php">Ajukan KHL Pribadi</a></li>
                    <li><a href="kalender_khl_penanggungjawab_sdm.php">Kalender KHL Divisi</a></li>
                    <li><a href="riwayat_khl_pribadi_penanggungjawab_sdm.php">Riwayat KHL Pribadi</a></li>
                </ul>
            </li>
            <li><a href="karyawan_divisi_sdm.php">Karyawan Divisi</a></li>
            <li><a href="#">Profil ▾</a>
                <ul>
                    <li><a href="profil_penanggungjawab_sdm.php">Profil Saya</a></li>
                    <li><a href="logout2.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<main>
    <div class="form-container">
        <h2>Formulir Pengajuan Cuti Pribadi</h2>
        <p>Silakan isi detail pengajuan cuti Anda.</p>
        <form action="" method="POST">
            <label for="kode_karyawan">No. Kode Karyawan</label>
            <input type="text" id="kode_karyawan" name="kode_karyawan" value="<?= htmlspecialchars($kode_pj) ?>" readonly>
            <label for="nama_karyawan">Nama Karyawan</label>
            <input type="text" id="nama_karyawan" name="nama_karyawan" value="<?= htmlspecialchars($nama_pj) ?>" readonly>
            <label for="jenis_cuti">Jenis Cuti</label>
            <select name="jenis_cuti" id="jenis_cuti" required>
                <option value="">Pilih Jenis Cuti</option>
                <option value="Cuti Tahunan">Cuti Tahunan</option>
                <option value="Cuti Sakit">Cuti Sakit</option>
                <option value="Cuti Alasan Penting">Cuti Alasan Penting</option>
                <option value="Cuti Melahirkan">Cuti Melahirkan</option>
            </select>
            <label for="tanggal_mulai">Tanggal Mulai Cuti</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" required>
            <label for="tanggal_selesai">Tanggal Selesai Cuti</label>
            <input type="date" name="tanggal_selesai" id="tanggal_selesai" required>
            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" id="keterangan" rows="4" placeholder="Tuliskan alasan..." required></textarea>
            <button type="submit">Kirim Pengajuan ke Direktur</button>
        </form>
    </div>
</main>
</body>
</html>