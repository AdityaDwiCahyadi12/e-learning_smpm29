<?php
session_start();

$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';

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

    // Cek role user secara case-insensitive
    $userRole = strtolower($userData['role']);

    if (!in_array($userRole, ['admin', 'guru'])) {
        // Jika role bukan admin atau guru, akses ditolak
        header("Location: akses_ditolak.php");
        exit;
    }

    // Ambil data statistik (contoh)
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $kuis_aktif = $pdo->query("SELECT COUNT(*) FROM quizzes WHERE NOW() BETWEEN start_time AND end_time")->fetchColumn();

    // Ambil aktivitas terkini
    $aktivitas_terkini = $pdo->query("SELECT type, title, description, created_at FROM activities ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // Hitung total tugas yang sudah dikumpulkan (dari tabel task_submissions)
    $stmt = $pdo->query("SELECT COUNT(*) AS total_submissions FROM task_submissions");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_submissions = $row ? (int)$row['total_submissions'] : 0;

} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E-Learning Class - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item {
            transition: all 0.2s ease;
        }
        .sidebar-item:hover {
            background-color: #f1f5f9;
            border-left: 3px solid #3b82f6;
        }
        .sidebar-item.active {
            background-color: #e0e7ff;
            color: #3b82f6;
            font-weight: 500;
            border-left: 3px solid #3b82f6;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .quick-action-card {
            transition: all 0.3s ease;
        }
        .quick-action-card:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
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
                <div class="flex items-center mb-6 p-3 bg-gray-100 rounded-lg">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($userData['full_name'] ?? 'User  ') ?>&background=3b82f6&color=fff" alt="Profil" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User  ') ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($userData['role'] ?? 'Role') ?></p>
                    </div>
                </div>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
                <a href="dashboard.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">MANAJEMEN</p>
                <a href="profil.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-user w-5 mr-3"></i>
                    <span>Profil</span>
                </a>
                <a href="tambah_anggota.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-user-plus w-5 mr-3"></i>
                    <span>Tambah Anggota</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-comments w-5 mr-3"></i>
                    <span>Forum Diskusi</span>
                </a>
                <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-book-open w-5 mr-3"></i>
                    <span>Tambah Materi</span>
                </a>
                <a href="buat_kuis.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-question-circle w-5 mr-3"></i>
                    <span>Buat Kuis</span>
                </a>
                <a href="buat_tugas.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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

        <!-- Main Content -->
        <div class="ml-64 w-full p-6 min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <p class="text-sm text-gray-600">Selamat datang, <?= htmlspecialchars($userData['full_name']) ?></p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"> 
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Anggota</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $total_users ?></h3>
                        </div>
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-xs mt-3">
                        <i class="fas fa-arrow-up"></i> 20% dari bulan lalu
                    </p>
                </div>
                
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-amber-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Kuis Aktif</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $kuis_aktif ?></h3>
                        </div>
                        <div class="bg-amber-100 p-2 rounded-lg">
                            <i class="fas fa-question-circle text-amber-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tugas Yang Dikumpulkan</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $total_submissions ?></h3>
                        </div>
                        <div class="bg-red-100 p-2 rounded-lg">
                            <i class="fas fa-tasks text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="tambah_materi.php" class="quick-action-card bg-white rounded-lg shadow-sm p-6 hover:bg-blue-50 cursor-pointer border border-transparent hover:border-blue-200 block">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-book-open text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Tambah Materi</h3>
                            <p class="text-sm text-gray-500">Upload materi pembelajaran baru</p>
                        </div>
                    </div>
                </a>
                
                <a href="buat_kuis.php" class="quick-action-card bg-white rounded-lg shadow-sm p-6 hover:bg-green-50 cursor-pointer border border-transparent hover:border-green-200 block">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-question-circle text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Buat Kuis</h3>
                            <p class="text-sm text-gray-500">Buat evaluasi pembelajaran</p>
                        </div>
                    </div>
                </a>
                
                <a href="buat_tugas.php" class="quick-action-card bg-white rounded-lg shadow-sm p-6 hover:bg-purple-50 cursor-pointer border border-transparent hover:border-purple-200 block">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-tasks text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Buat Tugas</h3>
                            <p class="text-sm text-gray-500">Buat tugas baru untuk siswa</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Aktivitas Terkini</h2>
                    <a href="#" class="text-blue-600 text-sm hover:underline">Lihat Semua</a>
                </div>
                
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php if (!empty($aktivitas_terkini)): ?>
                        <?php foreach ($aktivitas_terkini as $act): ?>
                            <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3 flex-shrink-0">
                                    <?php
                                    switch ($act['type']) {
                                        case 'materi': echo '<i class="fas fa-book text-blue-600"></i>'; break;
                                        case 'kuis': echo '<i class="fas fa-question-circle text-green-600"></i>'; break;
                                        case 'tugas': echo '<i class="fas fa-tasks text-amber-600"></i>'; break;
                                        default: echo '<i class="fas fa-info-circle text-gray-600"></i>';
                                    }
                                    ?>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($act['title']) ?></h3>
                                    <p class="text-sm text-gray-500"><?= nl2br(htmlspecialchars($act['description'])) ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?= date('d M Y, H:i', strtotime($act['created_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">Belum ada aktivitas terbaru.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
