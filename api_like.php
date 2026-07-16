<?php
// Proteksi session login & database config
require_once 'includes/auth.php';
require_once 'config/database.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$figure_id = isset($_POST['figure_id']) ? (int) $_POST['figure_id'] : 0;

if ($figure_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID Figure tidak valid.']);
    exit;
}

try {
    // Periksa apakah figure ada
    $check_stmt = $pdo->prepare("SELECT id FROM tb_figures WHERE id = :id");
    $check_stmt->execute(['id' => $figure_id]);
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Figure tidak ditemukan.']);
        exit;
    }

    // Periksa status like saat ini
    $like_stmt = $pdo->prepare("SELECT id FROM tb_likes WHERE user_id = :user_id AND figure_id = :figure_id");
    $like_stmt->execute(['user_id' => $user_id, 'figure_id' => $figure_id]);
    $existing_like = $like_stmt->fetch();

    if ($existing_like) {
        // Hapus status Like
        $delete_stmt = $pdo->prepare("DELETE FROM tb_likes WHERE user_id = :user_id AND figure_id = :figure_id");
        $delete_stmt->execute(['user_id' => $user_id, 'figure_id' => $figure_id]);
        $liked = false;
    } else {
        // Tambahkan status Like
        $insert_stmt = $pdo->prepare("INSERT INTO tb_likes (user_id, figure_id) VALUES (:user_id, :figure_id)");
        $insert_stmt->execute(['user_id' => $user_id, 'figure_id' => $figure_id]);
        $liked = true;
    }

    // Hitung total like terbaru untuk figure tersebut
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_likes WHERE figure_id = :figure_id");
    $count_stmt->execute(['figure_id' => $figure_id]);
    $likes_count = (int) $count_stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $likes_count
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Gagal mengubah status: ' . $e->getMessage()]);
}
?>
