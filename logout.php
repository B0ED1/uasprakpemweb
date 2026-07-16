<?php
/**
 * FiguSphere - Logout Logic
 * Menghapus seluruh session aktif dan mengalihkan user ke halaman login.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Kosongkan semua data session
$_SESSION = array();

// 2. Hancurkan session cookie jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session di server
session_destroy();

// 4. Mulai session baru sesaat untuk membawa pesan flash sukses logout
session_start();
$_SESSION['success'] = "Anda telah berhasil keluar dari sistem.";

header("Location: login.php");
exit;
?>
