<?php
session_start();
require 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['login_input']) || empty($_POST['password']) || empty($_POST['role'])) {
        header("Location: login_karyawan.php?error=missing");
        exit();
    }

    $login_input = $_POST['login_input'];
    $password = $_POST['password'];
    $role = $_POST['role'];

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

        if ($password === $db_user['password']) {

            $_SESSION['user'] = [
                'id_karyawan'   => $db_user['id_karyawan'],
                'kode_karyawan' => $db_user['kode_karyawan'],
                'nama_lengkap'  => $db_user['nama_lengkap'],
                'divisi'        => $db_user['divisi'],
                'role'          => $db_user['role'],
                'email'         => $db_user['email'],
                'jabatan'       => $db_user['jabatan']
            ];

            header("Location: dashboard.php");
            exit();
        }
    }

    header("Location: login_karyawan.php?error=invalid");
    exit();

    $stmt->close();
    $conn->close();
} else {
    header("Location: login_karyawan.php");
    exit();
}
?>