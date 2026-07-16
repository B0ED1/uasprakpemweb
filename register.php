<?php
// Halaman Registrasi FiguSphere
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Jika sudah login, langsung alihkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validasi isian formulir
    if (empty($username) || empty($nama_lengkap) || empty($password)) {
        $error = "Semua input wajib diisi!";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal harus 4 karakter.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter.";
    } else {
        try {
            // Periksa ketersediaan username
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM tb_users WHERE username = :username");
            $checkStmt->execute(['username' => $username]);
            $usernameExists = $checkStmt->fetchColumn();

            if ($usernameExists > 0) {
                $error = "Username sudah digunakan oleh orang lain!";
            } else {
                // Enkripsi password & Daftarkan user baru
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO tb_users (username, password, nama_lengkap) VALUES (:username, :password, :nama_lengkap)");
                $stmt->execute([
                    'username' => $username,
                    'password' => $hashedPassword,
                    'nama_lengkap' => $nama_lengkap
                ]);

                $_SESSION['success'] = "Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error database: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | FiguSphere</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        indigo: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center relative overflow-hidden px-4">
    <!-- Background dynamic gradient elements -->
    <div class="absolute w-[400px] h-[400px] rounded-full bg-brand-600/20 blur-[80px] -top-20 -left-20"></div>
    <div class="absolute w-[400px] h-[400px] rounded-full bg-indigo-500/20 blur-[80px] -bottom-20 -right-20"></div>

    <div class="max-w-md w-full animate-fade-in relative z-10 py-10">
        <!-- Brand Logo / Title -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 items-center justify-center shadow-lg shadow-brand-500/30 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Figu<span class="text-brand-400">Sphere</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Figure Collection Management System</p>
        </div>

        <!-- Alert Box from errors -->
        <?php if ($error !== null): ?>
            <div class="alert-box mb-5 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-300 rounded-2xl flex items-center space-x-3 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Glassmorphism Register Card -->
        <div class="glass-dark rounded-3xl p-8 border border-slate-800 shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-6">Pendaftaran Akun</h2>
            
            <form action="register.php" method="POST" class="space-y-5">
                <!-- Nama Lengkap -->
                <div>
                    <label for="nama_lengkap" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" placeholder="Nama lengkap Anda..." required class="block w-full pl-11 pr-4 py-3 bg-slate-800/80 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" id="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Minimal 4 karakter..." required class="block w-full pl-11 pr-4 py-3 bg-slate-800/80 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Minimal 6 karakter..." required class="block w-full pl-11 pr-4 py-3 bg-slate-800/80 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Action Button -->
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-700 hover:to-indigo-600 text-white font-semibold rounded-2xl text-sm shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30 transition-all duration-200 mt-2">
                    Daftar Sekarang
                </button>
            </form>
            
            <!-- Helper text -->
            <div class="mt-6 text-center text-sm text-slate-500">
                Sudah punya akun? <a href="login.php" class="text-brand-400 hover:text-brand-300 font-semibold transition-colors duration-200">Masuk di sini</a>
            </div>
        </div>
    </div>

    <!-- Script reference for alert fade-out -->
    <script src="assets/js/main.js"></script>
</body>
</html>
