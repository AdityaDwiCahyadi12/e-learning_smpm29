<?php
// Mulai session
session_start();

// Konfigurasi database
$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil data user
    $userId = $_SESSION['user']['id'] ?? null;
    $userData = [];

    if ($userId) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error mengambil data user: " . $e->getMessage());
        }
    }

    // Query untuk materials baru (7 hari terakhir) - menggunakan created_at
    $recentDate = date('Y-m-d H:i:s', strtotime('-7 days'));
    $stmtMaterials = $pdo->query("SELECT COUNT(*) AS total FROM materials WHERE created_at >= '$recentDate'");
    $totalMateri = $stmtMaterials->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Query jumlah quizzes aktif (yang sedang berlangsung)
    $currentDateTime = date('Y-m-d H:i:s');
    $stmtQuizzes = $pdo->query("SELECT COUNT(*) AS total FROM quizzes WHERE start_time <= '$currentDateTime' AND end_time >= '$currentDateTime'");
    $totalKuis = $stmtQuizzes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Query jumlah tasks aktif (belum melewati deadline)
    $currentDate = date('Y-m-d H:i:s');
    $stmtTasks = $pdo->query("SELECT COUNT(*) AS total FROM tasks WHERE deadline >= '$currentDate'");
    $totalTugas = $stmtTasks->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Query jumlah discussions baru (7 hari terakhir) - menggunakan created_at
    $stmtDiscussions = $pdo->query("SELECT COUNT(*) AS total FROM discussions WHERE created_at >= '$recentDate'");
    $totalDiskusi = $stmtDiscussions->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Query untuk Aktivitas Terkini - menggunakan created_at
    $recentActivities = [];
    
    try {
        $stmtActivities = $pdo->prepare("
            (SELECT 'submission' AS type, created_at AS activity_date, 'Anda mengumpulkan tugas' AS title, task_id AS related_id, 'Pengumpulan tugas' AS description 
             FROM submissions 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT 'material' AS type, created_at AS activity_date, 'Materi baru tersedia' AS title, id AS related_id, title AS description 
             FROM materials 
             ORDER BY created_at DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT 'quiz' AS type, created_at AS activity_date, 'Kuis baru tersedia' AS title, id AS related_id, title AS description 
             FROM quizzes 
             WHERE start_time <= NOW() AND end_time >= NOW()
             ORDER BY created_at DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT 'discussion' AS type, created_at AS activity_date, 'Diskusi baru di forum' AS title, id AS related_id, title AS description 
             FROM discussions 
             ORDER BY created_at DESC 
             LIMIT 2)
            
            ORDER BY activity_date DESC 
            LIMIT 4
        ");
        $stmtActivities->execute([$userId]);
        $recentActivities = $stmtActivities->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $recentActivities = [];
    }

    // Query untuk Deadline Mendatang
    $upcomingDeadlines = [];
    try {
        $stmtDeadlines = $pdo->query("
            (SELECT 'task' AS type, title, deadline AS due_date, id 
             FROM tasks 
             WHERE deadline >= NOW() 
             ORDER BY deadline ASC 
             LIMIT 3)
            
            UNION ALL
            
            (SELECT 'quiz' AS type, title, end_time AS due_date, id 
             FROM quizzes 
             WHERE end_time >= NOW() 
             ORDER BY end_time ASC 
             LIMIT 3)
            
            ORDER BY due_date ASC 
            LIMIT 3
        ");
        $upcomingDeadlines = $stmtDeadlines->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $upcomingDeadlines = [];
    }

    // Query untuk statistik tambahan
    $stats = [
        'materi_belum_dibaca' => 0,
        'kuis_berakhir_soon' => 0,
        'deadline_besok' => 0,
        'diskusi_menunggu' => 0
    ];

    // Hitung materi belum dibaca (asumsi ada tabel user_material dengan kolom is_read)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM materials m 
                              LEFT JOIN user_material um ON m.id = um.material_id AND um.user_id = ?
                              WHERE um.is_read = 0 OR um.id IS NULL");
        $stmt->execute([$userId]);
        $stats['materi_belum_dibaca'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (PDOException $e) {
        // Jika error, gunakan default
        $stats['materi_belum_dibaca'] = 0;
    }

    // Hitung kuis yang berakhir dalam 24 jam
    try {
        $soonTime = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM quizzes 
                            WHERE end_time BETWEEN NOW() AND '$soonTime'");
        $stats['kuis_berakhir_soon'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (PDOException $e) {
        $stats['kuis_berakhir_soon'] = 0;
    }

    // Hitung deadline besok
    try {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tasks 
                            WHERE DATE(deadline) = '$tomorrow'");
        $stats['deadline_besok'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (PDOException $e) {
        $stats['deadline_besok'] = 0;
    }

    // Hitung diskusi menunggu respons (asumsi ada kolom is_answered)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM discussions 
                              WHERE user_id = ? AND is_answered = 0");
        $stmt->execute([$userId]);
        $stats['diskusi_menunggu'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (PDOException $e) {
        $stats['diskusi_menunggu'] = 0;
    }

} catch (PDOException $e) {
    die("Terjadi kesalahan pada database: " . $e->getMessage());
}

// Check if user photo exists
$photoPath = file_exists("uploads/user_$userId.jpg") ? "uploads/user_$userId.jpg" : "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User') . "&background=3b82f6&color=fff";

// Fungsi untuk menentukan ikon dan warna berdasarkan jenis aktivitas
function getActivityIcon($type) {
    switch ($type) {
        case 'submission':
            return ['fas fa-check-circle', 'bg-green-100', 'text-green-600'];
        case 'material':
            return ['fas fa-book', 'bg-blue-100', 'text-blue-600'];
        case 'quiz':
            return ['fas fa-question-circle', 'bg-amber-100', 'text-amber-600'];
        case 'discussion':
            return ['fas fa-comment-dots', 'bg-purple-100', 'text-purple-600'];
        default:
            return ['fas fa-bell', 'bg-gray-100', 'text-gray-600'];
    }
}

// Fungsi untuk menentukan warna latar berdasarkan jenis deadline
function getDeadlineColor($type) {
    switch ($type) {
        case 'task':
            return 'bg-red-50 border-red-500';
        case 'quiz':
            return 'bg-amber-50 border-amber-500';
        default:
            return 'bg-blue-50 border-blue-500';
    }
}

// Fungsi untuk menentukan teks tombol berdasarkan jenis deadline
function getDeadlineButton($type) {
    switch ($type) {
        case 'task':
            return ['Kerjakan', 'text-red-600 hover:text-red-800'];
        case 'quiz':
            return ['Mulai', 'text-amber-600 hover:text-amber-800'];
        default:
            return ['Detail', 'text-blue-600 hover:text-blue-800'];
    }
}

// Fungsi untuk format waktu relatif
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $timeDiff = time() - $time;
    
    if ($timeDiff < 60) {
        return 'Baru saja';
    } elseif ($timeDiff < 3600) {
        return floor($timeDiff / 60) . ' menit lalu';
    } elseif ($timeDiff < 86400) {
        return floor($timeDiff / 3600) . ' jam lalu';
    } elseif ($timeDiff < 2592000) {
        return floor($timeDiff / 86400) . ' hari lalu';
    } else {
        return date('j M Y', $time);
    }
}

// Fungsi untuk format waktu deadline
function formatDeadline($datetime) {
    $now = new DateTime();
    $deadline = new DateTime($datetime);
    $interval = $now->diff($deadline);
    
    if ($interval->days == 0) {
        return 'Hari ini, ' . $deadline->format('H:i');
    } elseif ($interval->days == 1) {
        return 'Besok, ' . $deadline->format('H:i');
    } elseif ($interval->days < 7) {
        return $interval->days . ' hari lagi';
    } else {
        return $deadline->format('d M Y, H:i');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E-Learning Class - Dashboard User</title>
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
        .quick-access-card {
            transition: all 0.3s ease;
        }
        .quick-access-card:hover {
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
            <div class="p-4">
                <div class="flex items-center mb-6 p-3 bg-gray-100 rounded-lg">
                    <img src="<?= $photoPath ?>" alt="Profil" class="w-10 h-10 rounded-full mr-3 object-cover">
                    <div>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User') ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($userData['role'] ?? 'Role') ?></p>
                    </div>
                </div>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
                <a href="#" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-home w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PROFIL</p>
                <a href="profil_saya.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-user w-5 mr-3"></i>
                    <span>Profil Saya</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-comments w-5 mr-3"></i>
                    <span>Forum Diskusi</span>
                </a>
                <a href="materi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-book-open w-5 mr-3"></i>
                    <span>Materi</span>
                </a>
                <a href="kuis.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-question-circle w-5 mr-3"></i>
                    <span>Kuis</span>
                </a>
                <a href="tugas.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-tasks w-5 mr-3"></i>
                    <span>Tugas</span>
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
        <div class="ml-64 w-full p-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <p class="text-sm text-gray-600">Selamat datang, <?= htmlspecialchars($userData['full_name'] ?? 'User') ?></p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Materi Baru</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $totalMateri ?></h3>
                        </div>
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <i class="fas fa-book-open text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-xs mt-3">
                        <i class="fas fa-arrow-up"></i> <?= $stats['materi_belum_dibaca'] ?> belum dibaca
                    </p>
                </div>
                
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Kuis Aktif</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $totalKuis ?></h3>
                        </div>
                        <div class="bg-green-100 p-2 rounded-lg">
                            <i class="fas fa-question-circle text-green-600"></i>
                        </div>
                    </div>
                    <p class="text-amber-500 text-xs mt-3">
                        <i class="fas fa-clock"></i> <?= $stats['kuis_berakhir_soon'] ?> kuis berakhir soon
                    </p>
                </div>
                
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-amber-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tugas Aktif</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $totalTugas ?></h3>
                        </div>
                        <div class="bg-amber-100 p-2 rounded-lg">
                            <i class="fas fa-tasks text-amber-600"></i>
                        </div>
                    </div>
                    <p class="text-red-500 text-xs mt-3">
                        <i class="fas fa-exclamation-triangle"></i> <?= $stats['deadline_besok'] ?> deadline besok
                    </p>
                </div>
                
                <div class="stat-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Diskusi Baru</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $totalDiskusi ?></h3>
                        </div>
                        <div class="bg-purple-100 p-2 rounded-lg">
                            <i class="fas fa-comments text-purple-600"></i>
                        </div>
                    </div>
                    <p class="text-blue-500 text-xs mt-3">
                        <i class="fas fa-reply"></i> <?= $stats['diskusi_menunggu'] ?> menunggu respons Anda
                    </p>
                </div>
            </div>
            
            <!-- Quick Access -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="materi.php" class="quick-access-card bg-white rounded-lg shadow-sm p-6 hover:bg-blue-50 cursor-pointer border border-transparent hover:border-blue-200">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-book-open text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Materi</h3>
                            <p class="text-sm text-gray-500">Akses materi pembelajaran</p>
                        </div>
                    </div>
                </a>
                
                <a href="kuis.php" class="quick-access-card bg-white rounded-lg shadow-sm p-6 hover:bg-green-50 cursor-pointer border border-transparent hover:border-green-200">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-question-circle text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Kuis</h3>
                            <p class="text-sm text-gray-500">Kerjakan kuis yang tersedia</p>
                        </div>
                    </div>
                </a>
                
                <a href="tugas.php" class="quick-access-card bg-white rounded-lg shadow-sm p-6 hover:bg-purple-50 cursor-pointer border border-transparent hover:border-purple-200">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-tasks text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Tugas</h3>
                            <p class="text-sm text-gray-500">Lihat dan kumpulkan tugas</p>
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
                
                <div class="space-y-4">
                    <?php if (empty($recentActivities)): ?>
                        <p class="text-gray-500 text-center py-4">Tidak ada aktivitas terkini</p>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): 
                            list($iconClass, $bgClass, $textClass) = getActivityIcon($activity['type']);
                        ?>
                            <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg">
                                <div class="<?= $bgClass ?> p-2 rounded-lg mr-3">
                                    <i class="<?= $iconClass ?> <?= $textClass ?>"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($activity['title']) ?></h3>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($activity['description']) ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?= timeAgo($activity['activity_date']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Deadline Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Deadline Mendatang</h2>
                    <a href="tugas.php" class="text-blue-600 text-sm hover:underline">Lihat Semua</a>
                </div>
                
                <div class="space-y-3">
                    <?php if (empty($upcomingDeadlines)): ?>
                        <p class="text-gray-500 text-center py-4">Tidak ada deadline mendatang</p>
                    <?php else: ?>
                        <?php foreach ($upcomingDeadlines as $deadline): 
                            list($buttonText, $buttonClass) = getDeadlineButton($deadline['type']);
                            $colorClass = getDeadlineColor($deadline['type']);
                        ?>
                            <div class="flex items-center justify-between p-3 <?= $colorClass ?> rounded-lg border-l-4">
                                <div>
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($deadline['title']) ?></h3>
                                    <p class="text-sm text-gray-500"><?= formatDeadline($deadline['due_date']) ?></p>
                                </div>
                                <a href="<?= $deadline['type'] === 'task' ? 'tugas.php' : 'kuis.php' ?>" class="<?= $buttonClass ?> text-sm font-medium">
                                    <?= $buttonText ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>