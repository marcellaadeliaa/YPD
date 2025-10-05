<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'direksi') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk mengajukan KHL";
    header("Location: pengajuan_khl_direktur.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_karyawan = trim($_POST['kode_karyawan'] ?? '');
    
    // SIMPLE DEBUG - Langsung cek di database
    $check_query = "SELECT * FROM data_karyawan WHERE kode_karyawan = '$kode_karyawan'";
    $result = $conn->query($check_query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Proses insert KHL
        $proyek = trim($_POST['proyek'] ?? '');
        $tanggal_khl = $_POST['tanggal_mulai'] ?? '';
        $jam_mulai_kerja = $_POST['jam_mulai_normal'] ?? '';
        $jam_akhir_kerja = $_POST['jam_akhir_normal'] ?? '';
        $tanggal_cuti_khl = $_POST['tanggal_akhir'] ?? $tanggal_khl;
        $jam_mulai_cuti_khl = $_POST['jam_mulai_libur'] ?? '';
        $jam_akhir_cuti_khl = $_POST['jam_akhir_libur'] ?? '';
        
        $insert_query = "INSERT INTO data_pengajuan_khl (
            kode_karyawan, divisi, role, proyek, 
            tanggal_khl, jam_mulai_kerja, jam_akhir_kerja,
            tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl,
            status_khl, alasan_penolakan, created_at
        ) VALUES (
            '{$data['kode_karyawan']}', 
            '{$data['divisi']}', 
            '{$data['role']}', 
            '$proyek',
            '$tanggal_khl', '$jam_mulai_kerja', '$jam_akhir_kerja',
            '$tanggal_cuti_khl', '$jam_mulai_cuti_khl', '$jam_akhir_cuti_khl',
            'disetujui', NULL, NOW()
        )";
        
        if ($conn->query($insert_query)) {
            $_SESSION['success'] = "KHL berhasil diajukan untuk " . $data['nama_lengkap'] . " dan langsung disetujui!";
            header("Location: riwayat_khl_direktur.php?status=disetujui");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menyimpan: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Kode karyawan '$kode_karyawan' tidak ditemukan. Error: " . $conn->error;
    }
    
    header("Location: pengajuan_khl_direktur.php");
    exit();
}
?>