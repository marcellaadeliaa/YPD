<?php
session_start();
require 'config.php';

// Simulasi user direksi (untuk keperluan development)
// Dalam produksi, bagian ini harus diganti dengan sistem login yang sesungguhnya
$_SESSION['user'] = [
    'role' => 'direktur',
    'nama_lengkap' => 'Pico',
    'kode_karyawan' => 'YPD001'
];

$user = $_SESSION['user'];
$nama_direktur = $user['nama_lengkap'];

// Ambil pesan notifikasi
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);
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
      
      /* Notifikasi */
      .notification {
          padding: 15px;
          margin-bottom: 20px;
          border-radius: 8px;
          font-weight: 500;
          text-align: center;
      }
      
      .success {
          background-color: #d4edda;
          color: #155724;
          border: 1px solid #c3e6cb;
      }
      
      .error {
          background-color: #f8d7da;
          color: #721c24;
          border: 1px solid #f5c6cb;
      }
      
      .info-box {
          background: #e7f3ff;
          padding: 15px;
          border-radius: 10px;
          margin-bottom: 20px;
          border-left: 4px solid #2196F3;
          color: var(--text-color-dark);
      }
      
      .small-text {
          font-size: 0.85rem;
          color: #666;
          margin-top: -15px;
          margin-bottom: 20px;
          display: block;
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
        
        <!-- Info Box -->
        <div class="info-box">
            <strong>Informasi:</strong> Pengajuan KHL oleh Direktur akan langsung disetujui dan masuk ke riwayat.
        </div>
        
        <!-- Notifikasi -->
        <?php if (!empty($success)): ?>
            <div class="notification success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="notification error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="proses_pengajuan_khl.php" method="POST">
            <label for="kode_karyawan">Kode Karyawan</label>
            <input type="text" id="kode_karyawan" name="kode_karyawan" placeholder="Contoh: YPD001" required>
            <span class="small-text">Masukkan kode karyawan yang valid (contoh: YPD001, YPD013, dll)</span>

            <label for="proyek">Proyek</label>
            <input type="text" name="proyek" id="proyek" placeholder="Contoh: Proyek Sistem Baru" required>

            <label for="tanggal_mulai">Tanggal Mulai KHL</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" required>

            <label for="tanggal_akhir">Tanggal Akhir KHL (Opsional)</label>
            <input type="date" name="tanggal_akhir" id="tanggal_akhir">
            <span class="small-text">*Jika tidak diisi, akan menggunakan tanggal mulai</span>

            <label for="jam_mulai_normal">Jam Mulai Kerja (Normal)</label>
            <input type="time" name="jam_mulai_normal" id="jam_mulai_normal" required>

            <label for="jam_akhir_normal">Jam Akhir Kerja (Normal)</label>
            <input type="time" name="jam_akhir_normal" id="jam_akhir_normal" required>

            <label for="jam_mulai_libur">Jam Mulai Libur / Cuti Parsial</label>
            <input type="time" name="jam_mulai_libur" id="jam_mulai_libur">

            <label for="jam_akhir_libur">Jam Akhir Libur / Cuti Parsial</label>
            <input type="time" name="jam_akhir_libur" id="jam_akhir_libur">

            <button type="submit">Ajukan KHL</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="riwayat_khl_direktur.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                📋 Lihat Riwayat KHL
            </a>
        </div>
    </div>
</main>

<script>
    // Set tanggal minimum ke hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_mulai').min = today;
    document.getElementById('tanggal_akhir').min = today;
    
    // Validasi form sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const kodeKaryawan = document.getElementById('kode_karyawan').value.trim();
        const tanggalMulai = document.getElementById('tanggal_mulai').value;
        const tanggalAkhir = document.getElementById('tanggal_akhir').value;
        const jamMulaiNormal = document.getElementById('jam_mulai_normal').value;
        const jamAkhirNormal = document.getElementById('jam_akhir_normal').value;
        
        // Validasi kode karyawan
        if (!kodeKaryawan) {
            e.preventDefault();
            alert('Harap isi kode karyawan');
            document.getElementById('kode_karyawan').focus();
            return false;
        }
        
        // Validasi format kode karyawan (opsional)
        if (!kodeKaryawan.match(/^YPD\d{3}$/i)) {
            if (!confirm('Format kode karyawan mungkin tidak sesuai. Kode biasanya berupa "YPD" diikuti 3 angka (contoh: YPD001). Lanjutkan?')) {
                e.preventDefault();
                document.getElementById('kode_karyawan').focus();
                return false;
            }
        }
        
        if (!tanggalMulai) {
            e.preventDefault();
            alert('Harap isi tanggal mulai KHL');
            return false;
        }
        
        if (!jamMulaiNormal || !jamAkhirNormal) {
            e.preventDefault();
            alert('Harap isi jam mulai dan jam akhir kerja');
            return false;
        }
        
        if (tanggalAkhir && new Date(tanggalAkhir) < new Date(tanggalMulai)) {
            e.preventDefault();
            alert('Tanggal akhir tidak boleh sebelum tanggal mulai');
            return false;
        }
        
        return confirm('Apakah Anda yakin ingin mengajukan KHL ini? Pengajuan akan langsung disetujui.');
    });
</script>
</body>
</html>