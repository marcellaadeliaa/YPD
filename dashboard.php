<?php
session_start();
// Pastikan pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login_karyawan.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];

// --- AWAL MODIFIKASI ---

// Logika khusus untuk role 'penanggung jawab' berdasarkan divisi
if ($role === 'penanggung jawab') {
    // Ambil data divisi dari session.
    $divisi = $user['divisi'] ?? null;

    if ($divisi === 'Training') {
        header("Location: dashboardpenanggungjawab.php");
        exit();
    } elseif ($divisi === 'Konsultasi') {
        header("Location: dashboardpenanggungjawab_konsultasi.php");
        exit();
    } elseif ($divisi === 'Wisma') { // Penambahan Divisi Wisma
        header("Location: dashboardpenanggungjawab_wisma.php");
        exit();
    } elseif ($divisi === 'SDM') { // Penambahan Divisi SDM
        header("Location: dashboardpenanggungjawab_sdm.php");
        exit();
    } elseif ($divisi === 'Sekretariat') { // Penambahan Divisi Sekretariat
        header("Location: dashboardpenanggungjawab_sekretariat.php");
        exit();
    } elseif ($divisi === 'Keuangan') { // Penambahan Divisi Keuangan
        header("Location: dashboardpenanggungjawab_keuangan.php");
        exit();
    } else {
        // Jika divisi tidak valid, kembalikan ke halaman login
        header("Location: login_karyawan.php?error=invalid_division");
        exit();
    }
}

// --- AKHIR MODIFIKASI ---


// Mapping dashboard untuk role lainnya
$dashboard_map = [
    'admin' => 'dashboardadmin.php',
    'karyawan' => 'dashboardkaryawan.php',
    'direktur' => 'dashboarddirektur.php'
];

// Arahkan role lain ke dashboard yang sesuai
if (isset($dashboard_map[$role])) {
    header("Location: " . $dashboard_map[$role]);
} else {
    // Jika role tidak dikenali sama sekali
    header("Location: login_karyawan.php?error=invalid_role");
}
exit();
?>