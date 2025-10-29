<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if (isset($_GET['id'])) {
    $id_karyawan = $_GET['id'];
    
    $sql = "UPDATE data_karyawan 
            SET is_deleted = 0, 
                deleted_at = NULL,
                deleted_by = NULL,
                status_aktif = 'aktif'
            WHERE id_karyawan = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_karyawan);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data karyawan berhasil dikembalikan";
    } else {
        $_SESSION['error_message'] = "Gagal mengembalikan data karyawan";
    }
    
    $stmt->close();
}

header("Location: backup_karyawan.php");
exit();
?>