    <?php
    session_start();
    require_once 'config.php';

    // Cek apakah user sudah login sebagai penanggung jawab
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
        header("Location: login_penanggungjawab.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $kode_karyawan = mysqli_real_escape_string($conn, $_POST['kode_karyawan']);
        $divisi = mysqli_real_escape_string($conn, $_POST['divisi']);
        $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $proyek = mysqli_real_escape_string($conn, $_POST['proyek']);
        $tanggal_khl = mysqli_real_escape_string($conn, $_POST['tanggal_khl']);
        $jam_mulai_kerja = mysqli_real_escape_string($conn, $_POST['jam_mulai_kerja']);
        $jam_akhir_kerja = mysqli_real_escape_string($conn, $_POST['jam_akhir_kerja']);
        $tanggal_cuti_khl = mysqli_real_escape_string($conn, $_POST['tanggal_cuti_khl']);
        $jam_mulai_cuti_khl = mysqli_real_escape_string($conn, $_POST['jam_mulai_cuti_khl']);
        $jam_akhir_cuti_khl = mysqli_real_escape_string($conn, $_POST['jam_akhir_cuti_khl']);
        
        // Validasi data
        if (empty($proyek) || empty($tanggal_khl) || empty($jam_mulai_kerja) || empty($jam_akhir_kerja) || 
            empty($tanggal_cuti_khl) || empty($jam_mulai_cuti_khl) || empty($jam_akhir_cuti_khl)) {
            header("Location: pengajuankhl_penanggungjawab.php?status=error&message=Semua field harus diisi");
            exit();
        }
        
        // Validasi tanggal
        if ($tanggal_khl > $tanggal_cuti_khl) {
            header("Location: pengajuankhl_penanggungjawab.php?status=error&message=Tanggal KHL tidak boleh lebih besar dari Tanggal Cuti KHL");
            exit();
        }
        
        // Validasi jam kerja
        if ($jam_mulai_kerja >= $jam_akhir_kerja) {
            header("Location: pengajuankhl_penanggungjawab.php?status=error&message=Jam mulai kerja harus lebih awal dari jam akhir kerja");
            exit();
        }
        
        // Validasi jam cuti
        if ($jam_mulai_cuti_khl >= $jam_akhir_cuti_khl) {
            header("Location: pengajuankhl_penanggungjawab.php?status=error&message=Jam mulai cuti harus lebih awal dari jam akhir cuti");
            exit();
        }
        
        // Status default untuk penanggung jawab - sesuai dengan enum di database
        $status_khl = "pending";
        
        // Insert data ke database - SESUAI STRUKTUR TABEL data_pengajuan_khl
        $query_insert = "INSERT INTO data_pengajuan_khl (
            kode_karyawan, divisi, jabatan, role, proyek, 
            tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, 
            tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl, 
            status_khl
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        
        mysqli_stmt_bind_param($stmt_insert, "ssssssssssss", 
            $kode_karyawan, $divisi, $jabatan, $role, $proyek,
            $tanggal_khl, $jam_mulai_kerja, $jam_akhir_kerja,
            $tanggal_cuti_khl, $jam_mulai_cuti_khl, $jam_akhir_cuti_khl,
            $status_khl
        );
        
        if (mysqli_stmt_execute($stmt_insert)) {
            // Berhasil - ambil ID yang baru dibuat
            $id_khl = mysqli_insert_id($conn);
            header("Location: pengajuankhl_penanggungjawab.php?status=success&message=Pengajuan KHL berhasil dikirim dengan ID: KHL-" . $id_khl);
        } else {
            // Gagal
            $error_message = mysqli_error($conn);
            header("Location: pengajuankhl_penanggungjawab.php?status=error&message=Terjadi kesalahan sistem. Silakan coba lagi.");
        }
        
        mysqli_stmt_close($stmt_insert);
        mysqli_close($conn);
        exit();
        
    } else {
        // Jika bukan POST request, redirect ke form
        header("Location: pengajuankhl_penanggungjawab.php");
        exit();
    }
    ?>