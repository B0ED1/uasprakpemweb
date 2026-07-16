<?php
// Proteksi halaman dengan session login
require_once 'includes/auth.php';

$pageTitle = "Edit Figure";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$errors = [];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Cek apakah id figure valid dan ada di database (dan milik user yang bersangkutan)
if ($id <= 0) {
    $_SESSION['error'] = "ID Figure tidak valid!";
    header("Location: index.php");
    exit;
}

try {
    if ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM tb_figures WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tb_figures WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    }
    $figure = $stmt->fetch();
    
    if (!$figure) {
        $_SESSION['error'] = "Koleksi figure tidak ditemukan atau Anda tidak memiliki akses!";
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

// Proses update data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_figure = isset($_POST['nama_figure']) ? trim($_POST['nama_figure']) : '';
    $karakter = isset($_POST['karakter']) ? trim($_POST['karakter']) : '';
    $seri_anime = isset($_POST['seri_anime']) ? trim($_POST['seri_anime']) : '';
    $produsen = isset($_POST['produsen']) ? trim($_POST['produsen']) : '';
    $skala_ukuran = isset($_POST['skala_ukuran']) ? trim($_POST['skala_ukuran']) : '';
    $harga = isset($_POST['harga']) ? (int) $_POST['harga'] : 0;
    
    // Validasi input wajib
    if (empty($nama_figure)) {
        $errors['nama_figure'] = 'Nama figure wajib diisi.';
    }
    if (empty($karakter)) {
        $errors['karakter'] = 'Nama karakter wajib diisi.';
    }
    if (empty($seri_anime)) {
        $errors['seri_anime'] = 'Seri/Anime wajib diisi.';
    }
    if (empty($produsen)) {
        $errors['produsen'] = 'Produsen/Brand wajib diisi.';
    }
    if ($harga <= 0) {
        $errors['harga'] = 'Harga harus berupa angka lebih besar dari 0.';
    }

    // Default gunakan foto lama
    $foto_name = $figure['foto_figure'];
    
    // Cek jika ada upload foto baru
    if (isset($_FILES['foto_figure']) && $_FILES['foto_figure']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto_figure']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_figure']['tmp_name'];
            $file_name = $_FILES['foto_figure']['name'];
            $file_size = $_FILES['foto_figure']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (!in_array($file_ext, $allowed_extensions)) {
                $errors['foto_figure'] = 'Format foto tidak valid. Hanya diperbolehkan: JPG, JPEG, PNG, WEBP, GIF.';
            } elseif ($file_size > 2 * 1024 * 1024) { // Limit 2MB
                $errors['foto_figure'] = 'Ukuran foto terlalu besar. Maksimal adalah 2MB.';
            } else {
                // Berikan nama file unik
                $foto_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                $upload_dir = 'assets/uploads/';
                
                if (move_uploaded_file($file_tmp, $upload_dir . $foto_name)) {
                    // Hapus foto lama dari server jika ada dan file-nya memang ada
                    if (!empty($figure['foto_figure']) && file_exists($upload_dir . $figure['foto_figure'])) {
                        unlink($upload_dir . $figure['foto_figure']);
                    }
                } else {
                    $errors['foto_figure'] = 'Gagal mengunggah foto baru ke server.';
                }
            }
        } else {
            // Tangani error upload dari PHP
            switch ($_FILES['foto_figure']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors['foto_figure'] = 'Ukuran file foto melebihi batas maksimal server (2MB).';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors['foto_figure'] = 'File foto hanya terunggah sebagian.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errors['foto_figure'] = 'Folder penyimpanan sementara (tmp) tidak ditemukan di server.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errors['foto_figure'] = 'Gagal menulis file foto ke disk server.';
                    break;
                default:
                    $errors['foto_figure'] = 'Terjadi kesalahan sistem saat mengunggah foto (Error Code: ' . $_FILES['foto_figure']['error'] . ').';
                    break;
            }
        }
    }

    // Jika tidak ada error, perbarui database
    if (empty($errors)) {
        try {
            if ($_SESSION['role'] === 'admin') {
                $stmt = $pdo->prepare("UPDATE tb_figures 
                                       SET nama_figure = :nama_figure, karakter = :karakter, seri_anime = :seri_anime, 
                                           produsen = :produsen, skala_ukuran = :skala_ukuran, harga = :harga, foto_figure = :foto_figure 
                                       WHERE id = :id");
                
                $stmt->execute([
                    'nama_figure' => $nama_figure,
                    'karakter' => $karakter,
                    'seri_anime' => $seri_anime,
                    'produsen' => $produsen,
                    'skala_ukuran' => $skala_ukuran,
                    'harga' => $harga,
                    'foto_figure' => $foto_name,
                    'id' => $id
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE tb_figures 
                                       SET nama_figure = :nama_figure, karakter = :karakter, seri_anime = :seri_anime, 
                                           produsen = :produsen, skala_ukuran = :skala_ukuran, harga = :harga, foto_figure = :foto_figure 
                                       WHERE id = :id AND user_id = :user_id");
                
                $stmt->execute([
                    'nama_figure' => $nama_figure,
                    'karakter' => $karakter,
                    'seri_anime' => $seri_anime,
                    'produsen' => $produsen,
                    'skala_ukuran' => $skala_ukuran,
                    'harga' => $harga,
                    'foto_figure' => $foto_name,
                    'id' => $id,
                    'user_id' => $user_id
                ]);
            }
            
            $_SESSION['success'] = "Koleksi figure berhasil diperbarui!";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $errors['db'] = "Gagal memperbarui database: " . $e->getMessage();
        }
    }
}
?>

<main class="flex-grow max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
    <!-- Back button & Page title -->
    <div class="mb-6">
        <a href="index.php" class="inline-flex items-center text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Kembali ke Dashboard
        </a>
        <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-2">Edit Data Figure</h1>
        <p class="text-slate-500 mt-1 text-xs sm:text-base">Perbarui detail data koleksi figure: <strong><?= htmlspecialchars($figure['nama_figure']) ?></strong></p>
    </div>

    <!-- DB error message if any -->
    <?php if (isset($errors['db'])): ?>
        <div class="p-4 mb-6 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm font-medium"><?= htmlspecialchars($errors['db']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
        <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Nama Figure -->
                <div class="md:col-span-2">
                    <label for="nama_figure" class="block text-sm font-semibold text-slate-700 mb-2">Nama Figure <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_figure" id="nama_figure" value="<?= htmlspecialchars($_POST['nama_figure'] ?? $figure['nama_figure']) ?>" placeholder="Contoh: Nendoroid Hatsune Miku 2.0" class="block w-full px-4 py-2.5 bg-slate-50 border <?= isset($errors['nama_figure']) ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500' : 'border-slate-200 focus:ring-brand-500/20 focus:border-brand-500' ?> rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all duration-200 text-sm">
                    <?php if (isset($errors['nama_figure'])): ?>
                        <p class="text-rose-500 text-xs mt-1.5 font-medium"><?= $errors['nama_figure'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Karakter -->
                <div>
                    <label for="karakter" class="block text-sm font-semibold text-slate-700 mb-2">Karakter <span class="text-rose-500">*</span></label>
                    <input type="text" name="karakter" id="karakter" value="<?= htmlspecialchars($_POST['karakter'] ?? $figure['karakter']) ?>" placeholder="Contoh: Hatsune Miku" class="block w-full px-4 py-2.5 bg-slate-50 border <?= isset($errors['karakter']) ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500' : 'border-slate-200 focus:ring-brand-500/20 focus:border-brand-500' ?> rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all duration-200 text-sm">
                    <?php if (isset($errors['karakter'])): ?>
                        <p class="text-rose-500 text-xs mt-1.5 font-medium"><?= $errors['karakter'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Seri Anime -->
                <div>
                    <label for="seri_anime" class="block text-sm font-semibold text-slate-700 mb-2">Seri / Anime <span class="text-rose-500">*</span></label>
                    <input type="text" name="seri_anime" id="seri_anime" value="<?= htmlspecialchars($_POST['seri_anime'] ?? $figure['seri_anime']) ?>" placeholder="Contoh: Vocaloid" class="block w-full px-4 py-2.5 bg-slate-50 border <?= isset($errors['seri_anime']) ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500' : 'border-slate-200 focus:ring-brand-500/20 focus:border-brand-500' ?> rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all duration-200 text-sm">
                    <?php if (isset($errors['seri_anime'])): ?>
                        <p class="text-rose-500 text-xs mt-1.5 font-medium"><?= $errors['seri_anime'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Produsen / Brand -->
                <div>
                    <label for="produsen" class="block text-sm font-semibold text-slate-700 mb-2">Produsen / Brand <span class="text-rose-500">*</span></label>
                    <input type="text" name="produsen" id="produsen" value="<?= htmlspecialchars($_POST['produsen'] ?? $figure['produsen']) ?>" placeholder="Contoh: Good Smile Company" class="block w-full px-4 py-2.5 bg-slate-50 border <?= isset($errors['produsen']) ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500' : 'border-slate-200 focus:ring-brand-500/20 focus:border-brand-500' ?> rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all duration-200 text-sm">
                    <?php if (isset($errors['produsen'])): ?>
                        <p class="text-rose-500 text-xs mt-1.5 font-medium"><?= $errors['produsen'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Skala / Ukuran -->
                <div>
                    <label for="skala_ukuran" class="block text-sm font-semibold text-slate-700 mb-2">Skala / Ukuran</label>
                    <input type="text" name="skala_ukuran" id="skala_ukuran" value="<?= htmlspecialchars($_POST['skala_ukuran'] ?? $figure['skala_ukuran']) ?>" placeholder="Contoh: 1/7 Scale, Nendoroid, Non-scale" class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:ring-brand-500/20 focus:border-brand-500 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all duration-200 text-sm">
                </div>

                <!-- Harga -->
                <div>
                    <label for="harga" class="block text-sm font-semibold text-slate-700 mb-2">Harga Figure (Rupiah) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 text-sm font-bold">
                            Rp
                        </div>
                        <input type="number" name="harga" id="harga" min="0" value="<?= htmlspecialchars($_POST['harga'] ?? $figure['harga']) ?>" placeholder="Contoh: 750000" class="block w-full pl-11 pr-4 py-2.5 bg-slate-50 border <?= isset($errors['harga']) ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500' : 'border-slate-200 focus:ring-brand-500/20 focus:border-brand-500' ?> rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all duration-200 text-sm">
                    </div>
                    <?php if (isset($errors['harga'])): ?>
                        <p class="text-rose-500 text-xs mt-1.5 font-medium"><?= $errors['harga'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Upload/Replace Image -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Figure</label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <!-- Preview Box (Jika ada foto lama, tampilkan) -->
                        <div id="preview-container" class="aspect-square border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 relative group">
                            <?php if (!empty($figure['foto_figure']) && file_exists('assets/uploads/' . $figure['foto_figure'])): ?>
                                <img id="image-preview" src="assets/uploads/<?= htmlspecialchars($figure['foto_figure']) ?>" alt="Preview" class="w-full h-full object-cover">
                            <?php else: ?>
                                <!-- Fallback dummy image if DB has no record -->
                                <img id="image-preview" src="" alt="No Image" class="w-full h-full object-cover hidden">
                                <div id="no-image-placeholder" class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] font-semibold">TIDAK ADA FOTO</span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                <span class="text-xs text-white font-semibold">Ubah Foto</span>
                            </div>
                        </div>

                        <!-- Dropzone Box -->
                        <div class="md:col-span-2">
                            <label for="foto_figure" class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 hover:border-brand-500 bg-slate-50 hover:bg-brand-50/20 rounded-2xl p-6 cursor-pointer transition-all duration-200 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs font-semibold text-slate-600 block">Pilih Foto Baru</span>
                                <span class="text-[10px] text-slate-400 mt-1 block">Biarkan kosong jika tidak ingin mengubah foto.</span>
                                <input type="file" name="foto_figure" id="foto_figure" class="hidden" accept="image/*">
                            </label>
                        </div>
                    </div>
                    <?php if (isset($errors['foto_figure'])): ?>
                        <p class="text-rose-500 text-xs mt-1.5 font-medium"><?= $errors['foto_figure'] ?></p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Submit buttons -->
            <div class="border-t border-slate-100 pt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 sm:space-x-3">
                <a href="index.php" class="px-5 py-3 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm rounded-2xl transition-colors duration-200 text-center">
                    Batal
                </a>
                <button type="submit" class="px-5 py-3 bg-gradient-to-r from-brand-600 to-indigo-600 text-white font-semibold text-sm rounded-2xl hover:from-brand-700 hover:to-indigo-700 shadow-md shadow-brand-500/10 hover:shadow-lg transition-all duration-200 text-center">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</main>

<!-- Extra JS specific to edit page to handle fallback image toggling when new image is uploaded -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('foto_figure');
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('no-image-placeholder');
    
    if (input && preview && placeholder) {
        input.addEventListener('change', function() {
            if (this.files[0]) {
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
