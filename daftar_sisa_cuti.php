<?php
session_start();

// --- DUMMY DATA SISA CUTI KARYAWAN ---
$data_sisa_cuti = [
    [
        'id' => 1,
        'kode_karyawan' => '11223344',
        'nama_karyawan' => 'Xue',
        'divisi' => 'Admin SDM',
        'cuti_tahunan' => 12,
        'cuti_tahunan_terpakai' => 3,
        'cuti_lustrum' => 5,
        'cuti_lustrum_terpakai' => 1,
        'cuti_tambahan' => 0,
        'cuti_tambahan_terpakai' => 0,
        'total_sisa_cuti' => 13
    ],
    [
        'id' => 2,
        'kode_karyawan' => '11223355',
        'nama_karyawan' => 'Adel',
        'divisi' => 'Admin SDM',
        'cuti_tahunan' => 12,
        'cuti_tahunan_terpakai' => 8,
        'cuti_lustrum' => 3,
        'cuti_lustrum_terpakai' => 0,
        'cuti_tambahan' => 2,
        'cuti_tambahan_terpakai' => 1,
        'total_sisa_cuti' => 8
    ],
    [
        'id' => 3,
        'kode_karyawan' => '11223366',
        'nama_karyawan' => 'Budi Santoso',
        'divisi' => 'Operasional',
        'cuti_tahunan' => 12,
        'cuti_tahunan_terpakai' => 12,
        'cuti_lustrum' => 2,
        'cuti_lustrum_terpakai' => 2,
        'cuti_tambahan' => 0,
        'cuti_tambahan_terpakai' => 0,
        'total_sisa_cuti' => 0
    ],
    [
        'id' => 4,
        'kode_karyawan' => '11223377',
        'nama_karyawan' => 'Siti Rahayu',
        'divisi' => 'Marketing',
        'cuti_tahunan' => 12,
        'cuti_tahunan_terpakai' => 5,
        'cuti_lustrum' => 7,
        'cuti_lustrum_terpakai' => 2,
        'cuti_tambahan' => 0,
        'cuti_tambahan_terpakai' => 0,
        'total_sisa_cuti' => 12
    ],
    [
        'id' => 5,
        'kode_karyawan' => '11223388',
        'nama_karyawan' => 'Ahmad Fauzi',
        'divisi' => 'IT',
        'cuti_tahunan' => 12,
        'cuti_tahunan_terpakai' => 2,
        'cuti_lustrum' => 4,
        'cuti_lustrum_terpakai' => 0,
        'cuti_tambahan' => 5,
        'cuti_tambahan_terpakai' => 3,
        'total_sisa_cuti' => 16
    ],
];

// Inisialisasi variabel filter
$search_query = $_GET['search'] ?? '';

