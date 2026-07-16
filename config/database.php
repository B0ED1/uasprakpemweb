<?php
// Koneksi database & inisialisasi otomatis (migrasi table) menggunakan PDO.
$host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "db_figusphere";

try {
    // 1. Koneksi awal ke server MySQL (tanpa memilih database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. Koneksi dengan memilih database target
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 4. Buat tabel tb_users jika belum ada
    $userTableQuery = "CREATE TABLE IF NOT EXISTS tb_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($userTableQuery);

    // 5. Migrasi tb_users: Tambah kolom role jika belum ada
    $userColumns = $pdo->query("SHOW COLUMNS FROM tb_users LIKE 'role'")->fetchAll();
    if (empty($userColumns)) {
        $pdo->exec("ALTER TABLE tb_users ADD COLUMN role VARCHAR(20) DEFAULT 'user' AFTER nama_lengkap;");
        $pdo->exec("UPDATE tb_users SET role = 'admin' WHERE username = 'admin';");
    }
    
    // 6. Tambahkan akun admin default jika tabel tb_users kosong
    $userCount = $pdo->query("SELECT COUNT(*) FROM tb_users")->fetchColumn();
    if ($userCount == 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $seedUser = $pdo->prepare("INSERT INTO tb_users (username, password, nama_lengkap, role) VALUES ('admin', :password, 'Administrator FiguSphere', 'admin')");
        $seedUser->execute(['password' => $defaultPassword]);
    }
    
    // 7. Buat tabel tb_figures jika belum ada
    $figuresTableQuery = "CREATE TABLE IF NOT EXISTS tb_figures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama_figure VARCHAR(255) NOT NULL,
        karakter VARCHAR(100) NOT NULL,
        seri_anime VARCHAR(150) NOT NULL,
        produsen VARCHAR(100) NOT NULL,
        skala_ukuran VARCHAR(50),
        harga INT,
        foto_figure VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($figuresTableQuery);

    // 8. Migrasi tb_figures: Tambah user_id & foreign key
    $columns = $pdo->query("SHOW COLUMNS FROM tb_figures LIKE 'user_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE tb_figures ADD COLUMN user_id INT NULL AFTER id;");
        $firstUserId = $pdo->query("SELECT id FROM tb_users ORDER BY id LIMIT 1")->fetchColumn();
        if ($firstUserId) {
            $pdo->exec("UPDATE tb_figures SET user_id = $firstUserId WHERE user_id IS NULL;");
        }
        $pdo->exec("ALTER TABLE tb_figures MODIFY COLUMN user_id INT NOT NULL;");
        try {
            $pdo->exec("ALTER TABLE tb_figures ADD CONSTRAINT fk_figures_users FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE;");
        } catch (PDOException $ex) {
            // Abaikan jika constraint sudah ada
        }
    }

    // 9. Buat tabel tb_likes jika belum ada
    $likesTableQuery = "CREATE TABLE IF NOT EXISTS tb_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        figure_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE,
        FOREIGN KEY (figure_id) REFERENCES tb_figures(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_figure (user_id, figure_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($likesTableQuery);

    // 10. Buat tabel tb_comments jika belum ada
    $commentsTableQuery = "CREATE TABLE IF NOT EXISTS tb_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        figure_id INT NOT NULL,
        komentar TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE,
        FOREIGN KEY (figure_id) REFERENCES tb_figures(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($commentsTableQuery);
    
} catch (PDOException $e) {
    die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 8px; margin: 20px;'>
            <h3 style='margin-top:0;'>Gagal Menghubungkan & Menginisialisasi Database!</h3>
            <p>Error: <strong>{$e->getMessage()}</strong></p>
            <hr style='border-color: #fca5a5; margin: 15px 0;'>
            <p style='font-size: 14px;'>Pastikan MySQL server di XAMPP / Laragon Anda sudah aktif. Gunakan username <strong>root</strong> tanpa password secara default.</p>
         </div>");
}
?>
