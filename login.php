<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login — Yayasan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
  <!-- Background -->
  <div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-[url('image/gedungyayasan.png')] bg-center bg-cover"></div>
    <div class="absolute inset-0 bg-black/40"></div>
  </div>

  <!-- Container -->
  <main class="min-h-screen flex items-center justify-center p-6 relative z-10">
    <section class="w-full max-w-[500px]">
      <div class="mx-auto bg-[#1E105E]/95 rounded-2xl shadow-2xl p-10 md:p-12">
        <!-- Header -->
        <header class="text-center mb-6">
          <h1 class="text-3xl md:text-4xl text-white font-bold">Login</h1>
          <p class="text-sm text-white/80 mt-2">Masuk untuk melanjutkan ke dashboard Yayasan</p>
        </header>

        <!-- Pesan sukses -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
          <p class="text-green-400 text-sm mb-3 text-center">
            ✅ Pendaftaran berhasil, silakan login.
          </p>
        <?php endif; ?>

        <!-- Pesan error -->
        <?php if (!empty($error)): ?>
          <p class="text-red-400 text-sm mb-3 text-center"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-white/90 mb-2">Email</label>
            <input id="email" name="email" type="email" placeholder="Masukkan email" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-white/90 mb-2">Password</label>
            <input id="password" name="password" type="password" placeholder="Masukkan password" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
          </div>

          <div class="mt-4">
            <button type="submit"
              class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-lg shadow-sm">
              Login <span aria-hidden="true">➜</span>
            </button>
          </div>
        </form>

        <!-- Link daftar -->
        <p class="text-center text-sm text-white/80 mt-6">
          Belum punya akun?
          <a href="daftar.php" class="text-blue-300 hover:underline">Daftar disini</a>
        </p>
      </div>
    </section>
  </main>
</body>
</html>
