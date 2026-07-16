<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);

// Ambil daftar user untuk quick switcher jika session aktif dan PDO terkoneksi
$navbar_users = [];
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt_users = $pdo->query("SELECT id, username, nama_lengkap FROM tb_users ORDER BY nama_lengkap");
        $navbar_users = $stmt_users->fetchAll();
    } catch (PDOException $e) {
        // Abaikan
    }
}
?>
<nav class="glass-effect sticky top-0 z-40 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo Section -->
            <div class="flex items-center">
                <a href="index.php" class="flex items-center space-x-2 group">
                    <!-- Icon Sphere/Figure -->
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-slate-800">
                        Figu<span class="text-brand-600">Sphere</span>
                    </span>
                </a>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Desktop Navigation (Middle & Right) -->
                <div class="flex items-center space-x-1.5 sm:space-x-3 md:space-x-8">
                    <!-- Navigation Links -->
                    <div class="flex items-center space-x-0.5 sm:space-x-1.5 md:space-x-3">
                        <a href="index.php" class="inline-flex items-center px-2 sm:px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200 <?= $current_page == 'index.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                            </svg>
                            <span class="hidden sm:inline-block">Dashboard</span>
                        </a>
                        <a href="add.php" class="inline-flex items-center px-2 sm:px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200 <?= $current_page == 'add.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline-block">Tambah Figure</span>
                        </a>
                    </div>

                    <!-- Quick Account Switcher -->
                    <?php if (count($navbar_users) > 1): ?>
                        <div class="relative">
                            <button id="switcher-btn" type="button" class="inline-flex items-center justify-center px-2 sm:px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-brand-600 hover:bg-brand-50 hover:border-brand-200 transition-all duration-200 focus:outline-none" title="Ganti Akun Cepat">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="hidden md:inline-block text-sm font-semibold">Ganti Akun</span>
                            </button>
                            <!-- Dropdown Menu -->
                            <div id="switcher-dropdown" class="absolute right-0 mt-2 w-56 rounded-2xl bg-white border border-slate-200/80 shadow-lg py-2 hidden opacity-0 transition-all duration-200 z-50">
                                <div class="px-4 py-1.5 border-b border-slate-100 mb-1.5">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilih Akun</span>
                                </div>
                                <?php foreach ($navbar_users as $u): ?>
                                    <a href="switch_user.php?id=<?= $u['id'] ?>" class="flex items-center justify-between px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors duration-150 <?= $u['id'] == $_SESSION['user_id'] ? 'bg-slate-50/50 font-bold text-brand-600' : '' ?>">
                                        <div class="truncate max-w-[150px] text-left">
                                            <span class="block truncate"><?= htmlspecialchars($u['nama_lengkap']) ?></span>
                                            <span class="block text-[10px] font-normal text-slate-400">@<?= htmlspecialchars($u['username']) ?></span>
                                        </div>
                                        <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                            <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- User Profile Info & Logout -->
                    <div class="flex items-center space-x-2 sm:space-x-4 border-l border-slate-200 pl-2 sm:pl-6">
                        <div class="text-right hidden sm:block">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">MEMBER</span>
                            <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
                        </div>
                        
                        <a href="logout.php" class="inline-flex items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 transition-all duration-200" title="Logout dari Sistem">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
