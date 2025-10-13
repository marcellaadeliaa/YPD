<?php
require 'config.php';
session_start();

$role_direktur = 'direktur';
$kode_direktur = 'YPD001'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_karyawan     = trim($_POST['kode_karyawan'] ?? '');
    $proyek            = trim($_POST['proyek'] ?? '');
    $tanggal_mulai     = $_POST['tanggal_mulai'] ?? '';
    $tanggal_akhir     = !empty($_POST['tanggal_akhir']) ? $_POST['tanggal_akhir'] : $tanggal_mulai;
    $jam_mulai_normal  = $_POST['jam_mulai_normal'] ?? '';
    $jam_akhir_normal  = $_POST['jam_akhir_normal'] ?? '';
    $jam_mulai_libur   = $_POST['jam_mulai_libur'] ?? '';
    $jam_akhir_libur   = $_POST['jam_akhir_libur'] ?? '';

    if (empty($kode_karyawan) || empty($proyek) || empty($tanggal_mulai) || empty($jam_mulai_normal) || empty($jam_akhir_normal)) {
        $_SESSION['error'] = "Semua field wajib diisi (kecuali tanggal akhir & jam libur).";
        header("Location: pengajuan_khl_direktur.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM data_karyawan WHERE kode_karyawan = ?");
    $stmt->bind_param("s", $kode_karyawan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();

        $jabatan = $data['jabatan'];
        $divisi  = $data['divisi'];
        $role    = $data['role'];

        $insert = $conn->prepare("
            INSERT INTO data_pengajuan_khl (
                kode_karyawan, divisi, jabatan, role, proyek, 
                tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, 
                tanggal_cuti_khl, jam_mulai_cuti_khl, jam_akhir_cuti_khl, 
                status_khl, alasan_penolakan, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'disetujui', NULL, NOW())
        ");

        $insert->bind_param(
            "sssssssssss",
            $data['kode_karyawan'],
            $divisi,
            $jabatan,
            $role,
            $proyek,
            $tanggal_mulai,
            $jam_mulai_normal,
            $jam_akhir_normal,
            $tanggal_akhir,
            $jam_mulai_libur,
            $jam_akhir_libur
        );

        if ($insert->execute()) {
            $_SESSION['success'] = "✅ Pengajuan KHL berhasil dan langsung disetujui!";
            header("Location: riwayat_khl_direktur.php?status=disetujui");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menyimpan data: " . $conn->error;
        }

        $insert->close();
    } else {
        $_SESSION['error'] = "Kode karyawan <b>$kode_karyawan</b> tidak ditemukan di tabel data_karyawan!";
    }

    $stmt->close();
    header("Location: pengajuan_khl_direktur.php");
    exit();
}
?>
