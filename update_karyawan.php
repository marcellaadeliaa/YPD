<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    // Data personal tambahan - gunakan null coalescing untuk handle empty values
    $jenis_kelamin = !empty($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : NULL;
    $tempat_lahir = !empty($_POST['tempat_lahir']) ? $_POST['tempat_lahir'] : NULL;
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : NULL;
    $nik = !empty($_POST['nik']) ? $_POST['nik'] : NULL;
    $alamat_rumah = !empty($_POST['alamat_rumah']) ? $_POST['alamat_rumah'] : NULL;
    $alamat_domisili = !empty($_POST['alamat_domisili']) ? $_POST['alamat_domisili'] : NULL;
    $agama = !empty($_POST['agama']) ? $_POST['agama'] : NULL;
    $kontak_darurat = !empty($_POST['kontak_darurat']) ? $_POST['kontak_darurat'] : NULL;
    $pendidikan_terakhir = !empty($_POST['pendidikan_terakhir']) ? $_POST['pendidikan_terakhir'] : NULL;
    
    // Validasi field wajib
    if (empty($kode_karyawan) || empty($nama_lengkap) || empty($email) || empty($password) || 
        empty($jabatan) || empty($divisi) || empty($role)) {
        $_SESSION['error_message'] = "Semua field wajib harus diisi!";
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    // Validasi: Cek apakah kode karyawan sudah digunakan oleh karyawan lain
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
    
    // Validasi: Cek apakah email sudah digunakan oleh karyawan lain
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
    
    // HAPUS validasi nama lengkap - karena nama bisa sama untuk orang berbeda
    // $check_nama_sql = "SELECT id_karyawan FROM data_karyawan WHERE nama_lengkap = ? AND id_karyawan != ?";
    // $check_nama_stmt = $conn->prepare($check_nama_sql);
    // $check_nama_stmt->bind_param("si", $nama_lengkap, $id_karyawan);
    // $check_nama_stmt->execute();
    // $check_nama_result = $check_nama_stmt->get_result();
    
    // if ($check_nama_result->num_rows > 0) {
    //     $_SESSION['error_message'] = "Nama lengkap sudah digunakan oleh karyawan lain!";
    //     header("Location: edit_karyawan.php?id=" . $id_karyawan);
    //     exit;
    // }
    
    // Debug: Tampilkan data yang akan diupdate
    error_log("Updating karyawan ID: $id_karyawan");
    error_log("Data personal: jenis_kelamin=$jenis_kelamin, tempat_lahir=$tempat_lahir");
    
    // Buat SQL update dengan field yang pasti ada
    $sql = "UPDATE data_karyawan SET 
            kode_karyawan = ?, 
            nama_lengkap = ?, 
            email = ?, 
            password = ?, 
            jabatan = ?, 
            divisi = ?, 
            role = ?, 
            no_telp = ?, 
            status_aktif = ?";
    
    $params = [
        $kode_karyawan, 
        $nama_lengkap, 
        $email, 
        $password, 
        $jabatan, 
        $divisi, 
        $role, 
        $no_telp, 
        $status_aktif
    ];
    $param_types = "sssssssss";
    
    // Tambahkan field personal jika ada di database (opsional)
    $additional_fields = [
        'jenis_kelamin' => $jenis_kelamin,
        'tempat_lahir' => $tempat_lahir,
        'tanggal_lahir' => $tanggal_lahir,
        'nik' => $nik,
        'alamat_rumah' => $alamat_rumah,
        'alamat_domisili' => $alamat_domisili,
        'agama' => $agama,
        'kontak_darurat' => $kontak_darurat,
        'pendidikan_terakhir' => $pendidikan_terakhir
    ];
    
    // Cek kolom yang ada di database
    $check_table_sql = "DESCRIBE data_karyawan";
    $table_result = $conn->query($check_table_sql);
    $existing_columns = [];
    
    if ($table_result) {
        while ($row = $table_result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
    }
    
    // Tambahkan field yang ada di database
    foreach ($additional_fields as $column => $value) {
        if (in_array($column, $existing_columns)) {
            $sql .= ", $column = ?";
            $param_types .= "s";
            $params[] = $value;
            error_log("Adding column to update: $column");
        }
    }
    
    // Tambahkan WHERE clause
    $sql .= " WHERE id_karyawan = ?";
    $param_types .= "i";
    $params[] = $id_karyawan;
    
    error_log("Final SQL: $sql");
    error_log("Param types: $param_types");
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error_message'] = "Error preparing statement: " . $conn->error;
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    // Bind parameters
    $bind_params = [$param_types];
    foreach ($params as &$param) {
        $bind_params[] = &$param;
    }
    
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data karyawan berhasil diupdate!";
        header("Location: data_karyawan.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengupdate data: " . $conn->error;
        header("Location: edit_karyawan.php?id=" . $id_karyawan);
        exit;
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header("Location: data_karyawan.php");
    exit;
}
?>