<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $sql = "UPDATE users SET reset_token='$token', reset_expires='$expires' WHERE email='$email'";
    if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
        $link = "http://localhost/reset_password.php?token=$token";
        $msg = "Link reset password: <a href='$link'>$link</a>";
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lupa Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="bg-white p-8 rounded-lg shadow-md w-[400px]">
    <h2 class="text-2xl font-bold mb-4">Lupa Password</h2>
    <?php if (!empty($msg)): ?>
      <p class="text-green-600"><?php echo $msg; ?></p>
    <?php elseif (!empty($error)): ?>
      <p class="text-red-600"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="email" name="email" required placeholder="Masukkan email"
        class="w-full px-4 py-2 border rounded-lg">
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg">Kirim Link Reset</button>
    </form>
  </div>
</body>
</html>
