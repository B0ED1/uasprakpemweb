<?php
// Fitur Cepat Berpindah Akun
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

if (isset($_GET['id'])) {
    $target_id = (int) $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM tb_users WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['success'] = "Berhasil beralih ke akun: " . htmlspecialchars($user['nama_lengkap']) . "!";
        } else {
            $_SESSION['error'] = "Akun tidak ditemukan.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Kesalahan database: " . $e->getMessage();
    }
}

// Redirect kembali ke halaman asal (referer)
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: " . $referer);
exit;
