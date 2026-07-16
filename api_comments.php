<?php
// Proteksi session login & database config
require_once 'includes/auth.php';
require_once 'config/database.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$figure_id = isset($_REQUEST['figure_id']) ? (int) $_REQUEST['figure_id'] : 0;

if ($figure_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID Figure tidak valid.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Periksa apakah figure ada
    $check_stmt = $pdo->prepare("SELECT id FROM tb_figures WHERE id = :id");
    $check_stmt->execute(['id' => $figure_id]);
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Figure tidak ditemukan.']);
        exit;
    }

    if ($method === 'POST') {
        $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';
        if (empty($komentar)) {
            echo json_encode(['success' => false, 'error' => 'Komentar tidak boleh kosong.']);
            exit;
        }

        // Simpan komentar baru
        $insert_stmt = $pdo->prepare("INSERT INTO tb_comments (user_id, figure_id, komentar) VALUES (:user_id, :figure_id, :komentar)");
        $insert_stmt->execute([
            'user_id' => $user_id,
            'figure_id' => $figure_id,
            'komentar' => $komentar
        ]);
    }

    // Ambil daftar komentar untuk figure tersebut
    $comments_stmt = $pdo->prepare("SELECT c.*, u.nama_lengkap, u.username FROM tb_comments c JOIN tb_users u ON c.user_id = u.id WHERE c.figure_id = :figure_id ORDER BY c.created_at ASC");
    $comments_stmt->execute(['figure_id' => $figure_id]);
    $comments = $comments_stmt->fetchAll();

    // Format tanggal waktu agar lebih mudah dibaca
    foreach ($comments as &$c) {
        $time = strtotime($c['created_at']);
        $c['formatted_time'] = date('d M Y, H:i', $time);
    }

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Gagal memproses data komentar: ' . $e->getMessage()]);
}
?>
