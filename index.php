<?php
// Proteksi halaman dengan session login
require_once 'includes/auth.php';

// Set page title dan load db config
$pageTitle = "Dashboard Koleksi";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Ambil data untuk statistik dashboard
if ($is_admin) {
    // 1. Total Figure
    $total_stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_figures");
    $total_figures = $total_stmt->fetch()['total'];

    // 2. Total Investasi (Harga)
    $price_stmt = $pdo->query("SELECT SUM(harga) as total_harga FROM tb_figures");
    $total_value = $price_stmt->fetch()['total_harga'] ?? 0;

    // 3. Produsen Terpopuler (Brand Favorit)
    $brand_stmt = $pdo->query("SELECT produsen, COUNT(*) as jml FROM tb_figures GROUP BY produsen ORDER BY jml DESC LIMIT 1");
    $fav_brand_res = $brand_stmt->fetch();
    $fav_brand = $fav_brand_res ? $fav_brand_res['produsen'] . " ({$fav_brand_res['jml']})" : "-";

    // 4. Seri Anime Terbanyak
    $series_stmt = $pdo->query("SELECT seri_anime, COUNT(*) as jml FROM tb_figures GROUP BY seri_anime ORDER BY jml DESC LIMIT 1");
    $top_series_res = $series_stmt->fetch();
    $top_series = $top_series_res ? $top_series_res['seri_anime'] . " ({$top_series_res['jml']})" : "-";

    // Ambil list unik untuk filter dropdown
    $list_series = $pdo->query("SELECT DISTINCT seri_anime FROM tb_figures WHERE seri_anime != '' ORDER BY seri_anime")->fetchAll(PDO::FETCH_COLUMN);
    $list_brands = $pdo->query("SELECT DISTINCT produsen FROM tb_figures WHERE produsen != '' ORDER BY produsen")->fetchAll(PDO::FETCH_COLUMN);
} else {
    // 1. Total Figure
    $total_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_figures WHERE user_id = :user_id");
    $total_stmt->execute(['user_id' => $user_id]);
    $total_figures = $total_stmt->fetch()['total'];

    // 2. Total Investasi (Harga)
    $price_stmt = $pdo->prepare("SELECT SUM(harga) as total_harga FROM tb_figures WHERE user_id = :user_id");
    $price_stmt->execute(['user_id' => $user_id]);
    $total_value = $price_stmt->fetch()['total_harga'] ?? 0;

    // 3. Produsen Terpopuler (Brand Favorit)
    $brand_stmt = $pdo->prepare("SELECT produsen, COUNT(*) as jml FROM tb_figures WHERE user_id = :user_id GROUP BY produsen ORDER BY jml DESC LIMIT 1");
    $brand_stmt->execute(['user_id' => $user_id]);
    $fav_brand_res = $brand_stmt->fetch();
    $fav_brand = $fav_brand_res ? $fav_brand_res['produsen'] . " ({$fav_brand_res['jml']})" : "-";

    // 4. Seri Anime Terbanyak
    $series_stmt = $pdo->prepare("SELECT seri_anime, COUNT(*) as jml FROM tb_figures WHERE user_id = :user_id GROUP BY seri_anime ORDER BY seri_anime DESC LIMIT 1");
    $series_stmt->execute(['user_id' => $user_id]);
    $top_series_res = $series_stmt->fetch();
    $top_series = $top_series_res ? $top_series_res['seri_anime'] . " ({$top_series_res['jml']})" : "-";

    // Ambil list unik untuk filter dropdown
    $list_series_stmt = $pdo->prepare("SELECT DISTINCT seri_anime FROM tb_figures WHERE user_id = :user_id AND seri_anime != '' ORDER BY seri_anime");
    $list_series_stmt->execute(['user_id' => $user_id]);
    $list_series = $list_series_stmt->fetchAll(PDO::FETCH_COLUMN);

    $list_brands_stmt = $pdo->prepare("SELECT DISTINCT produsen FROM tb_figures WHERE user_id = :user_id AND produsen != '' ORDER BY produsen");
    $list_brands_stmt->execute(['user_id' => $user_id]);
    $list_brands = $list_brands_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Tangani Pencarian dan Filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_series = isset($_GET['series']) ? trim($_GET['series']) : '';
$filter_brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';

$params = [];
if ($is_admin) {
    $query = "SELECT f.*, u.nama_lengkap as owner_name FROM tb_figures f JOIN tb_users u ON f.user_id = u.id WHERE 1=1";
} else {
    $query = "SELECT f.*, u.nama_lengkap as owner_name FROM tb_figures f JOIN tb_users u ON f.user_id = u.id WHERE f.user_id = :user_id";
    $params['user_id'] = $user_id;
}

if ($search !== '') {
    $query .= " AND (f.nama_figure LIKE :search OR f.karakter LIKE :search OR f.seri_anime LIKE :search OR f.produsen LIKE :search OR u.nama_lengkap LIKE :search)";
    $params['search'] = "%$search%";
}

if ($filter_series !== '') {
    $query .= " AND f.seri_anime = :series";
    $params['series'] = $filter_series;
}

if ($filter_brand !== '') {
    $query .= " AND f.produsen = :brand";
    $params['brand'] = $filter_brand;
}

// Urutkan berdasarkan koleksi terbaru
$query .= " ORDER BY f.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$figures = $stmt->fetchAll();
?>

<main class="flex-grow max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 animate-fade-in">
    <!-- Notification Alerts (jika ada feedback dari add/edit/delete) -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert-box mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center space-x-3 shadow-sm transition-opacity duration-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium"><?= htmlspecialchars($_SESSION['success']) ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-box mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center space-x-3 shadow-sm transition-opacity duration-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium"><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Dashboard Header & Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 sm:mb-8 space-y-3 md:space-y-0">
        <div>
            <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Koleksi Figure Anda</h1>
            <p class="text-slate-500 mt-0.5 sm:mt-1 text-xs sm:text-base">Pantau dan kelola inventaris action figure favoritmu.</p>
        </div>
        <a href="add.php" class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 text-white font-semibold text-xs sm:text-sm hover:from-brand-700 hover:to-indigo-700 shadow-md shadow-brand-500/10 hover:shadow-lg hover:shadow-brand-500/20 transition-all duration-200 hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Figure Baru
        </a>
    </div>

    <!-- Statistics Panel -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
        <!-- Stat 1 -->
        <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="p-2.5 sm:p-3 bg-brand-50 rounded-2xl text-brand-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] sm:text-sm font-medium text-slate-500">Total Koleksi</p>
                <p class="text-base sm:text-2xl font-bold text-slate-800 truncate"><?= $total_figures ?> <span class="text-[10px] sm:text-xs font-normal text-slate-400">Unit</span></p>
            </div>
        </div>
        <!-- Stat 2 -->
        <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="p-2.5 sm:p-3 bg-amber-50 rounded-2xl text-amber-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[11px] sm:text-sm font-medium text-slate-500">Total Investasi</p>
                <p class="text-sm sm:text-2xl font-bold text-slate-800 truncate" title="Rp <?= number_format($total_value, 0, ',', '.') ?>">Rp <?= number_format($total_value, 0, ',', '.') ?></p>
            </div>
        </div>
        <!-- Stat 3 -->
        <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="p-2.5 sm:p-3 bg-emerald-50 rounded-2xl text-emerald-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[11px] sm:text-sm font-medium text-slate-500">Brand Terfavorit</p>
                <p class="text-xs sm:text-lg font-bold text-slate-800 truncate" title="<?= htmlspecialchars($fav_brand) ?>"><?= htmlspecialchars($fav_brand) ?></p>
            </div>
        </div>
        <!-- Stat 4 -->
        <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="p-2.5 sm:p-3 bg-rose-50 rounded-2xl text-rose-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[11px] sm:text-sm font-medium text-slate-500">Seri Terbanyak</p>
                <p class="text-xs sm:text-lg font-bold text-slate-800 truncate" title="<?= htmlspecialchars($top_series) ?>"><?= htmlspecialchars($top_series) ?></p>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/60 shadow-sm mb-6 sm:mb-8">
        <form method="GET" action="index.php" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
                <!-- Search field -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Cari Figure</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama figure, karakter, anime..." class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Anime Filter -->
                <div>
                    <label for="series" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Seri / Anime</label>
                    <select name="series" id="series" class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 text-sm">
                        <option value="">Semua Seri</option>
                        <?php foreach ($list_series as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $filter_series === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Brand Filter -->
                <div>
                    <label for="brand" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Produsen / Brand</label>
                    <select name="brand" id="brand" class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 text-sm">
                        <option value="">Semua Brand</option>
                        <?php foreach ($list_brands as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= $filter_brand === $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <?php if ($search !== '' || $filter_series !== '' || $filter_brand !== ''): ?>
                    <a href="index.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors duration-200 text-xs font-bold flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Reset Filter
                    </a>
                <?php endif; ?>
                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-colors duration-200 shadow-md shadow-brand-500/10 text-xs font-bold flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Figure Collection List -->
    <?php if (empty($figures)): ?>
        <div class="bg-white border border-slate-200/60 rounded-3xl p-12 text-center shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Tidak ada figure ditemukan</h3>
            <p class="text-slate-500 text-sm mt-1">Coba sesuaikan pencarian atau filter Anda, atau tambahkan figure baru ke koleksi.</p>
            <?php if ($search !== '' || $filter_series !== '' || $filter_brand !== ''): ?>
                <a href="index.php" class="inline-flex items-center px-4 py-2 mt-4 text-xs font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-xl transition-all duration-200">
                    Hapus Semua Filter
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
            <?php foreach ($figures as $fig): ?>
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col hover-lift">
                    <!-- Figure Image Container -->
                    <?php if (!empty($fig['foto_figure']) && file_exists('assets/uploads/' . $fig['foto_figure'])): ?>
                        <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden group cursor-pointer" 
                             onclick="openImageModal('assets/uploads/<?= htmlspecialchars($fig['foto_figure']) ?>', '<?= addslashes(htmlspecialchars($fig['nama_figure'])) ?>', '<?= addslashes(htmlspecialchars($fig['karakter'])) ?>', '<?= addslashes(htmlspecialchars($fig['seri_anime'])) ?>', 'Rp <?= number_format($fig['harga'], 0, ',', '.') ?>', '<?= addslashes(htmlspecialchars($fig['skala_ukuran'])) ?>', '<?= addslashes(htmlspecialchars($fig['produsen'])) ?>')"
                             title="Klik untuk memperbesar gambar">
                            <img src="assets/uploads/<?= htmlspecialchars($fig['foto_figure']) ?>" alt="<?= htmlspecialchars($fig['nama_figure']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <?php else: ?>
                        <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden group">
                            <!-- Beautiful SVG Fallback when no image is uploaded -->
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-tr from-brand-50 to-indigo-50/60 text-brand-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs font-semibold uppercase tracking-wider text-brand-500/80">FiguSphere figure</span>
                            </div>
                    <?php endif; ?>

                        <!-- Skala Badge -->
                        <?php if (!empty($fig['skala_ukuran'])): ?>
                            <span class="absolute top-3 right-3 px-2.5 py-1 bg-slate-900/80 backdrop-blur-sm text-white font-bold text-[10px] uppercase tracking-wider rounded-lg z-10">
                                <?= htmlspecialchars($fig['skala_ukuran']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Figure Details -->
                    <div class="p-3 sm:p-5 flex-grow flex flex-col justify-between">
                        <div>
                            <!-- Brand & Series -->
                            <div class="flex items-center space-x-1.5 sm:space-x-2 mb-1 sm:mb-1.5 flex-wrap gap-y-1">
                                <span class="px-1.5 sm:px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[8px] sm:text-[10px] font-bold uppercase tracking-wide">
                                    <?= htmlspecialchars($fig['produsen']) ?>
                                </span>
                                <span class="text-[9px] sm:text-[11px] font-medium text-slate-400 truncate max-w-[80px] sm:max-w-[120px]" title="<?= htmlspecialchars($fig['seri_anime']) ?>">
                                    <?= htmlspecialchars($fig['seri_anime']) ?>
                                </span>
                            </div>

                            <!-- Name -->
                            <h4 class="text-xs sm:text-base font-bold text-slate-800 leading-snug line-clamp-2 mb-0.5 sm:mb-1" title="<?= htmlspecialchars($fig['nama_figure']) ?>">
                                <?= htmlspecialchars($fig['nama_figure']) ?>
                            </h4>

                            <!-- Character -->
                            <p class="text-[10px] sm:text-xs text-slate-500 mb-0.5 sm:mb-1">
                                Karakter: <span class="font-semibold text-slate-600"><?= htmlspecialchars($fig['karakter']) ?></span>
                            </p>

                            <!-- Owner (Only shown to Admin) -->
                            <?php if ($is_admin): ?>
                                <p class="text-[9px] sm:text-[11px] text-slate-400 mb-2 sm:mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 mr-1 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="truncate">Kolektor: <span class="font-bold text-brand-600 ml-0.5"><?= htmlspecialchars($fig['owner_name']) ?></span></span>
                                </p>
                            <?php else: ?>
                                <div class="mb-2 sm:mb-3"></div>
                            <?php endif; ?>
                        </div>

                        <!-- Price & Actions -->
                        <div class="border-t border-slate-100 pt-2 sm:pt-4 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <span class="block text-[8px] sm:text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Harga</span>
                                <span class="text-xs sm:text-base font-extrabold text-brand-600 truncate block">
                                    Rp <?= number_format($fig['harga'], 0, ',', '.') ?>
                                </span>
                            </div>
                            <!-- Action Buttons -->
                            <div class="flex space-x-1 sm:space-x-1.5 flex-shrink-0">
                                <a href="edit.php?id=<?= $fig['id'] ?>" class="p-1.5 sm:p-2 bg-slate-50 hover:bg-amber-50 text-slate-500 hover:text-amber-600 border border-slate-200 rounded-lg sm:rounded-xl transition-all duration-200" title="Edit Figure">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <button type="button" onclick="openDeleteModal('delete.php?id=<?= $fig['id'] ?>', '<?= addslashes(htmlspecialchars($fig['nama_figure'])) ?>')" class="p-1.5 sm:p-2 bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200 rounded-lg sm:rounded-xl transition-all duration-200" title="Hapus Figure">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Beautiful Custom Delete Confirmation Modal (Hidden by default) -->
<div id="delete-modal" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden transition-all duration-300">
    <div class="modal-content transform scale-95 opacity-0 bg-white rounded-3xl max-w-md w-full mx-4 p-6 shadow-2xl transition-all duration-300 border border-slate-100">
        <div class="text-center">
            <!-- Alert Icon -->
            <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-rose-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Hapus Koleksi Figure?</h3>
            <p class="text-slate-500 text-sm mt-2 px-2">
                Apakah Anda yakin ingin menghapus <strong id="delete-figure-name" class="text-slate-700"></strong> dari katalog koleksi? Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <!-- Modal Action Buttons -->
        <div class="flex items-center space-x-3 mt-6">
            <button type="button" onclick="closeDeleteModal()" class="w-full py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-2xl text-sm border border-slate-200 transition-colors duration-200">
                Batal
            </button>
            <a id="confirm-delete-btn" href="#" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-2xl text-sm text-center shadow-md shadow-rose-500/10 hover:shadow-lg transition-colors duration-200">
                Ya, Hapus
            </a>
        </div>
    </div>
</div>

<div id="image-lightbox-modal" class="fixed inset-0 z-50 items-center justify-center bg-slate-950/80 backdrop-blur-md hidden transition-all duration-300" onclick="closeImageModal(event)">
    <div class="relative max-w-4xl w-full mx-2 sm:mx-4 bg-white/10 backdrop-blur-xl rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col md:flex-row transform scale-95 opacity-0 transition-all duration-300 max-h-[90vh] overflow-y-auto md:max-h-none md:overflow-y-visible" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button type="button" onclick="closeImageModal(null)" class="absolute top-4 right-4 z-50 p-2 bg-slate-900/60 hover:bg-slate-900 text-white rounded-full transition-colors duration-200" title="Tutup">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Image Pane -->
        <div class="w-full md:w-3/5 bg-slate-950/50 flex items-center justify-center relative aspect-[4/3] md:aspect-auto md:min-h-[500px]">
            <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[80vh] md:max-h-[600px] object-contain">
        </div>

        <!-- Details Pane -->
        <div class="w-full md:w-2/5 p-6 md:p-8 bg-slate-900 text-white flex flex-col justify-between border-t md:border-t-0 md:border-l border-white/10">
            <div>
                <!-- Brand and Series -->
                <div class="flex items-center space-x-2 mb-3">
                    <span id="lightbox-brand" class="px-2.5 py-0.5 bg-brand-500/20 text-brand-400 rounded text-xs font-bold uppercase tracking-wider"></span>
                    <span id="lightbox-series" class="text-xs font-semibold text-slate-400"></span>
                </div>

                <!-- Figure Name -->
                <h3 id="lightbox-name" class="text-2xl font-extrabold text-white leading-tight mb-4"></h3>

                <div class="space-y-3.5 border-t border-white/10 pt-4">
                    <!-- Character -->
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400 font-medium">Karakter</span>
                        <span id="lightbox-char" class="font-bold text-slate-200"></span>
                    </div>

                    <!-- Scale -->
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400 font-medium">Skala / Ukuran</span>
                        <span id="lightbox-scale" class="font-bold text-slate-200"></span>
                    </div>

                    <!-- Value -->
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400 font-medium">Nilai Koleksi</span>
                        <span id="lightbox-price" class="text-lg font-black text-brand-400"></span>
                    </div>
                </div>
            </div>

            <!-- Footer signature or quick tips -->
            <div class="mt-8 text-[11px] text-slate-500 border-t border-white/5 pt-4 text-center">
                FiguSphere Premium Lightbox View • Ketuk di luar untuk menutup
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
