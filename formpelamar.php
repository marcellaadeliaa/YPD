<?php
session_start();
require 'config.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

//UNTUK MENCEGAH PENGISIAN FORM BERULANG
$user_id = $_SESSION['user_id'];

// Siapkan query untuk mengecek apakah user sudah pernah mendaftar
$stmt = $conn->prepare("SELECT status FROM data_pelamar WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Jika data ditemukan, artinya user sudah pernah mendaftar.
    $data_pelamar = $result->fetch_assoc();
    $status_pelamar = htmlspecialchars($data_pelamar['status']);

    //Tampilan pesan pemberitahuan dan hentikan script agar form tidak muncul.
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8" />
        <title>Pemberitahuan Pendaftaran</title>
        <style>
            body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(180deg, #1E105E 0%, #8897AE 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; color: #333; }
            .notice-card { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); text-align: center; max-width: 500px; }
            .notice-card h2 { color: #1E105E; margin-top: 0; }
            .notice-card p { font-size: 1.1em; line-height: 1.6; }
            .notice-card .status { font-weight: bold; font-size: 1.2em; padding: 10px; border-radius: 8px; background-color: #f0f0f0; display: inline-block; margin-top: 10px; }
            .notice-card a { display: inline-block; margin-top: 30px; padding: 12px 25px; background-color: #4a3f81; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: background-color 0.3s; }
            .notice-card a:hover { background-color: #3a3162; }
        </style>
    </head>
    <body>
        <div class="notice-card">
            <h2>Pemberitahuan</h2>
            <p>Anda sudah pernah mengirimkan lamaran kerja. Anda tidak dapat mengisi formulir pendaftaran lagi.</p>
            <p>Status lamaran Anda saat ini adalah:</p>
            <div class="status"><?= $status_pelamar ?></div>
            <a href="dashboardpelamar.php">Kembali ke Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    $stmt->close();
    $conn->close();
    exit; 
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Form Pendaftaran Lamaran Kerja</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(180deg, #1E105E 0%, #8897AE 100%);
    }
    header {
      background: #fff;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #34377c;
    }
    .logo { display: flex; align-items: center; gap: 16px; font-weight: 500; font-size: 20px; color: #2e1f4f; }
    .logo img { width: 140px; height: 50px; object-fit: contain; }

    main {
      max-width: 960px;
      margin: 40px auto;
      background: #fff;
      border-radius: 15px;
      padding: 30px 40px;
      box-shadow: 0 0 10px rgb(72 54 120 / 0.2);
    }

    h2 {
      text-align: center;
      font-size: 22px;
      margin-bottom: 25px;
      padding-bottom: 10px;
      border-bottom: 2px solid #eee;
      color: #2e1f4f;
    }

    form { display: flex; gap: 40px; flex-wrap: nowrap; }
    .form-left, .form-right { flex: 1; display: flex; flex-direction: column; gap: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    label { font-weight: 600; font-size: 14px; color: #222; }
    input, select {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background-color: #f9f9f9;
    }

    .submit-btn {
      margin-top: 20px;
      padding: 12px;
      background-color: #4a3f81;
      border: none;
      border-radius: 8px;
      color: white;
      font-weight: 700;
      cursor: pointer;
      font-size: 15px;
    }
    .submit-btn:hover { background-color: #3a3162; }
    @media (max-width: 700px) { form { flex-direction: column; } }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="image/namayayasan.png" alt="Logo Yayasan">
      <span>Yayasan Purba Danarta</span>
    </div>
  </header>

  <main>
    <h2>Form Pendaftaran Lamaran Kerja</h2>
    <form action="proses_pelamar.php" method="POST" enctype="multipart/form-data">
      <div class="form-left">
        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="namaLengkap" required></div>
        <div class="form-group">
            <label for="posisiDilamar">Posisi yang Dilamar</label>
            <input type="text" id="posisiDilamar" name="posisiDilamar" required placeholder="Masukkan posisi yang dilamar">
        </div>
        <div class="form-group"><label>Jenis Kelamin</label>
          <select name="jenisKelamin" required>
            <option value="">Pilih Jenis Kelamin</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>
        <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tempatLahir" required></div>
        <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggalLahir" required></div>
        <div class="form-group"><label>Nomor Induk Keluarga</label><input type="text" name="nomorIndukKeluarga" required></div>
        <div class="form-group"><label>Alamat Rumah (Sesuai KTP)</label><input type="text" name="alamatRumah" required></div>
        <div class="form-group"><label>Alamat Rumah (Sesuai Domisili Sekarang)</label><input type="text" name="alamatDomisili" required></div>
        <div class="form-group"><label>No. Telepon (WA) Aktif</label><input type="text" name="noTelp" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Agama</label>
          <select name="agama" required>
            <option value="">Pilih</option>
            <option value="Islam">Islam</option>
            <option value="Kristen">Kristen</option>
            <option value="Katholik">Katholik</option>
            <option value="Buddha">Buddha</option>
            <option value="Hindu">Hindu</option>
            <option value="Khonghucu">Khonghucu</option>
          </select>
        </div>
        <div class="form-group"><label>Kontak Darurat</label><input type="text" name="kontakDarurat" required></div>
        <div class="form-group"><label>Pendidikan Terakhir</label>
          <select name="pendidikanTerakhir" required>
            <option value="">Pilih</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
            <option value="Diploma">Diploma</option>
            <option value="Sarjana">Sarjana</option>
            <option value="Magister">Magister</option>
          </select>
        </div>
      </div>

      <div class="form-right">
        <div class="form-group"><label>Surat Lamaran</label><input type="file" name="suratLamaran" required></div>
        <div class="form-group"><label>CV</label><input type="file" name="cv" required></div>
        <div class="form-group"><label>Pas Foto</label><input type="file" name="photoFormal" required></div>
        <div class="form-group"><label>KTP (Opsional)</label><input type="file" name="ktp"></div>
        <div class="form-group"><label>Ijazah & Transkrip</label><input type="file" name="ijazahTranskrip" required></div>
        <div class="form-group"><label>Berkas Pendukung</label><input type="file" name="berkasPendukung" required></div>

        <button type="submit" class="submit-btn">Masukkan</button>
      </div>
    </form>
  </main>
</body>
</html>
