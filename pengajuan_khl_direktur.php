<?php
require 'config.php';

// Data Direktur (sementara hardcode, bisa ambil dari session)
$nama_direktur = "Pico";
$jabatan = "Direktur";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pengajuan KHL Direktur</title>
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
          background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%); 
          min-height: 100vh; 
          display: flex;
          flex-direction: column;
      }

      header { 
          background: var(--card-bg); 
          padding: 20px 40px; 
          display: flex; 
          justify-content: space-between; 
          align-items: center; 
          box-shadow: 0 4px 15px var(--shadow-light); 
      }
      .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: var(--text-color-dark); }
      .logo img { width: 50px; height: 50px; object-fit: contain; }
      nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 30px; }
      nav li { position: relative; }
      nav a { text-decoration: none; color: var(--text-color-dark); font-weight: 600; padding: 8px 4px; display: block; }
      nav li ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card-bg); padding: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px var(--shadow-light); min-width: 200px; z-index: 999; }
      nav li:hover > ul { display: block; }
      nav li ul li a { color: var(--text-color-dark); font-weight: 400; padding: 5px 20px; white-space: nowrap; }

      main {
          padding: 40px 20px;
          display: flex;
          justify-content: center;
          align-items: flex-start;
          flex-grow: 1;
      }
      .form-container {
          background: var(--card-bg);
          color: var(--text-color-dark);
          padding: 40px;
          border-radius: 20px;
          box-shadow: 0 8px 25px rgba(0,0,0,0.2);
          width: 100%;
          max-width: 600px;
      }
      .form-container h2 {
          text-align: center;
          margin-top: 0;
          margin-bottom: 10px;
          color: var(--primary-color);
      }
      .form-container p {
          text-align: center;
          margin-bottom: 30px;
          color: #666;
      }
      .form-container label {
          display: block;
          margin-bottom: 8px;
          font-weight: 600;
      }
      .form-container input,
      .form-container select {
          width: 100%;
          padding: 12px;
          margin-bottom: 20px;
          border-radius: 8px;
          border: 1px solid #ccc;
          box-sizing: border-box;
          font-size: 16px;
      }
      .form-container button {
          width: 100%;
          padding: 15px;
          background: var(--accent-color);
          color: white;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          font-size: 16px;
          font-weight: 600;
      }
      .form-container button:hover {
          background: #352d5c;
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
            <li><a href="#">Pelamar ▾</a> 
                <ul> 
                    <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li> 
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
    <div class="form-container">
        <h2>Formulir Pengajuan KHL Direktur</h2>
        <p>Silakan isi detail pengajuan kerja harian lepas.</p>

        <form action="proses_pengajuan_khl.php" method="POST">
            <label for="kode_karyawan">Kode Karyawan</label>
            <input type="text" id="kode_karyawan" name="kode_karyawan" placeholder="Contoh: YPD0001" required>

            <label for="proyek">Proyek</label>
            <input type="text" name="proyek" id="proyek" placeholder="Contoh: Proyek Sistem Baru" required>

            <label for="tanggal_mulai">Tanggal Mulai KHL</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" required>

            <label for="tanggal_akhir">Tanggal Akhir KHL (Opsional)</label>
            <input type="date" name="tanggal_akhir" id="tanggal_akhir">

            <label for="jam_mulai_normal">Jam Mulai Kerja (Normal)</label>
            <input type="time" name="jam_mulai_normal" id="jam_mulai_normal">

            <label for="jam_akhir_normal">Jam Akhir Kerja (Normal)</label>
            <input type="time" name="jam_akhir_normal" id="jam_akhir_normal">

            <label for="jam_mulai_libur">Jam Mulai Libur / Cuti Parsial</label>
            <input type="time" name="jam_mulai_libur" id="jam_mulai_libur">

            <label for="jam_akhir_libur">Jam Akhir Libur / Cuti Parsial</label>
            <input type="time" name="jam_akhir_libur" id="jam_akhir_libur">

            <button type="submit">Ajukan KHL</button>
        </form>
    </div>
</main>
</body>
</html>
