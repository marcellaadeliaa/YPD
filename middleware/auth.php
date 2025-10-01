<?php
function checkAuth() {
    session_start();
    
    if (!isset($_SESSION['user'])) {
        header("Location: ../login_karyawan.php");
        exit();
    }
    
    // Cek session expiry (optional)
    if (isset($_SESSION['user']['login_time']) && (time() - $_SESSION['user']['login_time'] > 3600)) {
        // Session expired setelah 1 jam
        session_destroy();
        header("Location: ../login_karyawan.php?error=session_expired");
        exit();
    }
    
    return $_SESSION['user'];
}

function requireRole($allowed_roles) {
    $user = checkAuth();
    
    if (!in_array($user['role'], (array)$allowed_roles)) {
        header("Location: ../unauthorized.php");
        exit();
    }
    
    return $user;
}
?>