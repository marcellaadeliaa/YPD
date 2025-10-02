<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}
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
    .logo img { width: 130px; height: 50px; object-fit: contain; }

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
    <div class="form-title-header">Form Pendaftaran</div>
  </header>

  <main>
    <h2>Form Pendaftaran Lamaran Kerja</h2>
    <form action="proses_pelamar.php" method="POST" enctype="multipart/form-data">
      <div class="form-left">
        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="namaLengkap" required></div>
        <div class="form-group"><label>Posisi yang Dilamar</label>
          <select name="posisiDilamar" required>
            <option value="">Pilih Posisi</option>
              <option value="Training">Training</option>
              <option value="Wisma">Wisma</option>
              <option value="Konsultasi">Konsultasi</option>
              <option value="Keuangan">Keuangan</option>
              <option value="SDM">SDM</option>
              <option value="Sekretariat">Sekretariat</option>
          </select>
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
