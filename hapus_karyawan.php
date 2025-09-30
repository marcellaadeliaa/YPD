<?php
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Cari dan hapus karyawan dari session
    if (isset($_SESSION['karyawan'])) {
        foreach ($_SESSION['karyawan'] as $key => $karyawan) {
            if ($karyawan['kode'] == $id) {
                unset($_SESSION['karyawan'][$key]);
                break;
            }
        }
        // Re-index array
        $_SESSION['karyawan'] = array_values($_SESSION['karyawan']);
    }
    
    // Hapus data lengkap jika ada
    if (isset($_SESSION['karyawan_data'][$id])) {
        unset($_SESSION['karyawan_data'][$id]);
    }
    
    $_SESSION['success_message'] = "Data karyawan berhasil dihapus!";
}

header("Location: data_karyawan.php");
exit;
?>