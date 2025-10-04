<?php
session_start();
require 'config.php'; // Pastikan file koneksi di-include

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validasi input dasar
    if (empty($_POST['login_input']) || empty($_POST['password']) || empty($_POST['role'])) {
        header("Location: login_karyawan.php?error=missing");
        exit();
    }

    $login_input = $_POST['login_input'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // 2. Siapkan query untuk mengambil data pengguna
    $sql = "SELECT * FROM data_karyawan WHERE (kode_karyawan = ? OR nama_lengkap = ?) AND role = ? AND status_aktif = 'aktif'";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sss", $login_input, $login_input, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $db_user = $result->fetch_assoc();
        
        // 3. Verifikasi password (Ganti ini jika Anda sudah menggunakan password_hash)
        if ($password === $db_user['password']) {
            // Password cocok

            // 4. Set variabel sesi secara eksplisit dan aman
            $_SESSION['user'] = [
                'id_karyawan'   => $db_user['id_karyawan'],
                'kode_karyawan' => $db_user['kode_karyawan'],
                'nama_lengkap'  => $db_user['nama_lengkap'],
                'divisi'        => $db_user['divisi'],
                'role'          => $db_user['role'],
                'email'         => $db_user['email'],
                'jabatan'       => $db_user['jabatan']
            ];
            
            // 5. Arahkan ke dashboard
            header("Location: dashboard.php");
            exit();
        }
    }

    // Jika sampai sini, berarti login gagal
    header("Location: login_karyawan.php?error=invalid");
    exit();

    $stmt->close();
    $conn->close();
} else {
    // Jika halaman diakses tanpa metode POST
    header("Location: login_karyawan.php");
    exit();
}
?>