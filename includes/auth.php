<?php
// Mulai sesi jika belum aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect ke halaman login jika user belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu untuk mengakses halaman tersebut!";
    header("Location: login.php");
    exit;
}
?>
