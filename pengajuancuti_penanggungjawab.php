<?php
// FILE: pengajuancuti_penanggungjawab.php
$nama_pj = "Budi Santoso";
$sisa_cuti_pj = 7;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengajuan Cuti Pribadi</title>
    <style>
        :root { --primary-color: #1E105E; --accent-color: #4a3f81; --card-bg: #FFFFFF; --text-color-light: #fff; --text-color-dark: #2e1f4f; --shadow-light: rgba(0,0,0,0.15); }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); min-height: 100vh; padding-bottom: 40px; }
        header { background: var(--card-bg); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px var(--shadow-light); }
        .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
        .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
        nav li { position: relative; }
        nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
        nav a:hover { color: var(--accent-color); }
        nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 20px; }
        nav li ul li a { color: var(--text-color-dark); font-weight: 400; white-space: nowrap; }
        main { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); color: var(--text-color-dark); border-radius: 20px; padding: 30px 40px; box-shadow: 0 5px 20px var(--shadow-light); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
        .btn-submit { background: var(--accent-color); color: var(--text-color-light); padding: 15px 25px; border-radius: 8px; border: none; font-size: 16px; cursor: pointer; width: 100%; }
        .cuti-info { background: #f0f2f5; padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; }
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
            <li><a href="dashboardpenanggungjawab.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti Karyawan</a></li>
                    <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Karyawan</a></li>
                    <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL Karyawan</a></li>
                    <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Karyawan</a></li>
                    <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
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
    <div class="card">
        <h2>Formulir Pengajuan Cuti (Pribadi)</h2>
        <div class="cuti-info">
            Halo, <strong><?= htmlspecialchars($nama_pj) ?></strong>! Sisa cuti Anda: <strong><?= $sisa_cuti_pj ?> hari</strong>.
        </div>
        <form action="" method="POST">
            <div class="form-group">
                <label for="tgl_mulai">Tanggal Mulai Cuti</label>
                <input type="date" id="tgl_mulai" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="tgl_selesai">Tanggal Selesai Cuti</label>
                <input type="date" id="tgl_selesai" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="jumlah">Jumlah Hari</label>
                <input type="number" id="jumlah" class="form-control" readonly style="background-color: #e9ecef;">
            </div>
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn-submit">Kirim Pengajuan ke Direktur</button>
        </form>
    </div>
</main>
<script>
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const jumlahHari = document.getElementById('jumlah');
    function hitungJumlahHari() {
        const mulai = new Date(tglMulai.value);
        const selesai = new Date(tglSelesai.value);
        if (tglMulai.value && tglSelesai.value && selesai >= mulai) {
            const diffDays = Math.ceil(Math.abs(selesai - mulai) / (1000 * 60 * 60 * 24)) + 1;
            jumlahHari.value = diffDays;
        } else {
            jumlahHari.value = '';
        }
    }
    tglMulai.addEventListener('change', hitungJumlahHari);
    tglSelesai.addEventListener('change', hitungJumlahHari);
</script>
</body>
</html>