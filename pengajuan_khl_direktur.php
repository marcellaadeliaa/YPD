<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direktur') {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];
$divisi = $user['divisi'];
$jabatan = $user['jabatan'];
$role = $user['role'];

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
    <title>Pengajuan KHL - Direktur</title>
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
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-sizing: border-box;
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
        
        .auto-approved-badge {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            font-size: 16px;
        }
        
        .time-container {
            display: flex;
            gap: 10px;
        }
        
        .time-container select {
            flex: 1;
        }
        
        small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }

        /* Tambahan untuk validasi tanggal */
        .holiday-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
            text-align: center;
        }

        .date-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-top: 10px;
            margin-bottom: 10px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
    </style>
</head>
<body>
   <header>
    <div class="logo"><img src="image/namayayasan.png" alt="Logo"><span>Yayasan Purba Danarta</span></div>
    <nav>
        <ul>
            <li><a href="dashboarddirektur.php">Beranda</a></li>
            <li><a href="#">Cuti ▾</a>
                <ul>
                    <li><a href="persetujuan_cuti_direktur.php">Persetujuan Cuti</a></li>
                    <li><a href="riwayat_cuti_direktur.php">Riwayat Semua Cuti</a></li>
                    <li><a href="riwayat_cuti_pribadi_direktur.php">Riwayat Cuti Pribadi</a></li>
                    <li><a href="kalender_cuti_direktur.php">Kalender Cuti</a></li>
                </ul>
            </li>
            <li><a href="#">KHL ▾</a>
                <ul>
                    <li><a href="persetujuan_khl_direktur.php">Persetujuan KHL</a></li>
                    <li><a href="riwayat_khl_direktur.php">Riwayat Semua KHL</a></li>
                    <li><a href="riwayat_khl_pribadi_direktur.php">Riwayat KHL Pribadi</a></li>
                    <li><a href="kalender_khl_direktur.php">Kalender KHL</a></li>
                </ul>
            </li>
            <li><a href="#">Karyawan ▾</a>
                <ul>
                    <li><a href="data_karyawan_direktur.php">Data Karyawan</a></li>
                </ul>
            </li>
            <li><a href="#">Pelamar ▾</a>
                <ul>
                    <li><a href="riwayat_pelamar_direktur.php">Riwayat Pelamar</a></li>
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
        <div class="heading-section">
            <h1>Pengajuan Kerja Hari Libur (KHL) Pribadi</h1>
            <p>Formulir ini digunakan untuk mengajukan KHL atas nama Anda sendiri.</p>
        </div>

        <div class="form-container">
            <h2>Pengajuan KHL - Direktur</h2>
            
            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'success') {
                    $success_message = isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 'Pengajuan KHL berhasil dikirim!';
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
            </div>
            
            <form method="post" action="prosespengajuan_khldirektur.php" id="formKHL">

                <label>No. Kode Karyawan</label>
                <input type="text" name="nik" value="<?php echo htmlspecialchars($nik); ?>" readonly>

                <label>Nama Karyawan</label>
                <input type="text" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>

                <label>Divisi</label>
                <input type="text" value="<?php echo htmlspecialchars($divisi); ?>" readonly>

                <label class="required">Proyek</label>
                <input type="text" name="proyek" placeholder="Masukkan nama proyek" required>
                <small>Contoh: Maintenance Server, Implementasi Sistem Baru, dll.</small>

                <label class="required">Tanggal KHL</label>
                <input type="date" name="tanggal_khl" id="tanggal_khl" min="<?php echo date('Y-m-d'); ?>" required onchange="validateKHLDate(); updateCutiKHLMinDate();">
                <small>Tanggal saat Anda akan bekerja di hari libur</small>
                <div id="khlDateError" class="date-error" style="display: none;"></div>

                <div class="time-container">
                    <div style="flex: 1;">
                        <label class="required">Jam Mulai Kerja</label>
                        <select name="jam_mulai_kerja" required>
                            <option value="">Pilih Jam Mulai</option>
                            <option value="08:00">08:00</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="required">Jam Akhir Kerja</label>
                        <select name="jam_akhir_kerja" required>
                            <option value="">Pilih Jam Akhir</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                            <option value="19:00">19:00</option>
                        </select>
                    </div>
                </div>

                <label class="required">Tanggal Cuti KHL</label>
                <input type="date" name="tanggal_cuti_khl" id="tanggal_cuti_khl" min="<?php echo date('Y-m-d'); ?>" required onchange="validateCutiKHLDate();">
                <small>Tanggal pengganti cuti yang akan diambil</small>
                <div id="cutiKHLDateError" class="date-error" style="display: none;"></div>

                <div class="time-container">
                    <div style="flex: 1;">
                        <label class="required">Jam Mulai Cuti KHL</label>
                        <select name="jam_mulai_cuti_khl" required>
                            <option value="">Pilih Jam Mulai</option>
                            <option value="08:00">08:00</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="required">Jam Akhir Cuti KHL</label>
                        <select name="jam_akhir_cuti_khl" required>
                            <option value="">Pilih Jam Akhir</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                        </select>
                    </div>
                </div>

                <button type="submit">Ajukan KHL </button>
            </form>
        </div>
    </main>

    <script>
        // Daftar tanggal merah (format: MM-DD) - sama seperti di form cuti
        const fixedHolidays = [
            '01-01', // 1 Januari
            '08-17', // 17 Agustus
            '12-25'  // 25 Desember
        ];

        function isHoliday(dateString) {
            const date = new Date(dateString);
            const monthDay = `${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            return fixedHolidays.includes(monthDay);
        }

        function isWeekend(dateString) {
            const date = new Date(dateString);
            const dayOfWeek = date.getDay();
            return dayOfWeek === 0 || dayOfWeek === 6; // 0 = Minggu, 6 = Sabtu
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }

        function validateKHLDate() {
            const tanggalKHL = document.getElementById('tanggal_khl');
            const errorDiv = document.getElementById('khlDateError');
            
            errorDiv.style.display = 'none';
            
            if (tanggalKHL.value) {
                // Untuk KHL, tanggal harus weekend atau holiday (karena ini kerja hari libur)
                const isWeekendDay = isWeekend(tanggalKHL.value);
                const isHolidayDate = isHoliday(tanggalKHL.value);
                
                if (!isWeekendDay && !isHolidayDate) {
                    errorDiv.innerHTML = `PERINGATAN: Tanggal ${formatDate(tanggalKHL.value)} adalah hari kerja biasa. KHL seharusnya diajukan untuk hari libur (weekend atau hari libur nasional).`;
                    errorDiv.style.display = 'block';
                    errorDiv.className = 'holiday-warning';
                } else {
                    // Jika valid, tampilkan info
                    let infoMessage = `Tanggal ${formatDate(tanggalKHL.value)} adalah `;
                    if (isWeekendDay && isHolidayDate) {
                        infoMessage += "hari weekend dan hari libur nasional.";
                    } else if (isWeekendDay) {
                        infoMessage += "hari weekend.";
                    } else {
                        infoMessage += "hari libur nasional.";
                    }
                    errorDiv.innerHTML = infoMessage;
                    errorDiv.style.display = 'block';
                    errorDiv.className = 'info-message';
                }
            }
            
            return true;
        }

        function validateCutiKHLDate() {
            const tanggalCutiKHL = document.getElementById('tanggal_cuti_khl');
            const tanggalKHL = document.getElementById('tanggal_khl');
            const errorDiv = document.getElementById('cutiKHLDateError');
            
            errorDiv.style.display = 'none';
            
            if (tanggalCutiKHL.value) {
                // Validasi: Tanggal cuti KHL harus weekday dan bukan holiday
                if (isWeekend(tanggalCutiKHL.value)) {
                    errorDiv.innerHTML = `Tanggal ${formatDate(tanggalCutiKHL.value)} adalah hari weekend. Tanggal cuti KHL harus pada hari kerja (Senin-Jumat).`;
                    errorDiv.style.display = 'block';
                    errorDiv.className = 'error-message';
                    return false;
                }
                
                if (isHoliday(tanggalCutiKHL.value)) {
                    errorDiv.innerHTML = `Tanggal ${formatDate(tanggalCutiKHL.value)} adalah hari libur nasional. Tanggal cuti KHL harus pada hari kerja biasa.`;
                    errorDiv.style.display = 'block';
                    errorDiv.className = 'error-message';
                    return false;
                }
                
                // Validasi: Tanggal cuti KHL harus setelah tanggal KHL
                if (tanggalKHL.value && tanggalCutiKHL.value <= tanggalKHL.value) {
                    errorDiv.innerHTML = `Tanggal cuti KHL harus setelah tanggal KHL.`;
                    errorDiv.style.display = 'block';
                    errorDiv.className = 'error-message';
                    return false;
                }
            }
            
            return true;
        }

        function updateCutiKHLMinDate() {
            const tanggalKHL = document.getElementById('tanggal_khl');
            const tanggalCutiInput = document.getElementById('tanggal_cuti_khl');
            
            if (tanggalKHL.value) {
                const minDate = new Date(tanggalKHL.value);
                minDate.setDate(minDate.getDate() + 1);
                const minDateString = minDate.toISOString().split('T')[0];
                tanggalCutiInput.min = minDateString;
                
                if (tanggalCutiInput.value && tanggalCutiInput.value <= tanggalKHL.value) {
                    tanggalCutiInput.value = '';
                    document.getElementById('cutiKHLDateError').style.display = 'none';
                }
            }
        }

        document.getElementById('formKHL').addEventListener('submit', function(e) {
            const tanggalKHL = document.getElementById('tanggal_khl').value;
            const tanggalCutiKHL = document.getElementById('tanggal_cuti_khl').value;
            const jamMulaiKerja = document.querySelector('select[name="jam_mulai_kerja"]').value;
            const jamAkhirKerja = document.querySelector('select[name="jam_akhir_kerja"]').value;
            const jamMulaiCuti = document.querySelector('select[name="jam_mulai_cuti_khl"]').value;
            const jamAkhirCuti = document.querySelector('select[name="jam_akhir_cuti_khl"]').value;
            
            // Validasi weekend dan holiday untuk KHL
            if (tanggalKHL) {
                if (!isWeekend(tanggalKHL.value) && !isHoliday(tanggalKHL.value)) {
                    e.preventDefault();
                    alert('KHL hanya dapat diajukan untuk hari libur (weekend atau hari libur nasional)');
                    return;
                }
            }
            
            // Validasi weekend dan holiday untuk cuti KHL
            if (tanggalCutiKHL) {
                if (isWeekend(tanggalCutiKHL) || isHoliday(tanggalCutiKHL)) {
                    e.preventDefault();
                    alert('Tanggal cuti KHL harus pada hari kerja biasa (bukan weekend atau hari libur)');
                    return;
                }
            }
            
            if (tanggalKHL && tanggalCutiKHL && tanggalKHL >= tanggalCutiKHL) {
                e.preventDefault();
                alert('Tanggal Cuti KHL harus setelah Tanggal KHL');
                return;
            }
            
            if (jamMulaiKerja && jamAkhirKerja && jamMulaiKerja >= jamAkhirKerja) {
                e.preventDefault();
                alert('Jam mulai kerja harus lebih awal dari jam akhir kerja');
                return;
            }
            
            if (jamMulaiCuti && jamAkhirCuti && jamMulaiCuti >= jamAkhirCuti) {
                e.preventDefault();
                alert('Jam mulai cuti harus lebih awal dari jam akhir cuti');
                return;
            }
            
            if (!confirm('Apakah Anda yakin ingin mengajukan KHL? Pengajuan akan otomatis disetujui.')) {
                e.preventDefault();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_khl').min = today;
            document.getElementById('tanggal_cuti_khl').min = today;
        });
    </script>

    <?php
    if(isset($conn)) {
        mysqli_close($conn);
    }
    ?>
</body>
</html>