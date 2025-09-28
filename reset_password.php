<?php
include 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $sql = "SELECT * FROM users WHERE reset_token='$token' AND reset_expires > NOW()";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        die("Token tidak valid atau sudah kadaluarsa.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Password tidak sama.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password='$hash', reset_token=NULL, reset_expires=NULL WHERE reset_token='$token'";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?reset=1");
            exit;
        } else {
            $error = "Gagal reset password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="bg-white p-8 rounded-lg shadow-md w-[400px]">
    <h2 class="text-2xl font-bold mb-4">Reset Password</h2>
    <?php if (!empty($error)): ?>
      <p class="text-red-600"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
      <input type="password" name="password" required placeholder="Password baru"
        class="w-full px-4 py-2 border rounded-lg">
      <input type="password" name="confirm" required placeholder="Konfirmasi password"
        class="w-full px-4 py-2 border rounded-lg">
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg">Simpan Password</button>
    </form>
  </div>
</body>
</html>
