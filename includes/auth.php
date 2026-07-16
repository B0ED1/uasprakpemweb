<?php
/**
 * FiguSphere - Authentication Middleware
 * Menyertakan pelindung sesi. Jika belum login, user akan dialihkan ke login.php.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu untuk mengakses halaman tersebut!";
    header("Location: login.php");
    exit;
}
?>
