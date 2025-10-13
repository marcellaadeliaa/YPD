<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (strlen($new_password) < 8 || !preg_match('/[0-9]/', $new_password) || !preg_match('/[A-Za-z]/', $new_password)) {
        $error = "Password minimal 8 karakter dan harus mengandung huruf & angka.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak sesuai.";
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);
        
        if ($stmt->execute()) {
            $success = "Password berhasil direset! Silakan login dengan password baru.";
            unset($_SESSION['reset_email']);
        } else {
            $error = "Gagal reset password. Silakan coba lagi.";
        }
        
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_exists = $result->fetch_assoc();
$stmt->close();

if (!$user_exists) {
    $error = "Email tidak valid. Silakan lakukan reset password kembali.";
    unset($_SESSION['reset_email']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password — Yayasan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
  <div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-[url('image/gedungyayasan.png')] bg-center bg-cover"></div>
    <div class="absolute inset-0 bg-black/40"></div>
  </div>

  <main class="min-h-screen flex items-center justify-center p-6 relative z-10">
    <section class="w-full max-w-[500px]">
      <div class="mx-auto bg-[#1E105E]/95 rounded-2xl shadow-2xl p-10 md:p-12">
        <header class="text-center mb-6">
          <h1 class="text-3xl md:text-4xl text-white font-bold">Reset Password</h1>
          <p class="text-sm text-white/80 mt-2">Buat password baru untuk akun Anda</p>
          <?php if (isset($email)): ?>
            <p class="text-xs text-blue-300 mt-1">Email: <?= htmlspecialchars($email) ?></p>
          <?php endif; ?>
        </header>

        <?php if (!empty($success)): ?>
          <div class="bg-green-500/20 border border-green-500 rounded-lg p-4 mb-4">
            <p class="text-green-300 text-sm"><?= $success ?></p>
            <div class="mt-3">
              <a href="login.php" class="inline-flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-green-500 hover:bg-green-600 text-white font-semibold text-sm">
                Login Sekarang <span aria-hidden="true">➜</span>
              </a>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="bg-red-500/20 border border-red-500 rounded-lg p-4 mb-4">
            <p class="text-red-300 text-sm"><?= $error ?></p>
            <?php if (strpos($error, 'tidak valid') !== false): ?>
              <div class="mt-3">
                <a href="forgot_password.php" class="inline-flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm">
                  Reset Ulang <span aria-hidden="true">➜</span>
                </a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (empty($success) && isset($_SESSION['reset_email'])): ?>
        <form method="POST" class="space-y-4" autocomplete="off">
          <div>
            <label for="password" class="block text-sm font-medium text-white/90 mb-2">Password Baru</label>
            <input id="password" name="password" type="password" placeholder="Masukkan password baru" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <p class="text-xs text-white/70 mt-1">*Min. 8 karakter, gabungan angka dan huruf.</p>
          </div>

          <div>
            <label for="confirm_password" class="block text-sm font-medium text-white/90 mb-2">Konfirmasi Password Baru</label>
            <input id="confirm_password" name="confirm_password" type="password" placeholder="Masukkan ulang password baru" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
          </div>

          <div class="mt-4">
            <button type="submit"
              class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-lg shadow-sm">
              Reset Password <span aria-hidden="true">➜</span>
            </button>
          </div>
        </form>
        <?php endif; ?>

        <p class="text-center text-sm text-white/80 mt-6">
          <a href="login.php" class="text-blue-300 hover:underline">Kembali ke Login</a>
          <?php if (isset($_SESSION['reset_email'])): ?>
            <span class="mx-2">•</span>
            <a href="forgot_password.php" class="text-blue-300 hover:underline">Ganti Email</a>
          <?php endif; ?>
        </p>
      </div>
    </section>
  </main>
</body>
</html>