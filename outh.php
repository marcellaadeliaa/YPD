<?php
session_start();
include 'config.php'; // Make sure you have a file to connect to the database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validate input
    if (empty($_POST['login_input']) || empty($_POST['password']) || empty($_POST['role'])) {
        header("Location: login_karyawan.php?error=missing");
        exit();
    }

    $login_input = $_POST['login_input'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // 2. Prepare SQL query to check credentials AND role
    // This query checks if a user exists with the given username/code, password, AND role.
    $sql = "SELECT * FROM data_karyawan WHERE (kode_karyawan = ? OR nama_lengkap = ?) AND password = ? AND role = ? AND status_aktif = 'aktif'";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Note: The password should be hashed. The following assumes plain text for simplicity,
    // based on the provided SQL and JS files. In a real application, use password_hash() and password_verify().
    $stmt->bind_param("ssss", $login_input, $login_input, $password, $role);
    
    // 3. Execute and process result
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User found with matching credentials and role
        $user = $result->fetch_assoc();
        
        // 4. Set session variables and redirect
        $_SESSION['user'] = $user; // Store user data in session
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php"); // Redirect to the dashboard
        exit();
    } else {
        // No user found with that combination of username, password, and role
        header("Location: login_karyawan.php?error=invalid");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login_karyawan.php");
    exit();
}
?>