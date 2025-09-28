<?php
session_start();
include 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id           = $_SESSION['user_id'];
    $namaLengkap        = $_POST['namaLengkap'];
    $posisiDilamar      = $_POST['posisiDilamar'];
    $jenisKelamin       = $_POST['jenisKelamin'];
    $tempatLahir        = $_POST['tempatLahir'];
    $tanggalLahir       = $_POST['tanggalLahir'];
    $nik                = $_POST['nomorIndukKeluarga'];
    $alamatRumah        = $_POST['alamatRumah'];
    $noTelp             = $_POST['noTelp'];
    $email              = $_POST['email'];
    $agama              = $_POST['agama'];
    $kontakDarurat      = $_POST['kontakDarurat'];
    $pendidikanTerakhir = $_POST['pendidikanTerakhir'];

    // Direktori upload
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Fungsi upload file
    function uploadFile($inputName, $uploadDir) {
        if (!empty($_FILES[$inputName]['name'])) {
            $fileName = time() . "-" . basename($_FILES[$inputName]['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetFile)) {
                return $targetFile;
            }
        }
        return null;
    }

    // Proses upload
    $suratLamaran    = uploadFile("suratLamaran", $uploadDir);
    $cv              = uploadFile("cv", $uploadDir);
    $photoFormal     = uploadFile("photoFormal", $uploadDir);
    $ktp             = uploadFile("ktp", $uploadDir); // opsional
    $ijazahTranskrip = uploadFile("ijazahTranskrip", $uploadDir);
    $berkasPendukung = uploadFile("berkasPendukung", $uploadDir);

    // Query insert
    $sql = "INSERT INTO data_pelamar 
        (user_id, nama_lengkap, posisi_dilamar, jenis_kelamin, tempat_lahir, tanggal_lahir, nik, alamat_rumah, no_telp, email, agama, kontak_darurat, pendidikan_terakhir,
         surat_lamaran, cv, photo_formal, ktp, ijazah_transkrip, berkas_pendukung)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Query error: " . $conn->error);
    }

    // Fix bind_param (19 kolom → 19 variable)
    $stmt->bind_param(
        "issssssssssssssssss",
        $user_id, $namaLengkap, $posisiDilamar, $jenisKelamin, $tempatLahir, $tanggalLahir,
        $nik, $alamatRumah, $noTelp, $email, $agama, $kontakDarurat, $pendidikanTerakhir,
        $suratLamaran, $cv, $photoFormal, $ktp, $ijazahTranskrip, $berkasPendukung
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Pendaftaran berhasil!');
                window.location.href = 'dashboardpelamar.php';
              </script>";
        exit;
    } else {
        echo "Error insert: " . $stmt->error;
    }
}
?>
