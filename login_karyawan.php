<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Yayasan Purba Danarta</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Montserrat', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: 
                linear-gradient(180deg, rgba(36, 16, 74, 0.85) 0%, rgba(48, 20, 86, 0.75) 40%, rgba(134, 144, 166, 0.8) 100%),
                url('gedung.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 42px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .left-col { display: none; }
        .page-wrapper { width: 100%; max-width: 500px; display: flex; justify-content: center; align-items: center; gap: 0; }
        .right-col { width: 100%; display: flex; align-items: center; justify-content: center; }
        .card { width: 100%; background: linear-gradient(180deg, rgba(42,22,92,0.95) 0%, rgba(50,30,110,0.95) 100%); border-radius: 28px; padding: 40px 35px; box-shadow: 0 20px 50px rgba(15,12,40,0.4); position: relative; overflow: hidden; backdrop-filter: blur(10px); }
        .card::after { content: ""; position: absolute; left: 0; bottom: 0; width: 180px; height: 180px; background: linear-gradient(180deg, rgba(42,22,92,0.9), rgba(50,30,110,0.8)); border-top-right-radius: 200px; transform: translate(-40%, 30%); opacity: 0.3; pointer-events: none; }
        .card-inner { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 20px; color: #fff; text-align: center; padding: 10px 5px; }
        .welcome { font-size: 14px; color: rgba(255,255,255,0.9); font-weight: 300; margin-top: 0; letter-spacing: 0.5px; }
        .title { font-family: 'Playfair Display', serif; font-size: 42px; margin-top: 0; font-weight: 400; letter-spacing: 0.8px; line-height: 1; color: #ffffff; }
        .subtitle { font-family: 'Dancing Script', cursive; font-size: 32px; margin-top: -8px; font-weight: 700; letter-spacing: 1px; color: #e9e6ff; margin-bottom: 10px; }
        .login-container { width: 100%; max-width: 380px; margin: 0 auto; }
        .login-form { width: 100%; display: flex; flex-direction: column; gap: 22px; margin-top: 25px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; text-align: left; }
        .form-group label { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.95); margin-bottom: 2px; letter-spacing: 0.3px; }
        .form-input { width: 100%; padding: 14px 16px; border-radius: 10px; border: 1.5px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.12); color: white; font-size: 14px; font-family: 'Montserrat', sans-serif; transition: all 0.3s ease; }
        .form-input:focus { outline: none; border-color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.15); box-shadow: 0 0 0 3px rgba(255,255,255,0.1); }
        .form-input::placeholder { color: rgba(255,255,255,0.6); font-size: 13px; }
        select.form-input { appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 16px center; background-size: 16px; padding-right: 45px; color: white; }
        select.form-input option { background: #2a165c; color: white; padding: 10px; }
        select.form-input::-webkit-listbox, select.form-input::-webkit-select-list-box { background: #2a165c; color: white; }
        select.form-input::-webkit-option { background: #2a165c; color: white; padding: 10px; }
        .login-btn { width: 100%; padding: 16px; border-radius: 12px; background: #ffffff; border: none; font-family: 'Montserrat', sans-serif; font-size: 15px; font-weight: 700; color: #24104a; cursor: pointer; box-shadow: 0 6px 20px rgba(0,0,0,0.25); transition: all 0.3s ease; margin-top: 10px; letter-spacing: 0.5px; }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.35); background: #f8f9fa; }
        .error-message { background: rgba(255,0,0,0.15); border: 1px solid rgba(255,0,0,0.4); color: #ff6b6b; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; text-align: center; font-weight: 500; }
        .form-header { text-align: center; margin-bottom: 10px; padding: 0 5px; }
        .form-title { font-family: 'Montserrat', sans-serif; font-size: 22px; font-weight: 700; color: white; margin-bottom: 6px; letter-spacing: 0.5px; }
        .form-subtitle { font-family: 'Montserrat', sans-serif; font-size: 12px; color: rgba(255,255,255,0.8); font-weight: 400; line-height: 1.4; }
        .password-note { font-size: 11px; color: rgba(255,255,255,0.6); text-align: center; margin-top: -5px; font-style: italic; line-height: 1.3; }
        .input-hint { font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 3px; font-style: italic; }
        .btn-row { display: flex; gap: 25px; margin-top: 20px; align-items: center; justify-content: center; }
        .action-btn { width: 120px; height: 50px; border-radius: 40px; background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.3); font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; backdrop-filter: blur(10px); }
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.25); background: rgba(255,255,255,0.25); border-color: rgba(255,255,255,0.5); }
        @media (max-width: 768px) {
            body { padding: 20px; }
            .card { padding: 30px 25px; }
            .title { font-size: 36px; }
            .subtitle { font-size: 28px; }
        }
        @media (max-width: 480px) {
            .card { padding: 25px 20px; border-radius: 20px; }
            .title { font-size: 32px; }
            .subtitle { font-size: 26px; }
            .btn-row { gap: 15px; }
            .action-btn { width: 100px; height: 45px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="right-col">
            <div class="card">
                <div class="card-inner">
                    <p class="welcome">Selamat Datang di</p>
                    <h1 class="title">Yayasan</h1>
                    <h2 class="subtitle">Purba Danarta</h2>
                    
                    <div class="login-container">
                        <div class="form-header">
                            <div class="form-title">Login</div>
                        </div>
                        
                        <form class="login-form" action="outh.php" method="POST">
                            <?php if (isset($_GET['error'])): ?>
                                <div class="error-message">
                                    <?php 
                                    $errors = [
                                        'invalid' => 'Kode karyawan/nama, password, atau role salah!',
                                        'missing' => 'Harap isi semua field!',
                                        'invalid_role' => 'Role tidak valid!'
                                    ];
                                    echo $errors[$_GET['error']] ?? 'Terjadi kesalahan!';
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="login_input">Kode Karyawan / Nama</label>
                                <input type="text" id="login_input" name="login_input" class="form-input" 
                                       placeholder="Masukkan kode karyawan atau nama lengkap" required>
                                <div class="input-hint">Contoh: YPD001 atau Pico</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-input" 
                                       placeholder="Masukkan password Anda" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" class="form-input" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="karyawan">Karyawan</option>
                                    <option value="penanggung jawab">Penanggung Jawab</option>
                                    <option value="direktur">Direktur</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="login-btn">Login →</button>
                        </form>
                    </div>
                    
                    <div class="btn-row">
                        <button class="action-btn" onclick="window.location.href='#'">Bantuan</button>
                        <button class="action-btn" onclick="window.location.href='#'">Info</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </body>
</html>