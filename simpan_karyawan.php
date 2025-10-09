<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_karyawan = $_POST['kode_karyawan'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $divisi = $_POST['divisi'] ?? '';
    $role = $_POST['role'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $status_aktif = $_POST['status_aktif'] ?? 'aktif';
    
    if (empty($kode_karyawan) || empty($nama_lengkap) || empty($email) || empty($password) || 
        empty($jabatan) || empty($divisi) || empty($role)) {
        $_SESSION['error_message'] = "Semua field wajib harus diisi!";
        header("Location: tambah_karyawan.php");
        exit;
    }
    
    $check_sql = "SELECT id_karyawan FROM data_karyawan WHERE kode_karyawan = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $kode_karyawan);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Kode karyawan sudah digunakan!";
        header("Location: tambah_karyawan.php");
        exit;
    }
    
    $check_email_sql = "SELECT id_karyawan FROM data_karyawan WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    
    if ($check_email_result->num_rows > 0) {
        $_SESSION['error_message'] = "Email sudah digunakan!";
        header("Location: tambah_karyawan.php");
        exit;
    }

    $plain_password = $password;
    
    $sql = "INSERT INTO data_karyawan 
            (kode_karyawan, nama_lengkap, email, password, jabatan, divisi, role, no_telp, status_aktif) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", 
        $kode_karyawan, 
        $nama_lengkap, 
        $email, 
        $plain_password,  
        $jabatan, 
        $divisi, 
        $role, 
        $no_telp, 
        $status_aktif
    );
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data karyawan berhasil ditambahkan! Kode: $kode_karyawan, Password: $plain_password";
        
        header("Location: data_karyawan.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menyimpan data: " . $conn->error;
        header("Location: tambah_karyawan.php");
        exit;
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header("Location: tambah_karyawan.php");
    exit;
}
?>