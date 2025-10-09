<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "Email tidak ditemukan dalam sistem.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lupa Password — Yayasan</title>
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
          <h1 class="text-3xl md:text-4xl text-white font-bold">Lupa Password</h1>
          <p class="text-sm text-white/80 mt-2">Masukkan email untuk reset password</p>
        </header>

        <?php if (!empty($error)): ?>
          <div class="bg-red-500/20 border border-red-500 rounded-lg p-4 mb-4">
            <p class="text-red-300 text-sm"><?= $error ?></p>
          </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4" autocomplete="off">
          <div>
            <label for="email" class="block text-sm font-medium text-white/90 mb-2">Email</label>
            <input id="email" name="email" type="email" placeholder="Masukkan email yang terdaftar" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
          </div>

          <div class="mt-4">
            <button type="submit"
              class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-lg shadow-sm">
              Reset Password <span aria-hidden="true">➜</span>
            </button>
          </div>
        </form>

        <p class="text-center text-sm text-white/80 mt-6">
          <a href="login.php" class="text-blue-300 hover:underline">Kembali ke Login</a>
        </p>
      </div>
    </section>
  </main>
</body>
</html>