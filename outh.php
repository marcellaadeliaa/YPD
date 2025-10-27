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
    $selected_role = $_POST['role'];

    $sql = "SELECT * FROM data_karyawan WHERE (kode_karyawan = ? OR nama_lengkap = ?) AND status_aktif = 'aktif'";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ss", $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $db_user = $result->fetch_assoc();

        if ($password === $db_user['password']) {
            
            $user_roles = explode(',', $db_user['role']);
            $is_valid_role = in_array($selected_role, $user_roles);
            
            if ($is_valid_role) {
                $_SESSION['user'] = [
                    'id_karyawan'   => $db_user['id_karyawan'],
                    'kode_karyawan' => $db_user['kode_karyawan'],
                    'nama_lengkap'  => $db_user['nama_lengkap'],
                    'divisi'        => $db_user['divisi'],
                    'role'          => $selected_role, 
                    'email'         => $db_user['email'],
                    'jabatan'       => $db_user['jabatan'],
                    'all_roles'     => $user_roles 
                ];

                header("Location: dashboard.php");
                exit();
            } else {
                header("Location: login_karyawan.php?error=invalid_role");
                exit();
            }
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