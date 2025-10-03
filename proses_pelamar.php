<?php
session_start();
require 'config.php'; // Pastikan file config.php sudah ada dan benar

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Sesi Anda telah berakhir, silakan login kembali.'); window.location.href='login.php';</script>";
    exit;
}

// Fungsi untuk menangani upload file
function uploadFile($file, $uploadDir = 'uploads/') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // Abaikan jika tidak ada file yang diunggah (misal: KTP opsional)
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        // Jika ada error lain
        return ['error' => 'Terjadi kesalahan saat mengunggah file. Kode: ' . $file['error']];
    }

    // Pastikan direktori ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = time() . '-' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    } else {
        return ['error' => 'Gagal memindahkan file yang diunggah.'];
    }
}

// Cek apakah metode request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan sanitasi
    $user_id = $_SESSION['user_id'];
    $nama_lengkap = htmlspecialchars($_POST['namaLengkap']);
    $posisi_dilamar = htmlspecialchars($_POST['posisiDilamar']);
    $jenis_kelamin = htmlspecialchars($_POST['jenisKelamin']);
    $tempat_lahir = htmlspecialchars($_POST['tempatLahir']);
    $tanggal_lahir = htmlspecialchars($_POST['tanggalLahir']);
    $nik = htmlspecialchars($_POST['nomorIndukKeluarga']);
    $alamat_rumah = htmlspecialchars($_POST['alamatRumah']);
    $alamat_domisili = htmlspecialchars($_POST['alamatDomisili']); // Data baru
    $no_telp = htmlspecialchars($_POST['noTelp']);
    $email = htmlspecialchars($_POST['email']);
    $agama = htmlspecialchars($_POST['agama']);
    $kontak_darurat = htmlspecialchars($_POST['kontakDarurat']);
    $pendidikan_terakhir = htmlspecialchars($_POST['pendidikanTerakhir']);

    // Proses upload file
    $surat_lamaran_path = uploadFile($_FILES['suratLamaran']);
    $cv_path = uploadFile($_FILES['cv']);
    $photo_formal_path = uploadFile($_FILES['photoFormal']);
    $ktp_path = uploadFile($_FILES['ktp']); // KTP opsional
    $ijazah_transkrip_path = uploadFile($_FILES['ijazahTranskrip']);
    $berkas_pendukung_path = uploadFile($_FILES['berkasPendukung']);

    // Cek jika ada error saat upload
    $upload_errors = [];
    if (is_array($surat_lamaran_path) && isset($surat_lamaran_path['error'])) $upload_errors[] = 'Surat Lamaran: ' . $surat_lamaran_path['error'];
    // ... (tambahkan pengecekan error untuk file wajib lainnya)
    
    if (!empty($upload_errors)) {
        echo "<script>alert('Gagal mengunggah file:\\n" . implode("\\n", $upload_errors) . "'); window.history.back();</script>";
        exit;
    }

    // Siapkan statement SQL untuk insert data
    $stmt = $conn->prepare(
        "INSERT INTO data_pelamar (user_id, nama_lengkap, posisi_dilamar, jenis_kelamin, tempat_lahir, tanggal_lahir, nik, alamat_rumah, alamat_domisili, no_telp, email, agama, kontak_darurat, pendidikan_terakhir, surat_lamaran, cv, photo_formal, ktp, ijazah_transkrip, berkas_pendukung) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isssssssssssssssssss",
        $user_id, $nama_lengkap, $posisi_dilamar, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $nik, $alamat_rumah, $alamat_domisili, $no_telp, $email, $agama, $kontak_darurat, $pendidikan_terakhir, $surat_lamaran_path, $cv_path, $photo_formal_path, $ktp_path, $ijazah_transkrip_path, $berkas_pendukung_path
    );

    // Eksekusi query
    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran berhasil!'); window.location.href='dashboardpelamar.php';</script>"; // Arahkan ke halaman sukses
    } else {
        echo "<script>alert('Terjadi kesalahan: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika bukan metode POST, redirect ke halaman form
    header("Location: formpelamar.php");
    exit;
}
?>