<?php

// Perintah Wajib untuk Menampilkan Error Apapun yang Terjadi
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Tes Diagnostik Server</h1>";
echo "<p>Tes dimulai pada: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Tes 1: Memuat file konfigurasi
echo "<h2>Tes 1: Memuat config.php</h2>";
if (file_exists('config.php')) {
    require 'config.php';
    echo "<p style='color:green;'><b>BERHASIL:</b> File 'config.php' ditemukan dan dimuat.</p>";

    // Tes 2: Mengecek koneksi database
    echo "<hr><h2>Tes 2: Koneksi ke Database</h2>";
    if (isset($conn) && $conn instanceof mysqli) {
        if ($conn->ping()) {
            echo "<p style='color:green;'><b>BERHASIL:</b> Koneksi ke database '" . $db . "' berhasil.</p>";
        } else {
            echo "<p style='color:red;'><b>GAGAL:</b> Variabel \$conn ada, tapi koneksi ke database gagal. Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'><b>GAGAL:</b> File 'config.php' dimuat, tetapi tidak membuat variabel koneksi (\$conn) yang valid.</p>";
    }

} else {
    echo "<p style='color:red;'><b>GAGAL:</b> File 'config.php' TIDAK DITEMUKAN di direktori yang sama.</p>";
}

echo "<hr>";
echo "<p>Tes Selesai.</p>";

?>