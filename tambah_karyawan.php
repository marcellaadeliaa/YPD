<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_karyawan.php?error=unauthorized");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg,#1E105E 0%,#8897AE 100%); min-height:100vh; color:#333; }
        header { background:rgba(255,255,255,1); padding:20px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #34377c; }
        .logo { display:flex; align-items:center; gap:16px; font-weight:500; font-size:20px; color:#2e1f4f; }
        .logo img { width: 140px; height: 50px; object-fit: contain; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; gap:30px; }
        nav li { position:relative; }
        nav a { text-decoration:none; color:#333; font-weight:600; padding:8px 4px; display:block; }
        nav li ul { display:none; position:absolute; top:100%; left:0; background:#fff; padding:10px 0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); min-width:200px; z-index:999; }
        nav li:hover > ul { display:block; }
        nav li ul li { padding:5px 20px; }
        nav li ul li a { color:#333; font-weight:400; white-space:nowrap; }
        main { max-width:1000px; margin:40px auto; padding:0 20px; }
        
        .welcome-section {
            margin-bottom: 20px;
        }
        
        .welcome-section h1 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .welcome-section h2 {
            color: #ffffffff;
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .card { 
            background:#fff; 
            border-radius:20px; 
            padding:30px 40px; 
            box-shadow:0 2px 10px rgba(0,0,0,0.15); 
        }
        
        .form-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group.required label::after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1E105E;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #1E105E;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-note {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .section-note p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 16px;
        }
        
        .btn-save {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-cancel {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-back {
            background-color: #3498db;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-header {
                flex-direction: column;
                text-align: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="image/namayayasan.png" alt="Logo Yayasan">
            <span>Yayasan Purba Danarta</span>
        </div>
        <nav>
            <ul>
                <li><a href="dashboardadmin.php">Beranda</a></li>
                <li><a href="#">Cuti ▾</a>
                    <ul>
                        <li><a href="riwayat_cuti.php">Riwayat Cuti</a></li>
                        <li><a href="kalender_cuti.php">Kalender Cuti</a></li>
                        <li><a href="daftar_sisa_cuti.php">Sisa Cuti Karyawan</a></li>
                    </ul>
                </li>
                <li><a href="#">KHL ▾</a>
                    <ul>
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
                <li><a href="#">Karyawan ▾</a>
                    <ul>
                        <li><a href="data_karyawan.php">Data Karyawan</a></li>
                    </ul>
                </li>
                <li><a href="logout2.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="welcome-section">
            <h2>Tambah Karyawan Baru</h2>
        </div>
        
        <div class="card">
            <form id="tambahForm" method="POST" action="simpan_karyawan.php">
                <div class="form-header">
                    <div>
                        <h3>Data Karyawan Baru</h3>
                        <p>Buat akun untuk karyawan baru</p>
                    </div>
                </div>
                
                <div class="section-note">
                    <p><strong>Informasi:</strong> Field bertanda * wajib diisi. Field lainnya dapat diisi nanti oleh karyawan.</p>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Akun (Wajib)</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="kode_karyawan">Kode Karyawan</label>
                            <input type="text" id="kode_karyawan" name="kode_karyawan" placeholder="Contoh: YPD010" required>
                        </div>
                        <div class="form-group required">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="form-group required">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Contoh: nama@ypd.com" required>
                        </div>
                        <div class="form-group required">
                            <label for="password">Password</label>
                            <div style="position: relative;">
                                <input type="text" id="password" name="password" placeholder="Masukkan password" required style="padding-right: 40px;">
                                <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Pekerjaan (Wajib)</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" placeholder="Contoh: Staf Training" required>
                        </div>
                        <div class="form-group required">
                            <label for="divisi">Divisi</label>
                            <select id="divisi" name="divisi" required>
                                <option value="">Pilih Divisi</option>
                                <option value="Training">Training</option>
                                <option value="Wisma">Wisma</option>
                                <option value="Konsultasi">Konsultasi</option>
                                <option value="SDM">SDM</option>
                                <option value="Keuangan">Keuangan</option>
                                <option value="Sekretariat">Sekretariat</option>
                                <option value="Direksi">Direksi</option>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="karyawan">Karyawan</option>
                                <option value="admin">Admin</option>
                                <option value="penanggung jawab">Penanggung Jawab</option>
                                <option value="direktur">Direktur</option>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="no_telp">No. Telepon</label>
                            <input type="tel" id="no_telp" name="no_telp" placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Informasi Tambahan (Opsional)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="status_aktif">Status Aktif</label>
                            <select id="status_aktif" name="status_aktif">
                                <option value="aktif" selected>Aktif</option>
                                <option value="non_aktif">Non Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Buat Akun Karyawan
                    </button>
                    <a href="data_karyawan.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('nama_lengkap').addEventListener('blur', function() {
            const kodeInput = document.getElementById('kode_karyawan');
            const namaInput = document.getElementById('nama_lengkap');
            
            if (kodeInput.value === '' && namaInput.value !== '') {
                const nama = namaInput.value.toUpperCase();
                const inisial = nama.split(' ').map(word => word[0]).join('').substring(0, 3);
                kodeInput.value = 'YPD' + Math.floor(100 + Math.random() * 900);
            }
        });

        document.getElementById('nama_lengkap').addEventListener('blur', function() {
            const emailInput = document.getElementById('email');
            const namaInput = document.getElementById('nama_lengkap');
            
            if (emailInput.value === '' && namaInput.value !== '') {
                const nama = namaInput.value.toLowerCase().replace(/\s+/g, '.');
                emailInput.value = nama + '@ypd.com';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const defaultPassword = Math.random().toString(36).slice(-8);
            passwordInput.value = defaultPassword;
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>