<?php
require_once 'config.php';

echo "<h2>Status Database</h2>";

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
echo "✓ Koneksi database berhasil<br>";

// Cek tabel pengajuan_khl
$result = mysqli_query($conn, "SHOW TABLES LIKE 'pengajuan_khl'");
if (mysqli_num_rows($result) > 0) {
    echo "✓ Tabel pengajuan_khl ada<br>";
    
    // Tampilkan struktur tabel
    $structure = mysqli_query($conn, "DESCRIBE pengajuan_khl");
    echo "<h3>Struktur tabel pengajuan_khl:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "✗ Tabel pengajuan_khl tidak ada<br>";
}

// Cek data yang ada
$data = mysqli_query($conn, "SELECT * FROM pengajuan_khl ORDER BY created_at DESC LIMIT 5");
echo "<h3>Data terbaru dalam pengajuan_khl:</h3>";
if (mysqli_num_rows($data) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Kode</th><th>Proyek</th><th>Tanggal KHL</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($data)) {
        echo "<tr>";
        echo "<td>" . $row['id_khl'] . "</td>";
        echo "<td>" . $row['kode_karyawan'] . "</td>";
        echo "<td>" . $row['proyek'] . "</td>";
        echo "<td>" . $row['tanggal_khl'] . "</td>";
        echo "<td>" . $row['status_khl'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Tidak ada data dalam tabel<br>";
}

mysqli_close($conn);
?>