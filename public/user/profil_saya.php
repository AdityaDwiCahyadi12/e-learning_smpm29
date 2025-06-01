<?php
session_start();

// Simulate user session if not set (for testing)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'username' => 'testuser',
        'role' => 'student'
    ];
}

$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_profil'])) {
    $userId = $_POST['user_id'] ?? null;
    if ($userId) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $file = $_FILES['foto_profil'];
        $fileName = 'user_' . $userId . '.jpg';
        $targetPath = $uploadDir . $fileName;
        
        // Check file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowedTypes) && $file['size'] < 2000000) {
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $_SESSION['success'] = "Foto profil berhasil diunggah!";
                header("Location: profil_saya.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal mengunggah foto profil.";
            }
        } else {
            $_SESSION['error'] = "File harus berupa gambar (JPEG, PNG, GIF) dan ukuran maksimal 2MB.";
        }
    }
}

// Handle profile photo deletion
if (isset($_GET['delete_photo'])) {
    $userId = $_SESSION['user']['id'] ?? null;
    if ($userId) {
        $photoPath = 'uploads/user_' . $userId . '.jpg';
        if (file_exists($photoPath)) {
            if (unlink($photoPath)) {
                $_SESSION['success'] = "Foto profil berhasil dihapus!";
            } else {
                $_SESSION['error'] = "Gagal menghapus foto profil.";
            }
        }
        header("Location: profil_saya.php");
        exit();
    }
}

$userId = $_SESSION['user']['id'] ?? null;
$userData = [];

if ($userId) {
    try {
        // Create users table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert sample data if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO users (username, full_name, email, role) VALUES 
                ('admin', 'Administrator', 'admin@example.com', 'admin'),
                ('teacher1', 'Guru Matematika', 'guru@example.com', 'teacher'),
                ('student1', 'Siswa Contoh', 'siswa@example.com', 'student')");
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            // Default data if user not found
            $userData = [
                'id' => $userId,
                'username' => 'user' . $userId,
                'full_name' => 'User ' . $userId,
                'email' => 'user' . $userId . '@example.com',
                'role' => 'student',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
    } catch (PDOException $e) {
        die("Error mengambil data user: " . $e->getMessage());
    }
}

$joinDate = $userData['created_at'] ? date('d F Y', strtotime($userData['created_at'])) : 'Tidak diketahui';

// Create uploads directory if not exists
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

$photoPath = "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User') . "&background=3b82f6&color=fff&size=128";
$hasCustomPhoto = false;
if (file_exists("uploads/user_$userId.jpg")) {
    $photoPath = "uploads/user_$userId.jpg";
    $hasCustomPhoto = true;
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
        .photo-actions { display: none; }
        .photo-container:hover .photo-actions { display: flex; }
    </style>
</head>
<body>
<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 h-screen bg-white shadow-lg fixed left-0 sidebar">
        <div class="flex items-center justify-center p-4 border-b">
            <span class="text-xl font-bold text-gray-800">E-Learning Class</span>
        </div>
        <div class="p-4">
            <div class="flex items-center mb-6 p-3 bg-gray-100 rounded-lg">
                <img src="<?= $photoPath ?>" alt="Profil" class="w-10 h-10 rounded-full mr-3">
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User') ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($userData['role'] ?? 'Role') ?></p>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
            <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-home w-5 mr-3"></i><span>Dashboard</span>
            </a>
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PROFIL</p>
            <a href="profil_saya.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-user w-5 mr-3"></i><span>Profil Saya</span>
            </a>
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
            <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
            </a>
            <a href="materi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-book-open w-5 mr-3"></i><span>Materi</span>
            </a>
            <a href="kuis.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-question-circle w-5 mr-3"></i><span>Kuis</span>
            </a>
            <a href="tugas.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-tasks w-5 mr-3"></i><span>Tugas</span>
            </a>
            <div class="border-t mt-4 pt-2">
                <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i><span>Keluar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 w-full p-6">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="profile-card bg-white rounded-lg shadow-sm p-6">
                <div class="flex flex-col items-center">
                    <form id="photoForm" action="profil_saya.php" method="POST" enctype="multipart/form-data">
                        <div class="relative mb-4 photo-container">
                            <img src="<?= $photoPath ?>" alt="Profil" class="w-32 h-32 rounded-full border-4 border-white shadow-md object-cover">
                            <div class="photo-actions absolute bottom-0 right-0 flex space-x-2">
                                <label for="fileInput" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 cursor-pointer">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <?php if ($hasCustomPhoto): ?>
                                <a href="?delete_photo=true" class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 cursor-pointer" onclick="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <input id="fileInput" class="file-input" type="file" name="foto_profil" accept="image/*" onchange="document.getElementById('photoForm').submit();">
                            <input type="hidden" name="user_id" value="<?= $userId ?>">
                        </div>
                    </form>
                    <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User') ?></h2>
                    <p class="text-gray-500 mb-2"><?= htmlspecialchars($userData['role'] ?? 'Role') ?></p>
                </div>
                <div class="mt-6 pt-4 border-t">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-medium text-gray-500">Bergabung sejak</span>
                        <span class="text-sm text-gray-800"><?= $joinDate ?></span>
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

            <!-- Edit Profile -->
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