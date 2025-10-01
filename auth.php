<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = mysqli_real_escape_string($conn, $_POST['login_input']);
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Validasi input
    if (empty($login_input) || empty($password) || empty($role)) {
        header("Location: login_karyawan.php?error=missing");
        exit();
    }
    
    // Cari user berdasarkan kode_karyawan ATAU nama_lengkap dan role
    $query = "SELECT * FROM data_karyawan 
              WHERE (kode_karyawan = ? OR nama_lengkap = ?) 
              AND role = ? AND status_aktif = 'aktif'";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $login_input, $login_input, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Verify password (untuk testing, bandingkan langsung)
        // Dalam real project gunakan password_verify()
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user'] = [
                'id_karyawan' => $user['id_karyawan'],
                'kode_karyawan' => $user['kode_karyawan'],
                'nama_lengkap' => $user['nama_lengkap'],
                'jabatan' => $user['jabatan'],
                'divisi' => $user['divisi'],
                'role' => $user['role'],
                'no_telp' => $user['no_telp'],
                'sisa_cuti_tahunan' => $user['sisa_cuti_tahunan'],
                'sisa_cuti_lustrum' => $user['sisa_cuti_lustrum']
            ];
            
            // Redirect ke dashboard handler
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Jika gagal
    header("Location: login_karyawan.php?error=invalid");
    exit();
} else {
    header("Location: login_karyawan.php");
    exit();
}
?>