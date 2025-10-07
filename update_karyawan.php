<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id_karyawan = $_POST['id_karyawan'] ?? '';
    $kode_karyawan = $_POST['kode_karyawan'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $divisi = $_POST['divisi'] ?? '';
    $role = $_POST['role'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $status_aktif = $_POST['status_aktif'] ?? 'aktif';
    
    // Validasi data wajib
    if (empty($kode_karyawan) || empty($nama_lengkap) || empty($email) || empty($password) || 
        empty($jabatan) || empty($divisi) || empty($role)) {
        $_SESSION['error_message'] = "Semua field wajib harus diisi!";
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    // Cek apakah kode karyawan sudah digunakan oleh orang lain (kecuali diri sendiri)
    $check_sql = "SELECT id_karyawan FROM data_karyawan WHERE kode_karyawan = ? AND id_karyawan != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $kode_karyawan, $id_karyawan);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Kode karyawan sudah digunakan oleh karyawan lain!";
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    // Cek apakah email sudah digunakan oleh orang lain (kecuali diri sendiri)
    $check_email_sql = "SELECT id_karyawan FROM data_karyawan WHERE email = ? AND id_karyawan != ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("si", $email, $id_karyawan);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    
    if ($check_email_result->num_rows > 0) {
        $_SESSION['error_message'] = "Email sudah digunakan oleh karyawan lain!";
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    // Update data di database
    $sql = "UPDATE data_karyawan SET 
            kode_karyawan = ?, 
            nama_lengkap = ?, 
            email = ?, 
            password = ?, 
            jabatan = ?, 
            divisi = ?, 
            role = ?, 
            no_telp = ?, 
            status_aktif = ? 
            WHERE id_karyawan = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", 
        $kode_karyawan, 
        $nama_lengkap, 
        $email, 
        $password, 
        $jabatan, 
        $divisi, 
        $role, 
        $no_telp, 
        $status_aktif,
        $id_karyawan
    );
    
    if ($stmt->execute()) {
        // Set pesan sukses
        $_SESSION['success_message'] = "Data karyawan berhasil diupdate!";
        
        // Redirect ke halaman data karyawan
        header("Location: data_karyawan.php");
        exit;
    } else {
        // Jika terjadi error
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengupdate data: " . $conn->error;
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    // Jika bukan method POST, redirect ke halaman data karyawan
    header("Location: data_karyawan.php");
    exit;
}
?>