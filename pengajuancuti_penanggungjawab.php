<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];
$divisi = $user['divisi'];
$jabatan = $user['jabatan'];
$role = $user['role'];

$sisa_cuti_tahunan = 0;
$sisa_cuti_lustrum = 0;

$query_sisa_cuti = "SELECT sisa_cuti_tahunan, sisa_cuti_lustrum FROM data_karyawan WHERE kode_karyawan = ?";
$stmt_sisa = mysqli_prepare($conn, $query_sisa_cuti);

if($stmt_sisa) {
    mysqli_stmt_bind_param($stmt_sisa, "s", $nik);
    mysqli_stmt_execute($stmt_sisa);
    $result_sisa = mysqli_stmt_get_result($stmt_sisa);
    $sisa_cuti_data = mysqli_fetch_assoc($result_sisa);
    
    if ($sisa_cuti_data) {
        $sisa_cuti_tahunan = $sisa_cuti_data['sisa_cuti_tahunan'];
        $sisa_cuti_lustrum = $sisa_cuti_data['sisa_cuti_lustrum'];
    }
    mysqli_stmt_close($stmt_sisa);
}
if (empty($divisi) || empty($jabatan)) {
    $query_karyawan = "SELECT divisi, jabatan FROM data_karyawan WHERE kode_karyawan = ?";
    $stmt = mysqli_prepare($conn, $query_karyawan);
    mysqli_stmt_bind_param($stmt, "s", $nik);
    mysqli_stmt_execute($stmt);
    $result_karyawan = mysqli_stmt_get_result($stmt);
    $karyawan_detail = mysqli_fetch_assoc($result_karyawan);
    
    if ($karyawan_detail) {
        $divisi = $karyawan_detail['divisi'];
        $jabatan = $karyawan_detail['jabatan'];
        $_SESSION['user']['divisi'] = $divisi;
        $_SESSION['user']['jabatan'] = $jabatan;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Cuti - Penanggung Jawab</title>
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
            background: linear-gradient(180deg, var(--primary-color) 0%, #a29bb8 100%); 
            min-height: 100vh; 
            color: var(--text-color-light); 
            padding-bottom: 40px; 
        }
        
        header { 
            background: var(--card-bg); 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
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
            width: 140px; 
            height: 50px; 
            object-fit: contain; 
        }
        
        nav ul { 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            gap: 30px; 
        }
        
        nav li { 
            position: relative; 
        }
        
        nav a { 
            text-decoration: none; 
            color: var(--text-color-dark); 
            font-weight: 600; 
            padding: 8px 4px; 
            display: block; 
        }
        
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
        
        nav li:hover > ul { 
            display: block; 
        }
        
        nav li ul li a { 
            color: var(--text-color-dark); 
            font-weight: 400; 
            white-space: nowrap; 
            padding: 5px 20px; 
        }
        
        main { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        
        .heading-section h1 { 
            font-size: 2.5rem; 
            margin: 0; 
            color: #fff;
        }
        
        .heading-section p { 
            font-size: 1.1rem; 
            margin-top: 5px; 
            opacity: 0.9; 
            margin-bottom: 30px; 
            color: #fff;
        }
        
        .form-container {
            background: var(--card-bg);
            color: var(--text-color-dark);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 5px 20px var(--shadow-light);
            margin-bottom: 30px;
        }
        
        h2 {
            text-align: center;
            font-size: 22px;
            color: #2e1f4f;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin: 16px 0 6px;
            color: #222;
        }
        
        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }
        
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        button {
            display: block;
            margin-top: 25px;
            padding: 12px;
            background-color: #4a3f81;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            width: 100%;
        }
        
        button:hover {
            background-color: #3a3162;
        }
        
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
        
        .user-info {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4a3f81;
        }
        
        .user-info p {
            margin: 8px 0;
            font-size: 14px;
            color: #333;
        }
        
        .user-info strong {
            color: #4a3f81;
        }

        .info-message {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            text-align: center;
        }

        .form-note {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #ffeaa7;
            font-size: 14px;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }
        
        .conditional-input {
            display: none;
            margin-top: 10px;
            animation: fadeIn 0.3s ease-in;
        }
        
        .conditional-input.show {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .date-range-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .date-range-container input {
            flex: 1;
        }
        
        .date-range-container span {
            font-weight: bold;
            color: #4a3f81;
        }
        
        small {
            display:block;
            margin-top:5px;
            color:#666;
            font-size:12px;
        }
        
        .file-input-container {
            margin-top: 5px;
        }
        
        .file-input-container input[type="file"] {
            padding: 8px;
            border: 1px dashed #ccc;
            background-color: #f9f9f9;
            border-radius: 8px;
            width: 100%;
        }
        
        .max-days-info {
            color: #e74c3c;
            font-weight: 600;
            font-size: 12px;
            margin-top: 5px;
            padding: 5px;
            background-color: #fdf2f2;
            border-radius: 4px;
            border-left: 3px solid #e74c3c;
        }
        
        .sisa-cuti-info {
            color: #2e7d32;
            font-weight: 600;
            font-size: 12px;
            margin-top: 5px;
            padding: 5px;
            background-color: #f1f8e9;
            border-radius: 4px;
            border-left: 3px solid #2e7d32;
        }
        
        .warning-message {
            color: #e65100;
            font-weight: 600;
            font-size: 12px;
            margin-top: 5px;
            padding: 5px;
            background-color: #fff3e0;
            border-radius: 4px;
            border-left: 3px solid #e65100;
        }

        .file-required {
            border: 1px solid #e74c3c !important;
            background-color: #fdf2f2 !important;
        }

        .holiday-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
            text-align: center;
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
                <li><a href="dashboard_penanggungjawab.php">Beranda</a></li>
                <li><a href="#">Cuti ▾</a>
                    <ul>
                        <li><a href="persetujuancuti_penanggungjawab.php">Persetujuan Cuti</a></li>
                        <li><a href="riwayatcuti_penanggungjawab.php">Riwayat Cuti Divisi</a></li>
                        <li><a href="pengajuancuti_penanggungjawab.php">Ajukan Cuti Pribadi</a></li>
                        <li><a href="kalender_cuti_penanggungjawab.php">Kalender Cuti Divisi</a></li>
                        <li><a href="riwayat_cuti_pribadi_penanggungjawab.php">Riwayat Cuti Pribadi</a></li>
                    </ul>
                </li>
                <li><a href="#">KHL ▾</a>
                    <ul>
                        <li><a href="persetujuankhl_penanggungjawab.php">Persetujuan KHL</a></li>
                        <li><a href="riwayatkhl_penanggungjawab.php">Riwayat KHL Divisi</a></li>
                        <li><a href="pengajuankhl_penanggungjawab.php">Ajukan KHL Pribadi</a></li>
                        <li><a href="kalender_khl_penanggungjawab.php">Kalender KHL Divisi</a></li>
                        <li><a href="riwayat_khl_pribadi_penanggungjawab.php">Riwayat KHL Pribadi</a></li>
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
        <div class="heading-section">
            <h1>Pengajuan Cuti Pribadi</h1>
            <p>Formulir ini digunakan untuk mengajukan cuti atas nama Anda sendiri.</p>
        </div>

        <div class="form-container">
            <h2>Pengajuan Cuti - Penanggung Jawab</h2>
            
            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'success') {
                    $success_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Pengajuan cuti berhasil dikirim!';
                    echo '<div class="success-message">' . $success_message . '</div>';
                } elseif ($_GET['status'] == 'error') {
                    $error_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Terjadi kesalahan. Silakan coba lagi.';
                    echo '<div class="error-message">' . $error_message . '</div>';
                } elseif ($_GET['status'] == 'info') {
                    $info_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Informasi';
                    echo '<div class="info-message">' . $info_message . '</div>';
                }
            }
            ?>

            <div class="user-info">
                <p><strong>Kode Karyawan:</strong> <?php echo htmlspecialchars($nik); ?></p>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_lengkap); ?></p>
                <p><strong>Divisi:</strong> <?php echo htmlspecialchars($divisi); ?></p>
                <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($jabatan); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $role))); ?></p>
                <p><strong>Sisa Cuti Tahunan:</strong> <?php echo htmlspecialchars($sisa_cuti_tahunan); ?> hari</p>
                <p><strong>Sisa Cuti Lustrum:</strong> <?php echo htmlspecialchars($sisa_cuti_lustrum); ?> hari</p>
            </div>

            <div class="form-note">
                <strong>Catatan:</strong> Sebagai Penanggung Jawab, pengajuan cuti Anda akan masuk dengan status <strong>Pending</strong> dan membutuhkan persetujuan dari atasan.
            </div>
            
            <form method="post" action="prosescuti_penanggungjawab.php" id="formCuti" enctype="multipart/form-data">
                
                <label>No. Induk Karyawan</label>
                <input type="text" name="nik" value="<?php echo htmlspecialchars($nik); ?>" readonly>

                <label>Nama Karyawan</label>
                <input type="text" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>

                <label>Divisi</label>
                <input type="text" value="<?php echo htmlspecialchars($divisi); ?>" readonly>

                <label class="required">Jenis Cuti</label>
                <select name="jenis_cuti" id="jenisCuti" required onchange="toggleConditionalInputs(); checkSisaCuti(); validateFileRequired();">
                    <option value="">Pilih Jenis Cuti</option>
                    <option value="Tahunan">Cuti Tahunan</option>
                    <option value="Lustrum">Cuti Lustrum</option>
                    <option value="Khusus">Cuti Khusus</option>
                    <option value="DiluarTanggungan">Cuti Diluar Tanggungan</option>
                    <option value="Sakit">Cuti Sakit</option>
                    <option value="Ibadah">Cuti Ibadah</option>
                </select>

                <div id="sisaCutiInfo" class="sisa-cuti-info" style="display: none;"></div>
                <div id="warningMessage" class="warning-message" style="display: none;"></div>

                <div id="khususInputContainer" class="conditional-input">
                    <label for="jenis_cuti_khusus" class="required">Jenis Cuti Khusus</label>
                    <select name="jenis_cuti_khusus" id="jenis_cuti_khusus" onchange="updateMaxDaysInfo()">
                        <option value="">Pilih Jenis Cuti Khusus</option>
                        <option value="Menikah" data-max-days="3">Menikah</option>
                        <option value="Pernikahan Anak/Pembatisan Anak/Pengkhitanan Anak" data-max-days="2">Pernikahan Anak/Pembatisan Anak/Pengkhitanan Anak</option>
                        <option value="Istri Melahirkan/Keguguran" data-max-days="2">Istri Melahirkan/Keguguran</option>
                        <option value="Suami istri, anak/menantu, orangtua/mertua meninggal" data-max-days="2">Suami istri, anak/menantu, orangtua/mertua meninggal</option>
                        <option value="Anggota keluarga dalam satu rumah meninggal" data-max-days="1">Anggota keluarga dalam satu rumah meninggal</option>
                        <option value="Pemeriksaan Kesehatan/Pindah Rumah" data-max-days="1">Pemeriksaan Kesehatan/Pindah Rumah</option>
                    </select>
                    <div id="maxDaysInfo" class="max-days-info" style="display: none;"></div>
                </div>

                <div id="sakitInputContainer" class="conditional-input">
                    <label for="bukti_surat_dokter" class="required">Bukti Surat Keterangan Dokter</label>
                    <div class="file-input-container">
                        <input type="file" name="bukti_surat_dokter" id="bukti_surat_dokter" accept="image/*,.pdf" onchange="validateFileUpload(this)">
                    </div>
                    <small>Format: JPG, PNG, atau PDF (maks. 5MB)</small>
                    <div id="fileError" class="error-message" style="display: none; margin-top: 5px; font-size: 12px; padding: 8px;"></div>
                    <div id="fileSuccess" class="success-message" style="display: none; margin-top: 5px; font-size: 12px; padding: 8px;"></div>
                </div>

                <label class="required">Periode Cuti</label>
                <div class="date-range-container">
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" min="<?php echo date('Y-m-d'); ?>" required onchange="validateSelectedDates(); updateTanggalAkhir(); validateMaxDays(); checkSisaCuti();">
                    <span>s/d</span>
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" min="<?php echo date('Y-m-d'); ?>" required onchange="validateSelectedDates(); validateMaxDays(); checkSisaCuti();">
                </div>
                <small>Untuk cuti 1 hari, isi tanggal yang sama pada kedua kolom</small>
                <div id="dateError" class="error-message" style="display: none; margin-top: 10px; margin-bottom: 10px;"></div>

                <div id="totalHariInfo" class="sisa-cuti-info" style="display: none;"></div>

                <label class="required">Alasan Cuti</label>
                <textarea name="alasan_cuti" id="alasan_cuti" placeholder="Tuliskan alasan cuti Anda..." required></textarea>

                <button type="submit" id="submitButton">Ajukan Cuti</button>
            </form>
        </div>
    </main>

    <script>
        let currentMaxDays = 0;
        const sisaCutiTahunan = <?php echo $sisa_cuti_tahunan; ?>;
        const sisaCutiLustrum = <?php echo $sisa_cuti_lustrum; ?>;

        const fixedHolidays = [
            '01-01', 
            '08-17', 
            '12-25'  
        ];

        function isHoliday(dateString) {
            const date = new Date(dateString);
            const monthDay = `${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            return fixedHolidays.includes(monthDay);
        }

        function isWeekend(dateString) {
            const date = new Date(dateString);
            const dayOfWeek = date.getDay();
            return dayOfWeek === 0 || dayOfWeek === 6; 
        }

        function validateSelectedDates() {
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalAkhir = document.getElementById('tanggal_akhir');
            const errorDiv = document.getElementById('dateError');
            
            errorDiv.style.display = 'none';
            
            if (tanggalMulai.value) {
                if (isHoliday(tanggalMulai.value)) {
                    showDateError(`Tanggal ${formatDate(tanggalMulai.value)} adalah hari libur nasional. Silakan pilih tanggal lain.`);
                    tanggalMulai.value = '';
                    return false;
                }
                if (isWeekend(tanggalMulai.value)) {
                    showDateError(`Tanggal ${formatDate(tanggalMulai.value)} adalah hari weekend. Silakan pilih tanggal weekday (Senin-Jumat).`);
                    tanggalMulai.value = '';
                    return false;
                }
            }
            
            if (tanggalAkhir.value) {
                if (isHoliday(tanggalAkhir.value)) {
                    showDateError(`Tanggal ${formatDate(tanggalAkhir.value)} adalah hari libur nasional. Silakan pilih tanggal lain.`);
                    tanggalAkhir.value = '';
                    return false;
                }
                if (isWeekend(tanggalAkhir.value)) {
                    showDateError(`Tanggal ${formatDate(tanggalAkhir.value)} adalah hari weekend. Silakan pilih tanggal weekday (Senin-Jumat).`);
                    tanggalAkhir.value = '';
                    return false;
                }
            }
            
            if (tanggalMulai.value && tanggalAkhir.value) {
                const startDate = new Date(tanggalMulai.value);
                const endDate = new Date(tanggalAkhir.value);
                const currentDate = new Date(startDate);
                const holidayDates = [];
                const weekendDates = [];
                
                while (currentDate <= endDate) {
                    const dateString = currentDate.toISOString().split('T')[0];
                    if (isHoliday(dateString)) {
                        holidayDates.push(new Date(currentDate));
                    }
                    if (isWeekend(dateString)) {
                        weekendDates.push(new Date(currentDate));
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                
                if (holidayDates.length > 0 || weekendDates.length > 0) {
                    let warningMessage = "Periode cuti Anda mengandung: ";
                    const warnings = [];
                    
                    if (holidayDates.length > 0) {
                        const holidayList = holidayDates.map(date => formatDate(date)).join(', ');
                        warnings.push(`hari libur nasional (${holidayList})`);
                    }
                    
                    if (weekendDates.length > 0) {
                        const weekendList = weekendDates.map(date => formatDate(date)).join(', ');
                        warnings.push(`hari weekend (${weekendList})`);
                    }
                    
                    warningMessage += warnings.join(' dan ') + ". Hari-hari tersebut tidak dihitung sebagai hari cuti.";
                    showDateError(warningMessage, 'warning');
                }
            }
            
            return true;
        }

        function showDateError(message, type = 'error') {
            const errorDiv = document.getElementById('dateError');
            errorDiv.innerHTML = message;
            errorDiv.style.display = 'block';
            if (type === 'warning') {
                errorDiv.className = 'holiday-warning';
            } else {
                errorDiv.className = 'error-message';
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }

        function toggleConditionalInputs() {
            const jenisCutiSelect = document.getElementById('jenisCuti');
            const khususInputContainer = document.getElementById('khususInputContainer');
            const khususInput = document.getElementById('jenis_cuti_khusus');
            const sakitInputContainer = document.getElementById('sakitInputContainer');
            const sakitInput = document.getElementById('bukti_surat_dokter');
            const maxDaysInfo = document.getElementById('maxDaysInfo');
            
            khususInputContainer.classList.remove('show');
            khususInput.required = false;
            sakitInputContainer.classList.remove('show');
            sakitInput.required = false;
            maxDaysInfo.style.display = 'none';
            currentMaxDays = 0;
            
            document.getElementById('fileError').style.display = 'none';
            document.getElementById('fileSuccess').style.display = 'none';
            
            if (jenisCutiSelect.value === 'Khusus') {
                khususInputContainer.classList.add('show');
                khususInput.required = true;
            } else if (jenisCutiSelect.value === 'Sakit') {
                sakitInputContainer.classList.add('show');
                sakitInput.required = true;
                validateFileRequired();
            }
            
            checkSisaCuti();
        }

        function validateFileRequired() {
            const jenisCutiSelect = document.getElementById('jenisCuti');
            const fileInput = document.getElementById('bukti_surat_dokter');
            
            if (jenisCutiSelect.value === 'Sakit') {
                fileInput.required = true;
                fileInput.classList.add('file-required');
            } else {
                fileInput.required = false;
                fileInput.classList.remove('file-required');
            }
        }

        function validateFileUpload(input) {
            const fileError = document.getElementById('fileError');
            const fileSuccess = document.getElementById('fileSuccess');
            
            fileError.style.display = 'none';
            fileSuccess.style.display = 'none';
            
            if (input.files.length > 0) {
                const file = input.files[0];
                const fileSize = file.size / 1024 / 1024; 
                const fileType = file.type;
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                
                if (!allowedTypes.includes(fileType)) {
                    fileError.textContent = 'Format file tidak didukung. Harap unggah file JPG, PNG, atau PDF.';
                    fileError.style.display = 'block';
                    input.value = ''; 
                    return false;
                }
                
                if (fileSize > 5) {
                    fileError.textContent = 'Ukuran file terlalu besar. Maksimal 5MB.';
                    fileError.style.display = 'block';
                    input.value = ''; 
                    return false;
                }
                
                fileSuccess.textContent = 'File berhasil diunggah.';
                fileSuccess.style.display = 'block';
                return true;
            }
            
            return false;
        }

        function updateMaxDaysInfo() {
            const khususSelect = document.getElementById('jenis_cuti_khusus');
            const maxDaysInfo = document.getElementById('maxDaysInfo');
            const selectedOption = khususSelect.options[khususSelect.selectedIndex];
            
            if (selectedOption.value !== '') {
                currentMaxDays = parseInt(selectedOption.getAttribute('data-max-days'));
                maxDaysInfo.textContent = `Batas maksimal cuti: ${currentMaxDays} hari`;
                maxDaysInfo.style.display = 'block';
                
                validateMaxDays();
            } else {
                maxDaysInfo.style.display = 'none';
                currentMaxDays = 0;
            }
        }

        function updateTanggalAkhir() {
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalAkhir = document.getElementById('tanggal_akhir');
            
            if (tanggalMulai.value) {
                tanggalAkhir.min = tanggalMulai.value;
                
                if (tanggalAkhir.value && tanggalAkhir.value < tanggalMulai.value) {
                    tanggalAkhir.value = tanggalMulai.value;
                }
            }
            
            validateMaxDays();
            checkSisaCuti();
        }

        function validateMaxDays() {
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalAkhir = document.getElementById('tanggal_akhir');
            const maxDaysInfo = document.getElementById('maxDaysInfo');
            
            if (tanggalMulai.value && tanggalAkhir.value && currentMaxDays > 0) {
                const startDate = new Date(tanggalMulai.value);
                const endDate = new Date(tanggalAkhir.value);
                
                const timeDiff = endDate.getTime() - startDate.getTime();
                const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                
                if (dayDiff > currentMaxDays) {
                    maxDaysInfo.innerHTML = `Batas maksimal cuti: ${currentMaxDays} hari <span style="color: #e74c3c;">(Anda mengajukan ${dayDiff} hari - Melebihi batas!)</span>`;
                    maxDaysInfo.style.display = 'block';
                } else {
                    maxDaysInfo.innerHTML = `Batas maksimal cuti: ${currentMaxDays} hari (Anda mengajukan ${dayDiff} hari)`;
                    maxDaysInfo.style.display = 'block';
                }
            }
        }

        function calculateWorkingDays(startDate, endDate) {
            let count = 0;
            const current = new Date(startDate);
            const end = new Date(endDate);
            
            while (current <= end) {
                const dayOfWeek = current.getDay();
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                const isHolidayDate = isHoliday(current.toISOString().split('T')[0]);
                
                if (!isWeekend && !isHolidayDate) {
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }
            
            return count;
        }

        function checkSisaCuti() {
            const jenisCutiSelect = document.getElementById('jenisCuti');
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalAkhir = document.getElementById('tanggal_akhir');
            const sisaCutiInfo = document.getElementById('sisaCutiInfo');
            const warningMessage = document.getElementById('warningMessage');
            const totalHariInfo = document.getElementById('totalHariInfo');
            const submitButton = document.getElementById('submitButton');
            
            sisaCutiInfo.style.display = 'none';
            warningMessage.style.display = 'none';
            totalHariInfo.style.display = 'none';
            submitButton.disabled = false;
            
            if (jenisCutiSelect.value && tanggalMulai.value && tanggalAkhir.value) {
                const startDate = new Date(tanggalMulai.value);
                const endDate = new Date(tanggalAkhir.value);
                
                const timeDiff = endDate.getTime() - startDate.getTime();
                const totalDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                
                const workingDays = calculateWorkingDays(startDate, endDate);
                
                totalHariInfo.innerHTML = `Total hari cuti: ${totalDays} hari (${workingDays} hari kerja)`;
                totalHariInfo.style.display = 'block';
                
                if (jenisCutiSelect.value === 'Tahunan') {
                    sisaCutiInfo.innerHTML = `Sisa cuti tahunan Anda: ${sisaCutiTahunan} hari`;
                    sisaCutiInfo.style.display = 'block';
                    
                    if (workingDays > sisaCutiTahunan) {
                        warningMessage.innerHTML = `PERINGATAN: Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti tahunan hanya ${sisaCutiTahunan} hari. Pengajuan tidak dapat diproses.`;
                        warningMessage.style.display = 'block';
                        submitButton.disabled = true;
                    } else if (workingDays > 0) {
                        sisaCutiInfo.innerHTML += ` - Setelah cuti ini, sisa cuti tahunan: ${sisaCutiTahunan - workingDays} hari`;
                    }
                    
                } else if (jenisCutiSelect.value === 'Lustrum') {
                    sisaCutiInfo.innerHTML = `Sisa cuti lustrum Anda: ${sisaCutiLustrum} hari`;
                    sisaCutiInfo.style.display = 'block';
                    
                    if (workingDays > sisaCutiLustrum) {
                        warningMessage.innerHTML = `PERINGATAN: Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti lustrum hanya ${sisaCutiLustrum} hari. Pengajuan tidak dapat diproses.`;
                        warningMessage.style.display = 'block';
                        submitButton.disabled = true;
                    } else if (workingDays > 0) {
                        sisaCutiInfo.innerHTML += ` - Setelah cuti ini, sisa cuti lustrum: ${sisaCutiLustrum - workingDays} hari`;
                    }
                }
            }
        }

        document.getElementById('formCuti').addEventListener('submit', function(e) {
            const jenisCutiSelect = document.getElementById('jenisCuti');
            const khususInput = document.getElementById('jenis_cuti_khusus');
            const sakitInput = document.getElementById('bukti_surat_dokter');
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalAkhir = document.getElementById('tanggal_akhir');
            
            if (tanggalMulai.value && (isWeekend(tanggalMulai.value) || isHoliday(tanggalMulai.value))) {
                e.preventDefault();
                alert('Tanggal mulai tidak boleh pada hari weekend atau hari libur nasional');
                tanggalMulai.focus();
                return;
            }
            
            if (tanggalAkhir.value && (isWeekend(tanggalAkhir.value) || isHoliday(tanggalAkhir.value))) {
                e.preventDefault();
                alert('Tanggal akhir tidak boleh pada hari weekend atau hari libur nasional');
                tanggalAkhir.focus();
                return;
            }
            
            if (jenisCutiSelect.value === 'Khusus' && !khususInput.value.trim()) {
                e.preventDefault();
                alert('Silakan pilih jenis cuti khusus');
                khususInput.focus();
                return;
            }
            
            if (jenisCutiSelect.value === 'Sakit') {
                if (!sakitInput.value) {
                    e.preventDefault();
                    alert('Untuk cuti sakit, wajib mengunggah bukti surat keterangan dokter');
                    sakitInput.focus();
                    return;
                }
                
                if (!validateFileUpload(sakitInput)) {
                    e.preventDefault();
                    alert('File surat dokter tidak valid. Pastikan format JPG, PNG, atau PDF dan ukuran maksimal 5MB.');
                    return;
                }
            }
            
            if (tanggalMulai.value && tanggalAkhir.value && tanggalAkhir.value < tanggalMulai.value) {
                e.preventDefault();
                alert('Tanggal akhir tidak boleh lebih awal dari tanggal mulai');
                tanggalAkhir.focus();
                return;
            }
            
            if (jenisCutiSelect.value === 'Khusus' && khususInput.value.trim()) {
                const selectedOption = khususInput.options[khususInput.selectedIndex];
                const maxDays = parseInt(selectedOption.getAttribute('data-max-days'));
                
                if (tanggalMulai.value && tanggalAkhir.value) {
                    const startDate = new Date(tanggalMulai.value);
                    const endDate = new Date(tanggalAkhir.value);
                    const timeDiff = endDate.getTime() - startDate.getTime();
                    const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                    
                    if (dayDiff > maxDays) {
                        e.preventDefault();
                        alert(`Jumlah hari cuti untuk ${selectedOption.text} maksimal ${maxDays} hari. Anda mengajukan ${dayDiff} hari.`);
                        return;
                    }
                }
            }
            
            if (jenisCutiSelect.value === 'Tahunan' || jenisCutiSelect.value === 'Lustrum') {
                if (tanggalMulai.value && tanggalAkhir.value) {
                    const startDate = new Date(tanggalMulai.value);
                    const endDate = new Date(tanggalAkhir.value);
                    const workingDays = calculateWorkingDays(startDate, endDate);
                    
                    if (jenisCutiSelect.value === 'Tahunan' && workingDays > sisaCutiTahunan) {
                        e.preventDefault();
                        alert(`Sisa cuti tahunan Anda tidak mencukupi. Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti hanya ${sisaCutiTahunan} hari.`);
                        return;
                    }
                    
                    if (jenisCutiSelect.value === 'Lustrum' && workingDays > sisaCutiLustrum) {
                        e.preventDefault();
                        alert(`Sisa cuti lustrum Anda tidak mencukupi. Anda mengajukan ${workingDays} hari kerja, tetapi sisa cuti hanya ${sisaCutiLustrum} hari.`);
                        return;
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            toggleConditionalInputs();
            
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_mulai').min = today;
            document.getElementById('tanggal_akhir').min = today;
        });
    </script>

    <?php
    if(isset($conn)) {
        mysqli_close($conn);
    }
    ?>
</body>
</html>