// Filter data berdasarkan pencarian
$filtered_data = $data_sisa_cuti;
if (!empty($search_query)) {
    $search_lower = strtolower($search_query);
    $filtered_data = array_filter($filtered_data, function($karyawan) use ($search_lower) {
        return strpos(strtolower($karyawan['nama_karyawan']), $search_lower) !== false ||
               strpos(strtolower($karyawan['kode_karyawan']), $search_lower) !== false ||
               strpos(strtolower($karyawan['divisi']), $search_lower) !== false;
    });
    $filtered_data = array_values($filtered_data);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Sisa Cuti Karyawan - Admin SDM</title>
<style>
    body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
    header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
    .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
    .logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 50%; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
    nav li { position:relative; }
    nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
    nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
    nav li:hover > ul { display:block; }
    nav li ul li { padding:5px 20px; }
    nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
    main { max-width:1400px; margin:40px auto; padding:0 20px; }
    h1, p.admin-title { color: #fff; }
    h1 { text-align:left; font-size:28px; margin-bottom:10px; }
    p.admin-title { font-size: 16px; margin-top: 0; margin-bottom: 30px; font-weight: 400; opacity: 0.9; }
    .card { background:#fff; border-radius:20px; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.15); }
    .page-title { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 30px; color: #1E105E; }
    
    /* Filter Section */
    .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
    .filter-row { display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-weight: 600; font-size: 14px; color: #333; }
    .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .filter-group.search-group { flex-grow: 1; min-width: 300px; }
    
    .action-bar { display: flex; gap: 10px; margin-top: 15px; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.3s; }
    .btn-cari { background-color: #4a3f81; }
    .btn-cari:hover { background-color: #3a3162; }
    .btn-reset { background-color: #6c757d; }
    .btn-reset:hover { background-color: #545b62; }
    .btn-tambah { background-color: #28a745; }
    .btn-tambah:hover { background-color: #218838; }
    
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; color: #333; position: sticky; top: 0; }
    .data-table tbody tr:hover { background-color: #f1f1f1; }
    
    /* Status styles */
    .sisa-banyak { color: #28a745; font-weight: 600; }
    .sisa-sedikit { color: #ffc107; font-weight: 600; }
    .sisa-habis { color: #dc3545; font-weight: 600; }
    
    .no-data { text-align: center; padding: 40px; color: #666; font-style: italic; }
    
    .filter-info { 
        background: #e7f3ff; 
        padding: 10px 15px; 
        border-radius: 6px; 
        margin-bottom: 15px; 
        font-size: 14px; 
        border-left: 4px solid #4a3f81;
    }
    
    .action-cell { display: flex; gap: 5px; }
    .btn-action { padding: 6px 12px; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; color: #fff; cursor: pointer; }
    .btn-edit { background-color: #17a2b8; }
    .btn-edit:hover { background-color: #138496; }
    .btn-tambah-cuti { background-color: #28a745; }
    .btn-tambah-cuti:hover { background-color: #218838; }
    
    /* Modal Styles */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 5% auto; padding: 30px; border-radius: 10px; width: 80%; max-width: 500px; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover { color: #000; }
    
    .modal-title { font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #1E105E; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #333; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    
    .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4a3f81; }
    .info-box h4 { margin: 0 0 10px 0; color: #1E105E; }
    .info-box p { margin: 5px 0; font-size: 14px; }
    
    @media (max-width: 768px) {
        .filter-row { flex-direction: column; }
        .filter-group { width: 100%; }
        .action-bar { flex-direction: column; }
        .btn { width: 100%; }
        .action-cell { flex-direction: column; }
    }
</style>
</head>
<body>

<header>
    <div class="logo">
        <img src="https://yt3.googleusercontent.com/ytc/AIdro_k21dE_e_T4s2-9e5aB2H3-_hDUa8sGAky5TTsD=s900-c-k-c0x00ffffff-no-rj" alt="Logo Yayasan">
        <span>Yayasan Purba Danarta</span>
    </div>
    <nav>
        <ul>
        <li><a href="dashboardadmin.php">Beranda</a></li>
        <li><a href="#">Cuti ▾</a>
            <ul>
            <li><a href="administrasi_cuti.php">Administrasi Cuti</a></li>
            <li><a href="riwayat_cuti_pegawai.php">Riwayat Cuti</a></li>
            <li><a href="kalender_cuti.php">Kalender Cuti</a></li>
            <li><a href="daftar_sisa_cuti.php">Sisa Cuti Karyawan</a></li>
            </ul>
        </li>
        <li><a href="#">KHL ▾</a>
            <ul>
                <li><a href="administrasi_khl.php">Administrasi KHL</a></li>
                <li><a href="riwayat_khl.php">Riwayat KHL</a></li>
                <li><a href="kalender_khl.php">Kalender KHL</a></li>
            </ul>
        </li>
        <li><a href="#">Lamaran Kerja ▾</a>
            <ul>
                <li><a href="administrasi_pelamar.php">Administrasi Pelamar</a></li>
                <li><a href="riwayat_pelamar.php">Riwayat Pelamar</a></li>
            </ul>
        </li>
        <li><a href="#">Karyawan ▾</a></li>
        <li><a href="#">Profil ▾</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Welcome, Xue!</h1>
    <p class="admin-title">Admin Divisi XXXX</p>

    <div class="card">
        <h2 class="page-title">Daftar Sisa Cuti Karyawan</h2>
        
        <div class="info-box">
            <h4>📋 Informasi Kuota Cuti</h4>
            <p><strong>Cuti Tahunan:</strong> 12 hari per tahun untuk semua karyawan</p>
            <p><strong>Cuti Lustrum:</strong> Bervariasi per karyawan (dapat disesuaikan)</p>
            <p><strong>Cuti Tambahan:</strong> Dapat diberikan oleh admin sesuai kebutuhan</p>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="daftar_sisa_cuti.php">
                <div class="filter-row">
                    <div class="filter-group search-group">
                        <label for="search">Cari Karyawan (Nama/Kode/Divisi)</label>
                        <input type="text" id="search" name="search" placeholder="Cari berdasarkan nama, kode, atau divisi..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="action-bar">
                    <button type="submit" class="btn btn-cari">Cari</button>
                    <a href="daftar_sisa_cuti.php" class="btn btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <!-- Info Filter Aktif -->
        <?php if (!empty($search_query)): ?>
            <div class="filter-info">
                <strong>Filter Aktif:</strong> Pencarian: '<?= htmlspecialchars($search_query) ?>'
                <span style="float: right; color: #666;">
                    Data ditemukan: <?= count($filtered_data) ?>
                </span>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Kode</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Cuti Tahunan</th>
                    <th>Cuti Lustrum</th>
                    <th>Cuti Tambahan</th>
                    <th>Total Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_data)): ?>
                    <?php foreach($filtered_data as $karyawan): 
                        $sisa_tahunan = $karyawan['cuti_tahunan'] - $karyawan['cuti_tahunan_terpakai'];
                        $sisa_lustrum = $karyawan['cuti_lustrum'] - $karyawan['cuti_lustrum_terpakai'];
                        $sisa_tambahan = $karyawan['cuti_tambahan'] - $karyawan['cuti_tambahan_terpakai'];
                        $total_sisa = $sisa_tahunan + $sisa_lustrum + $sisa_tambahan;
                        
                        // Tentukan status
                        if ($total_sisa >= 8) {
                            $status_class = 'sisa-banyak';
                            $status_text = 'Banyak';
                        } elseif ($total_sisa >= 3) {
                            $status_class = 'sisa-sedikit';
                            $status_text = 'Sedikit';
                        } else {
                            $status_class = 'sisa-habis';
                            $status_text = 'Habis';
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($karyawan['kode_karyawan']) ?></td>
                        <td><?= htmlspecialchars($karyawan['nama_karyawan']) ?></td>
                        <td><?= htmlspecialchars($karyawan['divisi']) ?></td>
                        <td>
                            <small><?= $sisa_tahunan ?> / <?= $karyawan['cuti_tahunan'] ?></small><br>
                            <progress value="<?= $karyawan['cuti_tahunan_terpakai'] ?>" max="<?= $karyawan['cuti_tahunan'] ?>" style="width: 80px; height: 8px;"></progress>
                        </td>
                        <td>
                            <small><?= $sisa_lustrum ?> / <?= $karyawan['cuti_lustrum'] ?></small><br>
                            <progress value="<?= $karyawan['cuti_lustrum_terpakai'] ?>" max="<?= $karyawan['cuti_lustrum'] ?>" style="width: 80px; height: 8px;"></progress>
                        </td>
                        <td>
                            <small><?= $sisa_tambahan ?> / <?= $karyawan['cuti_tambahan'] ?></small><br>
                            <progress value="<?= $karyawan['cuti_tambahan_terpakai'] ?>" max="<?= $karyawan['cuti_tambahan'] ?>" style="width: 80px; height: 8px;"></progress>
                        </td>
                        <td>
                            <strong class="<?= $status_class ?>"><?= $total_sisa ?> hari</strong>
                        </td>
                        <td>
                            <span class="<?= $status_class ?>"><?= $status_text ?></span>
                        </td>
                        <td class="action-cell">
                            <button class="btn-action btn-edit" onclick="editCutiLustrum(<?= htmlspecialchars(json_encode($karyawan)) ?>)">Edit Lustrum</button>
                            <button class="btn-action btn-tambah-cuti" onclick="tambahCutiTambahan(<?= htmlspecialchars(json_encode($karyawan)) ?>)">Tambah Cuti</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="no-data">Tidak ada data karyawan yang ditemukan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Edit Cuti Lustrum -->
<div id="editLustrumModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-title">Edit Cuti Lustrum</div>
        <form id="editLustrumForm">
            <input type="hidden" id="editKaryawanId" name="karyawan_id">
            <div class="form-group">
                <label for="editNamaKaryawan">Nama Karyawan</label>
                <input type="text" id="editNamaKaryawan" readonly>
            </div>
            <div class="form-group">
                <label for="editKodeKaryawan">Kode Karyawan</label>
                <input type="text" id="editKodeKaryawan" readonly>
            </div>
            <div class="form-group">
                <label for="editCutiLustrum">Jumlah Cuti Lustrum</label>
                <input type="number" id="editCutiLustrum" name="cuti_lustrum" min="0" max="30" required>
            </div>
            <div class="form-group">
                <label for="editKeterangan">Keterangan (Opsional)</label>
                <textarea id="editKeterangan" name="keterangan" rows="3" placeholder="Alasan penyesuaian cuti lustrum..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-reset" onclick="closeModal('editLustrumModal')">Batal</button>
                <button type="submit" class="btn btn-cari">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Cuti Tambahan -->
<div id="tambahCutiModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-title">Tambah Cuti Tambahan</div>
        <form id="tambahCutiForm">
            <input type="hidden" id="tambahKaryawanId" name="karyawan_id">
            <div class="form-group">
                <label for="tambahNamaKaryawan">Nama Karyawan</label>
                <input type="text" id="tambahNamaKaryawan" readonly>
            </div>
            <div class="form-group">
                <label for="tambahKodeKaryawan">Kode Karyawan</label>
                <input type="text" id="tambahKodeKaryawan" readonly>
            </div>
            <div class="form-group">
                <label for="tambahJumlahCuti">Jumlah Cuti Tambahan</label>
                <input type="number" id="tambahJumlahCuti" name="jumlah_cuti" min="1" max="30" required>
            </div>
            <div class="form-group">
                <label for="tambahAlasan">Alasan Penambahan</label>
                <select id="tambahAlasan" name="alasan" required>
                    <option value="">Pilih Alasan</option>
                    <option value="Kebutuhan Khusus">Kebutuhan Khusus</option>
                    <option value="Penghargaan Kinerja">Penghargaan Kinerja</option>
                    <option value="Keadaan Darurat">Keadaan Darurat</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tambahKeterangan">Keterangan Tambahan</label>
                <textarea id="tambahKeterangan" name="keterangan" rows="3" placeholder="Jelaskan alasan penambahan cuti..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-reset" onclick="closeModal('tambahCutiModal')">Batal</button>
                <button type="submit" class="btn btn-tambah">Tambah Cuti</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal functionality
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Close modals when clicking X
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.onclick = function() {
            this.closest('.modal').style.display = 'none';
        }
    });
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
    
    // Edit Cuti Lustrum
    function editCutiLustrum(karyawan) {
        document.getElementById('editKaryawanId').value = karyawan.id;
        document.getElementById('editNamaKaryawan').value = karyawan.nama_karyawan;
        document.getElementById('editKodeKaryawan').value = karyawan.kode_karyawan;
        document.getElementById('editCutiLustrum').value = karyawan.cuti_lustrum;
        document.getElementById('editKeterangan').value = '';
        openModal('editLustrumModal');
    }
    
    // Tambah Cuti Tambahan
    function tambahCutiTambahan(karyawan) {
        document.getElementById('tambahKaryawanId').value = karyawan.id;
        document.getElementById('tambahNamaKaryawan').value = karyawan.nama_karyawan;
        document.getElementById('tambahKodeKaryawan').value = karyawan.kode_karyawan;
        document.getElementById('tambahJumlahCuti').value = 1;
        document.getElementById('tambahAlasan').value = '';
        document.getElementById('tambahKeterangan').value = '';
        openModal('tambahCutiModal');
    }
    
    // Form submissions (simulasi)
    document.getElementById('editLustrumForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        alert('Cuti lustrum berhasil diperbarui!\n\nKaryawan: ' + document.getElementById('editNamaKaryawan').value +
              '\nCuti Lustrum Baru: ' + document.getElementById('editCutiLustrum').value + ' hari');
        closeModal('editLustrumModal');
    });
    
    document.getElementById('tambahCutiForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        alert('Cuti tambahan berhasil ditambahkan!\n\nKaryawan: ' + document.getElementById('tambahNamaKaryawan').value +
              '\nJumlah Cuti: ' + document.getElementById('tambahJumlahCuti').value + ' hari' +
              '\nAlasan: ' + document.getElementById('tambahAlasan').value);
        closeModal('tambahCutiModal');
    });
</script>

</body>
</html>