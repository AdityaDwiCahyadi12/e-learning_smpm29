<?php
session_start();

// Koneksi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Ambil data user
$userId = $_SESSION['user']['id'] ?? null;
$userData = [];

if ($userId) {
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
}

// Cek apakah foto user ada
$photoPath = file_exists("uploads/user_$userId.jpg") ? "uploads/user_$userId.jpg" : "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User ') . "&background=3b82f6&color=fff";

// Query untuk mengambil materi terbaru 
$sql = "SELECT * FROM materials ORDER BY created_at DESC"; 
$result = $mysqli->query($sql); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Pembelajaran - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .materi-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .materi-card:hover {
            transform: translateY(-2px);
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
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User ') ?></p>
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
                <a href="materi.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">Materi Pembelajaran</h1>
                        <p class="text-sm text-gray-600">Pelajari materi pembelajaran yang telah disediakan oleh guru</p>
                    </div>
                </div>
            </div>

            <!-- Materi List -->
            <div class="space-y-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="bg-white border border-gray-200 rounded-lg p-6 materi-card">
                            <div class="flex">
                                <div class="bg-blue-100 p-3 rounded-full h-12 w-12 flex items-center justify-center mr-4">
                                    <?php
                                    $filePath = $row['file_path'];
                                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                    
                                    if (in_array($ext, ['pdf'])) {
                                        echo '<i class="fas fa-file-pdf text-blue-600"></i>';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        echo '<i class="fas fa-file-word text-blue-600"></i>';
                                    } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                        echo '<i class="fas fa-file-powerpoint text-blue-600"></i>';
                                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        echo '<i class="fas fa-file-image text-blue-600"></i>';
                                    } elseif (in_array($ext, ['mp4', 'mov', 'avi'])) {
                                        echo '<i class="fas fa-file-video text-blue-600"></i>';
                                    } else {
                                        echo '<i class="fas fa-book-open text-blue-600"></i>';
                                    }
                                    ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($row['title']) ?></h3>
                                        <span class="text-xs text-gray-500"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Kategori: <?= htmlspecialchars($row['category'] ?? 'Umum') ?></p>
                                    <p class="text-gray-700 mt-2 text-sm"><?= nl2br(htmlspecialchars($row['content'])) ?></p>

                                    <?php if (!empty($row['file_path'])): ?>
                                        <?php
                                        $fileName = basename($row['file_path']);
                                        $absolutePath = __DIR__ . '/../uploads/materials/' . $fileName;

                                        if (file_exists($absolutePath)):
                                            $fileSize = filesize($absolutePath);
                                        ?>
                                            <div class="mt-3 flex items-center text-sm">
                                                <a href="download.php?file=<?= urlencode($fileName) ?>" class="text-blue-600 hover:underline mr-4">
                                                    Download File (<?= round($fileSize / 1024, 1) ?> KB)
                                                </a>
                                                <a href="../uploads/materials/<?= $fileName ?>" target="_blank" class="text-green-600 hover:underline">
                                                    Lihat File
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-3 text-sm text-red-500">
                                                File tidak tersedia
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                        <i class="fas fa-book-open text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">Belum ada materi yang tersedia</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php $mysqli->close(); ?>
