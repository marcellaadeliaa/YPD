<?php
// FILE: pengajuan_khl_direktur.php

// Data Direktur
// Kode Karyawan dibuat kosong agar input awalnya kosong, sesuai permintaan.
$kode_karyawan = ""; 
$nama_direktur = "Pico"; 
$jabatan = "Direktur";

// Data placeholder untuk form dropdown (simulasi dari DB)
$proyek_list = ["Proyek A (Jakarta)", "Proyek B (Bandung)", "Proyek C (Surabaya)", "Proyek Internal"];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan KHL - Yayasan Purba Danarta</title>
    <style>
        /* === Variabel Warna & Font Konsisten === */
        :root {
            --primary-color: #1E105E; /* Ungu gelap */
            --secondary-color: #8897AE; /* Abu-abu kebiruan */
            --accent-color: #4a3f81; /* Ungu sedang untuk tombol */
            --card-bg: #FFFFFF;
            --text-color-light: #fff;
            --text-color-dark: #2e1f4f;
            --shadow-light: rgba(0,0,0,0.15);
        }

        /* === GLOBAL STYLES === */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh;
            color: var(--text-color-light);
            padding-bottom: 50px;
        }

        /* ===== HEADER & NAV ===== */
        header {
            background: var(--card-bg);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent-color);
            flex-wrap: wrap;
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
        nav a {
            text-decoration: none;
            color: var(--text-color-dark);
            font-weight: 600;
            padding: 8px 4px;
            display: block;
            transition: color 0.3s ease;
        }
        nav a:hover {
            color: var(--accent-color);
        }
        nav li { position: relative; }
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
        nav li:hover > ul { display: block; }
        
        /* === MAIN CONTENT & FORM LAYOUT === */
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .welcome-section {
            text-align: left;
            margin-bottom: 30px;
        }
        .welcome-section h1 {
            font-size: 2.5rem;
            margin: 0;
        }
        
        .form-card {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 40px 60px;
            box-shadow: 0 5px 20px var(--shadow-light);
            max-width: 500px; 
            margin: 0 auto;
            text-align: left;
        }

        .form-card h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 24px;
        }
        .form-card p.subtitle {
            text-align: center;
            color: var(--secondary-color);
            margin-bottom: 40px;
        }
        
        /* === INPUT & DROPDOWN STYLES === */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color-dark);
            font-size: 1rem;
        }
        
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 1px solid #ccc; /* Border untuk input */
            border-radius: 8px;
            background: #f0f0f5;
            box-sizing: border-box;
            font-size: 1rem;
            color: var(--text-color-dark);
        }
        
        /* HAPUS STYLING KHUSUS UNTUK INPUT READONLY/KODE KARYAWAN */
        .form-group input[readonly] {
             /* Hapus semua style di sini agar input Kode Karyawan normal */
             /* Biarkan kosong agar menggunakan style .form-group input di atas */
        }
        /* Jika Anda ingin agar input readonly tetap terlihat sedikit berbeda: */
        .form-group input[readonly] {
             background: #f8f9fa; /* Lebih terang dari input biasa */
             color: var(--text-color-dark);
             cursor: default;
             font-weight: 400; 
        }

        .select-wrapper {
            position: relative;
        }
        .select-wrapper::after {
            content: '⌄'; 
            position: absolute;
            top: 55%;
            right: 20px;
            transform: translateY(-50%);
            color: var(--secondary-color);
            pointer-events: none;
            font-size: 1.2rem;
        }
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* Grouping untuk Jam */
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .form-card h3 {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        /* === BUTTON STYLES === */
        .submit-btn {
            background: var(--accent-color);
            color: var(--text-color-light);
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
            display: block; 
            width: 100%;
            margin-top: 40px;
        }
        .submit-btn:hover {
            background-color: #352d5c;
        }

        /* === RESPONSIVE CSS === */
        @media(max-width: 768px){
            header{
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }
            nav ul{
                flex-direction: column;
                gap: 10px;
                width: 100%;
                margin-top: 15px;
            }
            .form-card{
                padding: 30px 20px;
                max-width: 100%; 
            }
            .form-row {
                flex-direction: column; 
                gap: 0;
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
            <li><a href="dashboard_direktur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Cuti</a></li>
                </ul>
            </li>
            <li style="border-bottom: 2px solid var(--accent-color);"><a href="#">KHL ▾</a> 
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
            <li><a href="#">Profil 👤</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="welcome-section">
        <h1>Welcome, <?= htmlspecialchars($nama_direktur) ?>!</h1>
        <p><?= htmlspecialchars($jabatan) ?></p>
    </div>

    <div class="form-card">
        <h2>Pengajuan KHL</h2>
        <p class="subtitle">Ajukan Permohonan KHL Anda</p>

        <form action="proses_pengajuan_khl.php" method="POST">
            
            <div class="form-group">
                <label for="kode_karyawan">No. Kode Karyawan</label>
                <input type="text" id="kode_karyawan" name="kode_karyawan" value="<?= htmlspecialchars($kode_karyawan) ?>" 
                       onclick="this.select()" required>
            </div>

            <div class="form-group select-wrapper">
                <label for="proyek">Proyek</label>
                <select id="proyek" name="proyek" required>
                    <option value="" disabled selected>Pilih Proyek</option>
                    <?php foreach ($proyek_list as $proyek): ?>
                        <option value="<?= htmlspecialchars($proyek) ?>"><?= htmlspecialchars($proyek) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_khl">Tanggal KHL</label>
                <input type="date" id="tanggal_khl" name="tanggal_khl" required>
            </div>

            <h3>Jadwal Kerja KHL</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="jam_mulai_kerja">Jam Mulai Kerja</label>
                    <input type="time" id="jam_mulai_kerja" name="jam_mulai_kerja" value="08:00" required>
                </div>
                <div class="form-group">
                    <label for="jam_akhir_kerja">Jam Akhir Kerja</label>
                    <input type="time" id="jam_akhir_kerja" name="jam_akhir_kerja" value="17:00" required>
                </div>
            </div>

            <h3>Jadwal Libur Pengganti</h3>
            <div class="form-group">
                <label for="tanggal_libur">Tanggal Libur</label>
                <input type="date" id="tanggal_libur" name="tanggal_libur" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="jam_mulai_libur">Jam Mulai Libur</label>
                    <input type="time" id="jam_mulai_libur" name="jam_mulai_libur" value="08:00" required>
                </div>
                <div class="form-group">
                    <label for="jam_akhir_libur">Jam Akhir Libur</label>
                    <input type="time" id="jam_akhir_libur" name="jam_akhir_libur" value="17:00" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Masukkan</button>
        </form>
    </div>
</div>
</body>
</html>