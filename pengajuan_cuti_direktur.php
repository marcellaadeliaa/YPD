<?php
// FILE: pengajuan_cuti.php

// Untuk tujuan tampilan saja, kita tidak memerlukan logika PHP/Database yang kompleks di sini.
// Namun, jika ada data yang ingin diambil (misal: Sisa Cuti), Anda bisa menempatkan logika PHP di sini.

// Data sisa cuti (Contoh statis, nanti bisa diganti dari database)
$sisa_cuti_tahunan = 10;
$sisa_cuti_lustrum = 4;
$nama_karyawan = "Adrian"; // Placeholder nama
// Menghapus inisialisasi kode_karyawan atau menjadikannya string kosong agar input kosong
$kode_karyawan = ""; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Cuti - Yayasan Purba Danarta</title>
    <style>
        /* === CSS NATIVE UNTUK TAMPILAN UI/UX === */
        :root {
            --primary-color: #1E105E;
            --secondary-color: #8897AE;
            --card-bg: #FFFFFF;
            --text-dark: #333;
            --text-light: #fff;
            --input-bg: #F0F0F0;
            --button-color: #4A3F81;
            --shadow-light: rgba(0,0,0,0.15);
            --header-bg: #FFFFFF; /* Warna Header Putih */
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 80%);
            min-height: 100vh;
            color: var(--text-light);
        }

        /* ===== HEADER & NAVIGASI ===== */
        header {
            background: var(--header-bg);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px var(--shadow-light);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            color: var(--text-dark);
            font-weight: 600;
        }
        .logo img {
            width: 40px; 
            height: 40px;
            object-fit: contain;
        }
        
        .logo span {
            font-family: serif; 
            font-size: 24px;
        }

        /* Navigasi */
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center; 
            gap: 30px;
        }
        nav li {
            position: relative;
        }
        nav a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            padding: 10px 0;
            display: flex; 
            align-items: center; 
            gap: 5px; 
        }
        
        /* Style untuk Ikon Profil */
        .profile-icon {
            font-size: 20px; /* Ukuran ikon dikurangi */
            line-height: 1;
            color: var(--button-color);
            margin-left: 2px;
        }
        
        /* Style Dropdown Menu */
        nav li ul {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--card-bg);
            padding: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px var(--shadow-light);
            min-width: 150px;
            z-index: 1000;
        }
        nav li:hover > ul { display: block; }
        nav li ul li { padding: 5px 15px; }
        nav li ul li a {
            color: var(--text-dark);
            font-weight: 400;
            padding: 5px 0;
            display: block; 
        }


        /* ===== MAIN CONTENT & FORM ===== */
        main {
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .welcome {
            max-width: 800px;
            width: 100%;
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-card {
            background: var(--card-bg);
            color: var(--text-dark);
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 4px 20px var(--shadow-light);
        }

        .form-card h2 {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-card p {
            font-size: 16px;
            margin-bottom: 30px;
            text-align: center;
            opacity: 0.8;
        }
        
        /* ===== FORM INPUT LAYOUT (NO. KODE KARYAWAN KEMBALI NORMAL) ===== */
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        
        /* Input Teks, Select, dan Date akan menggunakan style normal */
        .input-text, .input-select, .input-date {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: var(--input-bg);
            font-size: 16px;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            color: var(--text-dark); /* Menggunakan warna teks gelap (hitam) */
        }
        
        /* Hapus style spesifik untuk #no_karyawan */
        
        .input-select {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
        }
        
        .input-date {
            padding-right: 15px; 
        }

        /* Grouping Tombol dan Sisa Cuti */
        .action-group {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
        }
        
        .btn-submit {
            background: var(--button-color);
            color: var(--text-light);
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #352d5c;
        }

        .sisa-cuti {
            text-align: right;
            line-height: 1.6;
            color: var(--text-dark);
        }
        .sisa-cuti strong {
            display: block;
            margin-bottom: 5px;
            font-size: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header { 
                padding: 15px 20px; 
            }
            nav ul { 
                display: none; 
            }
            .welcome { 
                font-size: 1.8rem; 
            }
            .form-card { 
                padding: 30px 20px; 
            }
            .action-group {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            .btn-submit { 
                width: 100%; 
            }
            .sisa-cuti { 
                text-align: center; 
                width: 100%;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="image/namayayasan.png" alt="Logo YPD">
        <span>Yayasan Purba Danarta</span>
    </div>
    
    <nav>
        <ul>
            <li><a href="dashboardkaryawan.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="pengajuan_cuti.php">Pengajuan Cuti</a></li>
                    <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="pengajuan_khl.php">Pengajuan KHL</a></li>
                    <li><a href="riwayat_khl.php">Riwayat KHL</a></li>
                </ul>
            </li>
            <li><a href="profil.php">Profil <span class="profile-icon">👤</span></a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="welcome">Welcome, <?= htmlspecialchars($nama_karyawan) ?>!</div>

    <div class="form-card">
        <h2>Pengajuan Cuti</h2>
        <p>Ajukkan Permohonan Cuti Anda</p>
        
        <form action="" method="POST">
            
            <div class="form-group">
                <label for="no_karyawan">No. Kode Karyawan</label>
                <input type="text" id="no_karyawan" name="no_karyawan" class="input-text" value="" 
                       onclick="this.select()">
            </div>
            
            <div class="form-group">
                <label for="jenis_cuti">Jenis Cuti</label>
                <select id="jenis_cuti" name="jenis_cuti" class="input-select" required>
                    <option value="" disabled selected>Pilih Jenis Cuti</option>
                    <option value="Tahunan">Cuti Tahunan</option>
                    <option value="Sakit">Cuti Sakit</option>
                    <option value="Melahirkan">Cuti Melahirkan</option>
                    <option value="Lustrum">Cuti Lustrum</option>
                    <option value="Lainnya">Cuti Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_cuti">Tanggal Cuti</label>
                <input type="date" id="tanggal_cuti" name="tanggal_cuti" class="input-date" required>
            </div>
            
            <div class="action-group">
                <button type="submit" class="btn-submit">Masukkan</button>
                
                <div class="sisa-cuti">
                    <strong>Sisa Cuti</strong>
                    Cuti Tahunan: <?= $sisa_cuti_tahunan ?> hari<br>
                    Cuti Lustrum: <?= $sisa_cuti_lustrum ?> hari
                </div>
            </div>
        </form>
    </div>
</main>

</body>
</html>