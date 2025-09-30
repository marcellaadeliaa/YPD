<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lama = $_POST['id_lama'] ?? '';
    $kode_karyawan = $_POST['kode_karyawan'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $email = $_POST['email'] ?? '';
    $divisi = $_POST['divisi'] ?? '';
    $role = $_POST['role'] ?? '';
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';
    $status = $_POST['status'] ?? '';
    
    // Update data di session karyawan (untuk tabel)
    foreach ($_SESSION['karyawan'] as &$karyawan) {
        if ($karyawan['kode'] == $id_lama) {
            $karyawan['kode'] = $kode_karyawan;
            $karyawan['nama'] = $nama;
            $karyawan['divisi'] = $divisi;
            $karyawan['role'] = $role;
            $karyawan['telepon'] = $telepon;
            $karyawan['email'] = $email;
            break;
        }
    }
    
    // Pastikan session karyawan_data ada
    if (!isset($_SESSION['karyawan_data'])) {
        $_SESSION['karyawan_data'] = array();
    }
    
    // Update data lengkap di session karyawan_data (untuk detail)
    if ($id_lama != $kode_karyawan) {
        // Jika kode berubah, pindahkan data ke kode baru
        if (isset($_SESSION['karyawan_data'][$id_lama])) {
            $_SESSION['karyawan_data'][$kode_karyawan] = $_SESSION['karyawan_data'][$id_lama];
            unset($_SESSION['karyawan_data'][$id_lama]);
        }
    }
    
    // Update data lengkap
    $_SESSION['karyawan_data'][$kode_karyawan] = array(
        'nama' => $nama,
        'divisi' => $divisi,
        'role' => $role,
        'telepon' => $telepon,
        'email' => $email,
        'alamat' => $alamat,
        'tanggal_masuk' => $tanggal_masuk,
        'status' => $status,
        'tanggal_lahir' => $tanggal_lahir,
        'jenis_kelamin' => $jenis_kelamin
    );
    
    $_SESSION['success_message'] = "Data karyawan berhasil diupdate!";
    header("Location: data_karyawan.php");
    exit;
} else {
    header("Location: data_karyawan.php");
    exit;
}
?>