<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login sebagai penanggung jawab
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'penanggung jawab') {
    header("Location: login_penanggungjawab.php");
    exit();
}

// Ambil data dari session
$user = $_SESSION['user'];
$nik = $user['kode_karyawan'];
$nama_lengkap = $user['nama_lengkap'];
$divisi = $user['divisi'];
$jabatan = $user['jabatan'];
$role = 'penanggung jawab'; // Set role secara eksplisit

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $jenis_cuti = mysqli_real_escape_string($conn, $_POST['jenis_cuti']);
    $jenis_cuti_khusus = isset($_POST['jenis_cuti_khusus']) ? mysqli_real_escape_string($conn, $_POST['jenis_cuti_khusus']) : '';
    $tanggal_mulai = mysqli_real_escape_string($conn, $_POST['tanggal_mulai']);
    $tanggal_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);
    $alasan_cuti = mysqli_real_escape_string($conn, $_POST['alasan_cuti']);
    
    // Format jenis cuti untuk database
    if ($jenis_cuti == 'Khusus' && !empty($jenis_cuti_khusus)) {
        $jenis_cuti_db = $jenis_cuti . ' - ' . $jenis_cuti_khusus;
    } else {
        $jenis_cuti_db = $jenis_cuti;
    }
    
    // Hitung jumlah hari cuti
    $start_date = new DateTime($tanggal_mulai);
    $end_date = new DateTime($tanggal_akhir);
    $interval = $start_date->diff($end_date);
    $jumlah_hari = $interval->days + 1; // +1 karena termasuk tanggal mulai
    
    // Validasi tanggal
    if ($tanggal_akhir < $tanggal_mulai) {
        header("Location: pengajuancuti_penanggungjawab.php?status=error&message=Tanggal akhir tidak boleh lebih awal dari tanggal mulai");
        exit();
    }
    
    // Validasi untuk cuti khusus
    if ($jenis_cuti == 'Khusus' && !empty($jenis_cuti_khusus)) {
        $max_days = 0;
        switch($jenis_cuti_khusus) {
            case 'Menikah':
                $max_days = 3;
                break;
            case 'Pernikahan Anak/Pembatisan Anak/Pengkhitanan Anak':
            case 'Istri Melahirkan/Keguguran':
            case 'Suami istri, anak/menantu, orangtua/mertua meninggal':
                $max_days = 2;
                break;
            case 'Anggota keluarga dalam satu rumah meninggal':
            case 'Pemeriksaan Kesehatan/Pindah Rumah':
                $max_days = 1;
                break;
        }
        
        if ($jumlah_hari > $max_days) {
            header("Location: pengajuancuti_penanggungjawab.php?status=error&message=Jumlah hari cuti untuk $jenis_cuti_khusus maksimal $max_days hari");
            exit();
        }
    }
    
    // Handle upload file untuk cuti sakit
    $nama_file_bukti = NULL;
    if ($jenis_cuti == 'Sakit' && isset($_FILES['bukti_surat_dokter']) && $_FILES['bukti_surat_dokter']['error'] == 0) {
        $file = $_FILES['bukti_surat_dokter'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        // Validasi file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            header("Location: pengajuancuti_penanggungjawab.php?status=error&message=Format file tidak didukung. Gunakan JPG, PNG, atau PDF");
            exit();
        }
        
        if ($file_size > 5 * 1024 * 1024) { // 5MB
            header("Location: pengajuancuti_penanggungjawab.php?status=error&message=Ukuran file terlalu besar. Maksimal 5MB");
            exit();
        }
        
        // Generate nama file unik
        $nama_file_bukti = 'surat_sakit_' . $nik . '_' . time() . '.' . $file_extension;
        $upload_path = 'uploads/surat_sakit/' . $nama_file_bukti;
        
        // Buat folder jika belum ada
        if (!is_dir('uploads/surat_sakit')) {
            mkdir('uploads/surat_sakit', 0777, true);
        }
        
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            header("Location: pengajuancuti_penanggungjawab.php?status=error&message=Gagal mengupload file bukti");
            exit();
        }
    } elseif ($jenis_cuti == 'Sakit') {
        header("Location: pengajuancuti_penanggungjawab.php?status=error&message=Bukti surat dokter wajib untuk cuti sakit");
        exit();
    }
    
    // Debug: Cek nilai variabel sebelum insert
    error_log("Inserting data: kode_karyawan=$nik, nama_karyawan=$nama_lengkap, divisi=$divisi, jabatan=$jabatan, role=$role, jenis_cuti=$jenis_cuti_db");
    
    // Insert data ke database sesuai struktur tabel data_pengajuan_cuti
    $query = "INSERT INTO data_pengajuan_cuti (
        kode_karyawan, 
        nama_karyawan, 
        divisi, 
        jabatan, 
        role, 
        jenis_cuti, 
        tanggal_mulai, 
        tanggal_akhir, 
        alasan, 
        file_surat_dokter, 
        status,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Persetujuan', NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt, 
            "ssssssssss", 
            $nik, 
            $nama_lengkap, 
            $divisi, 
            $jabatan, 
            $role, 
            $jenis_cuti_db, 
            $tanggal_mulai, 
            $tanggal_akhir, 
            $alasan_cuti, 
            $nama_file_bukti
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $last_id = mysqli_insert_id($conn);
            header("Location: pengajuancuti_penanggungjawab.php?status=success&message=Pengajuan cuti berhasil dikirim! ID Pengajuan: $last_id");
            exit();
        } else {
            // Hapus file yang sudah diupload jika insert gagal
            if (!empty($nama_file_bukti)) {
                @unlink('uploads/surat_sakit/' . $nama_file_bukti);
            }
            $error_message = "Gagal menyimpan data pengajuan cuti: " . mysqli_error($conn);
            error_log($error_message); // Log error untuk debugging
            header("Location: pengajuancuti_penanggungjawab.php?status=error&message=" . urlencode($error_message));
            exit();
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Terjadi kesalahan sistem: " . mysqli_error($conn);
        error_log($error_message); // Log error untuk debugging
        header("Location: pengajuancuti_penanggungjawab.php?status=error&message=" . urlencode($error_message));
        exit();
    }
    
} else {
    // Jika bukan POST request, redirect ke form
    header("Location: pengajuancuti_penanggungjawab.php");
    exit();
}

// Tutup koneksi
mysqli_close($conn);
?>