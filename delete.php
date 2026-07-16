<?php
/**
 * FiguSphere - Delete Action Logic
 * Menghapus data dari database serta membersihkan file foto terkait dari server.
 * Dilindungi oleh session login & pengecekan kepemilikan data.
 */

// Proteksi halaman dengan session login
require_once 'includes/auth.php';
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($id <= 0) {
    $_SESSION['error'] = "ID Figure tidak valid!";
    header("Location: index.php");
    exit;
}

try {
    // 1. Ambil nama file foto dan validasi kepemilikan record sebelum menghapus
    if ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT foto_figure FROM tb_figures WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } else {
        $stmt = $pdo->prepare("SELECT foto_figure FROM tb_figures WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    }
    $figure = $stmt->fetch();
    
    if ($figure) {
        // Hapus file gambar dari server jika ada
        if (!empty($figure['foto_figure'])) {
            $file_path = 'assets/uploads/' . $figure['foto_figure'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // 2. Hapus record dari database
        if ($_SESSION['role'] === 'admin') {
            $delete_stmt = $pdo->prepare("DELETE FROM tb_figures WHERE id = :id");
            $delete_stmt->execute(['id' => $id]);
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM tb_figures WHERE id = :id AND user_id = :user_id");
            $delete_stmt->execute(['id' => $id, 'user_id' => $user_id]);
        }
        
        $_SESSION['success'] = "Koleksi figure berhasil dihapus dari sistem!";
    } else {
        $_SESSION['error'] = "Koleksi figure tidak ditemukan atau Anda tidak memiliki akses!";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
}

// Kembalikan ke dashboard utama
header("Location: index.php");
exit;
?>
