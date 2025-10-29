<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if (isset($_GET['id'])) {
    $id_karyawan = $_GET['id'];
    $deleted_by = $_SESSION['user']['kode_karyawan']; 
    
    $sql = "UPDATE data_karyawan 
            SET is_deleted = 1, 
                deleted_at = NOW(),
                deleted_by = ?,
                status_aktif = 'non_aktif'
            WHERE id_karyawan = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $deleted_by, $id_karyawan);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data karyawan berhasil dihapus (tersimpan di backup)";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data karyawan";
    }
    
    $stmt->close();
}

header("Location: data_karyawan.php");
exit();
?>