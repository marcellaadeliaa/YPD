<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Validasi password
    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) {
        $error = "Password minimal 8 karakter dan harus mengandung huruf & angka.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak sesuai.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password) VALUES ('$email', '$hash')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?success=1");
            exit;
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar — Yayasan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
  <!-- Background -->
  <div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-[url('image/gedungyayasan.png')] bg-center bg-cover"></div>
    <div class="absolute inset-0 bg-black/40"></div>
  </div>

  <!-- Container -->
  <main class="min-h-screen flex items-center justify-center p-6">
    <section class="w-full max-w-[500px]">
      <div class="mx-auto bg-[#1E105E]/95 rounded-2xl shadow-2xl p-10 md:p-12">
        <!-- Header -->
        <header class="text-center mb-6">
          <h1 class="text-3xl md:text-4xl text-white font-bold">Daftar</h1>
          <p class="text-sm text-white/80 mt-2">Mari bergabung untuk pembangunan dan pengembangan masyarakat</p>
        </header>

        <!-- Pesan error -->
        <?php if (!empty($error)): ?>
          <p class="text-red-400 text-sm mb-3"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-white/90 mb-2">Masukkan Email</label>
            <input id="email" name="email" type="email" placeholder="Email" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-white/90 mb-2">Masukkan Password</label>
            <input id="password" name="password" type="password" placeholder="Masukkan Password" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <p class="text-xs text-white/70 mt-1">*Min. 8 karakter, gabungan angka dan huruf.</p>
          </div>

          <div>
            <label for="confirm" class="block text-sm font-medium text-white/90 mb-2">Konfirmasi Password</label>
            <input id="confirm" name="confirm" type="password" placeholder="Masukkan ulang password" required
              class="w-full py-3 px-4 rounded-full bg-white/90 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
          </div>

          <div class="mt-4">
            <button type="submit"
              class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold text-lg shadow-sm">
              Daftar <span aria-hidden="true">➜</span>
            </button>
          </div>
        </form>

        <!-- Link login -->
        <p class="text-center text-sm text-white/80 mt-6">
          Sudah punya akun?
          <a href="login.php" class="text-blue-300 hover:underline">Login disini</a>
        </p>
      </div>
    </section>
  </main>
</body>
</html>
