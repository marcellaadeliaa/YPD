<?php
require_once 'vendor/autoload.php';
require 'config.php';
session_start();

$client = new Google_Client();
$client->setClientId('63606362871-93d3u1s5ganl9io17fm8u1hk9j2s755q.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/YPD/callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        $_SESSION['error_message'] = 'Gagal otentikasi Google: ' . $token['error_description'];
        header('Location: login.php');
        exit();
    }

    $client->setAccessToken($token['access_token']);
    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    $email = $userInfo->email;
    $nama_lengkap = $userInfo->name ?: ''; 

    error_log("Google Login Attempt - Email: " . $email . ", Name: " . $nama_lengkap);

    $stmt = $conn->prepare("SELECT id, nama_lengkap FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
       
        if (empty($user['nama_lengkap']) && !empty($nama_lengkap)) {
            $update_stmt = $conn->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
            $update_stmt->bind_param("si", $nama_lengkap, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            error_log("Updated nama_lengkap for user ID: " . $user_id);
        }
        
        error_log("User found - ID: " . $user_id);
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO users (nama_lengkap, email, password) VALUES (?, ?, '')");
        $stmt_insert->bind_param("ss", $nama_lengkap, $email);

        if ($stmt_insert->execute()) {
            $user_id = $conn->insert_id;
            error_log("New user created - ID: " . $user_id);
        } else {
            error_log("INSERT ERROR: " . $stmt_insert->error);
            
            $stmt_alt = $conn->prepare("INSERT INTO users (nama_lengkap, email, password) VALUES (?, ?, '')");
            $nama_lengkap = $nama_lengkap ?: '';
            $stmt_alt->bind_param("ss", $nama_lengkap, $email);
            
            if ($stmt_alt->execute()) {
                $user_id = $conn->insert_id;
                error_log("New user created via alternative - ID: " . $user_id);
            } else {
                error_log("ALTERNATIVE INSERT ALSO FAILED: " . $stmt_alt->error);
                $_SESSION['error_message'] = "Gagal membuat akun: " . $stmt_alt->error;
                header('Location: login.php');
                exit();
            }
            $stmt_alt->close();
        }

        $stmt->close();
    }
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['nama_lengkap'] = $nama_lengkap;

    error_log("Login successful - User ID: " . $user_id . " redirecting to formpelamar.php");

    $check_stmt = $conn->prepare("SELECT MAX(id) as max_id, COUNT(*) as total FROM users");
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_data = $check_result->fetch_assoc();
    error_log("Table status - Max ID: " . $check_data['max_id'] . ", Total users: " . $check_data['total']);
    $check_stmt->close();

    $conn->close();

    header('Location: formpelamar.php');
    exit();

} else {
    $_SESSION['error_message'] = 'Kode otorisasi Google tidak ditemukan.';
    header('Location: login.php');
    exit();
}
?>