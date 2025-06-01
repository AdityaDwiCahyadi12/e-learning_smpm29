<?php
// Mulai session
session_start();

// Konfigurasi database
$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';

// Membuat koneksi PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}


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

// Check if user photo exists
$photoPath = file_exists("uploads/user_$userId.jpg") ? "uploads/user_$userId.jpg" : "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User') . "&background=3b82f6&color=fff";

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .logout-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .logout-card {
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 h-screen bg-white shadow-lg fixed left-0">
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
                <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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
                    <a href="keluar.php" class="sidebar-item active flex items-center px-3 py-2 text-blue-600 rounded-lg">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                        <span>Keluar</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="ml-64 w-full p-6 flex items-center justify-center">
            <div class="logout-container">
                <div class="logout-card bg-white rounded-lg shadow-sm p-8 text-center">
                    <div class="mb-6">
                        <i class="fas fa-sign-out-alt text-5xl text-blue-500 mb-4"></i>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Konfirmasi Keluar</h2>
                        <p class="text-gray-600">Apakah Anda yakin ingin keluar dari sistem E-Learning Class?</p>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <a href="dashboard.php" class="bg-gray-200 text-gray-700 py-2 px-6 rounded-lg hover:bg-gray-300 transition duration-200">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                        <a href="../../index.php" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript functionality if needed
            console.log('Logout page loaded');
        });
    </script>
</body>
</html>