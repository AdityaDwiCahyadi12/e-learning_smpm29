<?php
session_start();

$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil user ID dari session
    $userId = $_SESSION['user']['id'] ?? null;

    if (!$userId) {
        // Jika belum login, redirect ke halaman login
        header("Location: login.php");
        exit;
    }

    // Ambil data user dari database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        // Jika user tidak ditemukan, redirect ke login
        header("Location: login.php");
        exit;
    }

    // Buat direktori uploads jika belum ada
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $photoPath = "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User ') . "&background=3b82f6&color=fff&size=128";
    if (file_exists("uploads/user_$userId.jpg")) {
        $photoPath = "uploads/user_$userId.jpg";
    }

    // Tangani penghapusan foto
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
        if (file_exists("uploads/user_$userId.jpg")) {
            unlink("uploads/user_$userId.jpg");
            header("Location: profil.php"); // Redirect ke halaman yang sama
            exit;
        }
    }
} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Class - Profil Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .sidebar { transition: all 0.3s ease; }
        .sidebar-item:hover { background-color: #f1f5f9; border-left: 3px solid #3b82f6; }
        .sidebar-item.active { background-color: #e0e7ff; color: #3b82f6; font-weight: 500; border-left: 3px solid #3b82f6; }
        .profile-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .file-input { display: none; }
    </style>
</head>
<body>
<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 h-screen bg-white shadow-lg fixed left-0 sidebar">
        <div class="flex items-center justify-center p-4 border-b">
            <span class="text-xl font-bold text-gray-800">E-Learning Class</span>
        </div>
        <div class="p-4 overflow-y-auto" style="height: calc(100vh - 64px);">
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
            <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'dashboard.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                <span>Dashboard</span>
            </a>

            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">MANAJEMEN</p>
            <a href="profil.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-user w-5 mr-3"></i>
                <span>Profil</span>
            </a>
            <a href="tambah_anggota.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_anggota.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-user-plus w-5 mr-3"></i>
                <span>Tambah Anggota</span>
            </a>

            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
            <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'forum_diskusi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-comments w-5 mr-3"></i>
                <span>Forum Diskusi</span>
            </a>
            <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_materi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-book-open w-5 mr-3"></i>
                <span>Tambah Materi</span>
            </a>
            <a href="buat_kuis.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-question-circle w-5 mr-3"></i>
                <span>Buat Kuis</span>
            </a>
            <a href="buat_tugas.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_tugas.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-tasks w-5 mr-3"></i>
                <span>Buat Tugas</span>
            </a>

            <div class="border-t mt-4 pt-2">
                <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </div>


    <!-- Konten Utama -->
    <div class="ml-64 w-full p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kartu Profil -->
            <div class="profile-card bg-white rounded-lg shadow-sm p-6">
                <div class="flex flex-col items-center">
                    <form id="photoForm" action="upload_foto.php" method="POST" enctype="multipart/form-data">
                        <div class="relative mb-4">
                            <img src="<?= $photoPath ?>" alt="Profil" class="w-32 h-32 rounded-full border-4 border-white shadow-md object-cover">
                            <label for="fileInput" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 cursor-pointer">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input id="fileInput" class="file-input" type="file" name="foto_profil" accept="image/*" onchange="document.getElementById('photoForm').submit();">
                            <input type="hidden" name="user_id" value="<?= $userId ?>">
                        </div>
                    </form>
                    <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User ') ?></h2>
                    <p class="text-gray-500 mb-2"><?= htmlspecialchars($userData['role'] ?? 'Role') ?></p>
                    <form method="POST" action="">
                        <button type="submit" name="delete_photo" class="text-red-600 hover:underline">Hapus Foto Profil</button>
                    </form>
                </div>
                <div class="mt-6 pt-4 border-t">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-medium text-gray-500">Bergabung sejak</span>
                        <span class="text-sm text-gray-800"><?= date('d F Y', strtotime($userData['created_at'])) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-medium text-gray-500">Terakhir login</span>
                        <span class="text-sm text-gray-800">Hari ini, <?= date('H:i') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Status</span>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Edit Profil -->
            <div class="lg:col-span-2">
                <form id="profileForm" method="POST" action="update_profile.php">
                    <input type="hidden" name="user_id" value="<?= $userId ?>">
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($userData['full_name'] ?? '') ?>" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($userData['username'] ?? '') ?>" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-save mr-2"></i>Simpan Profil
                        </button>
                    </div>
                </form>

                <!-- Ubah Password Form -->
                <form id="passwordForm" method="POST" action="ubah_password.php">
                    <input type="hidden" name="user_id" value="<?= $userId ?>">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ubah Password</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                            <input type="password" name="old_password" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                            <input type="password" name="new_password" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-key mr-2"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
