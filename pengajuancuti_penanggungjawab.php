<?php
// FILE: pengajuancuti_penanggungjawab.php
$nama_pj = "Ria";
$kode_pj = "YPD-003";
$sisa_cuti_pj = 7;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Cuti Pribadi</title>
    <style>
        /* CSS diambil dan diadaptasi dari formcutikaryawan.php */
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
        }

        /* Header khusus Penanggung Jawab */
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

        /* Styling Form dari formcutikaryawan.php */
        main {
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
        }
        .form-container {
            background: var(--card-bg);
            color: var(--text-color-dark);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
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
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-container input[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
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
        .sisa-cuti-info {
            background-color: #e7f3ff;
            border-left: 5px solid #0d6efd;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
            font-weight: 500;
        }
        .manual-input {
            display: none;
        }
        .manual-input.show {
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
    <div class="form-container">
        <h2>Formulir Pengajuan Cuti Pribadi</h2>
        <p>Silakan isi formulir di bawah untuk mengajukan cuti.</p>

        <div class="sisa-cuti-info">
            Sisa Cuti Tahunan Anda: <strong><?= $sisa_cuti_pj ?> hari</strong>
        </div>

        <form id="formCuti" action="" method="POST">
            <label for="kode_karyawan">No. Kode Karyawan</label>
            <input type="text" id="kode_karyawan" name="kode_karyawan" value="<?= htmlspecialchars($kode_pj) ?>" readonly>

            <label for="nama_karyawan">Nama Karyawan</label>
            <input type="text" id="nama_karyawan" name="nama_karyawan" value="<?= htmlspecialchars($nama_pj) ?>" readonly>

            <label for="jenisCuti">Jenis Cuti</label>
            <select id="jenisCuti" name="jenis_cuti" onchange="toggleManualInput()" required>
                <option value="" disabled selected>Pilih Jenis Cuti</option>
                <option value="Tahunan">Cuti Tahunan</option>
                <option value="Sakit">Cuti Sakit (dengan surat dokter)</option>
                <option value="Penting">Cuti Alasan Penting</option>
                <option value="Lainnya">Lainnya</option>
            </select>

            <div id="manualInputContainer" class="manual-input">
                <label for="jenis_cuti_manual">Tulis Jenis Cuti Lainnya</label>
                <input type="text" name="jenis_cuti_manual" id="jenis_cuti_manual" placeholder="Contoh: Cuti Melahirkan">
            </div>
            
            <label for="tgl_mulai">Tanggal Mulai Cuti</label>
            <input type="date" id="tgl_mulai" name="tgl_mulai" required>

            <label for="tgl_selesai">Tanggal Selesai Cuti</label>
            <input type="date" id="tgl_selesai" name="tgl_selesai" required>

            <label for="jumlah">Jumlah Hari</label>
            <input type="number" id="jumlah" name="jumlah" readonly style="background-color: #e9ecef;">

            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="4" placeholder="Tuliskan alasan atau keterangan cuti Anda..." required></textarea>
            
            <button type="submit">Kirim Pengajuan ke Direktur</button>
        </form>
    </div>
</main>
<script>
    // Script untuk toggle input manual dari formcutikaryawan.php
    function toggleManualInput() {
        const jenisCutiSelect = document.getElementById('jenisCuti');
        const manualInputContainer = document.getElementById('manualInputContainer');
        const manualInput = document.getElementById('jenis_cuti_manual');
        
        if (jenisCutiSelect.value === 'Lainnya') {
            manualInputContainer.classList.add('show');
            manualInput.required = true;
        } else {
            manualInputContainer.classList.remove('show');
            manualInput.required = false;
            manualInput.value = ''; // Kosongkan input jika pilihan lain dipilih
        }
    }

    // Script untuk hitung jumlah hari dari pengajuancuti_penanggungjawab.php
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const jumlahHari = document.getElementById('jumlah');

    function hitungJumlahHari() {
        if (tglMulai.value && tglSelesai.value) {
            const mulai = new Date(tglMulai.value);
            const selesai = new Date(tglSelesai.value);
            
            if (selesai >= mulai) {
                const diffTime = Math.abs(selesai - mulai);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                jumlahHari.value = diffDays;
            } else {
                jumlahHari.value = 0; // Reset jika tanggal selesai lebih awal
            }
        }
    }

    tglMulai.addEventListener('change', hitungJumlahHari);
    tglSelesai.addEventListener('change', hitungJumlahHari);
</script>
</body>
</html>