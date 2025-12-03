<?php
session_start();
include 'config.php';
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('63606362871-93d3u1s5ganl9io17fm8u1hk9j2s755q.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/YPD/callback.php');
$client->addScope("email");
$client->addScope("profile");
$google_login_url = $client->createAuthUrl();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, email, password, nama_lengkap FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

        header("Location: formpelamar.php");
        exit;
    } else {
        $error = "Email atau password salah!";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login — Pelamar</title>
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
          <h1 class="text-3xl md:text-4xl text-white font-bold">Login</h1>
          <p class="text-sm text-white/80 mt-2">Masuk untuk melanjutkan ke dashboard Yayasan</p>
        </header>

        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <strong class="font-bold">Terjadi Kesalahan!</strong>
            <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
          </div>
          <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
          <p class="text-green-400 text-sm mb-3 text-center">
            ✅ Pendaftaran berhasil, silakan login.
          </p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <p class="text-red-400 text-sm mb-3 text-center"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4" autocomplete="off">
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

        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-white/40"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="bg-[#1E105E] px-2 text-white/80">Atau</span>
          </div>
        </div>

        <div>
          <a href="<?php echo $google_login_url; ?>"
            class="w-full inline-flex items-center justify-center gap-3 py-3 px-4 rounded-xl bg-white hover:bg-gray-200 text-gray-800 font-semibold shadow-sm">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.223,0-9.649-3.317-11.28-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.574l6.19,5.238C42.021,35.591,44,30.138,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
            </svg>
            <span>Login dengan Google</span>
          </a>
        </div>
        <p class="text-center text-sm text-white/80 mt-6"> <a href="forgot_password.php" class="text-blue-300 hover:underline">Lupa password?</a>
        </p>

        <p class="text-center text-sm text-white/80 mt-3"> Belum punya akun?
          <a href="register.php" class="text-blue-300 hover:underline">Daftar disini</a>
        </p>
      </div>
    </section>
  </main>
</body>
</html>