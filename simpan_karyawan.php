<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
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
    
    // Pastikan session karyawan sudah ada
    if (!isset($_SESSION['karyawan'])) {
        $_SESSION['karyawan'] = array();
    }
    
    // Tambahkan data karyawan baru ke session
    $karyawan_baru = array(
        'kode' => $kode_karyawan,
        'nama' => $nama,
        'divisi' => $divisi,
        'role' => $role,
        'telepon' => $telepon,
        'email' => $email
    );
    
    $_SESSION['karyawan'][] = $karyawan_baru;
    
    // Simpan data lengkap di session terpisah jika diperlukan
    if (!isset($_SESSION['karyawan_data'])) {
        $_SESSION['karyawan_data'] = array();
    }
    
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
    
    // Set pesan sukses
    $_SESSION['success_message'] = "Data karyawan berhasil ditambahkan!";
    
    // Redirect ke halaman data karyawan
    header("Location: data_karyawan.php");
    exit;
} else {
    // Jika bukan method POST, redirect ke halaman tambah
    header("Location: tambah_karyawan.php");
    exit;
}
?>