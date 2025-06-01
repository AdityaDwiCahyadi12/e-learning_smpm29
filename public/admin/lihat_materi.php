<?php
// Koneksi database
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'smpm29';

$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk mengambil semua materi
$sql = "SELECT * FROM materials ORDER BY created_at DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Materi - E-Learning Class</title>
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
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
                <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-comments w-5 mr-3"></i>
                    <span>Forum Diskusi</span>
                </a>
                <a href="lihat_materi.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-book-open w-5 mr-3"></i>
                    <span>Materi Pembelajaran</span>
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
                <h1 class="text-xl font-semibold text-gray-800">Materi Pembelajaran</h1>
                <p class="text-sm text-gray-600">Daftar materi pembelajaran yang tersedia</p>
            </div>
            
            <!-- Filter Materi -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" placeholder="Cari materi..." class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="w-48">
                        <select class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="">Semua Kategori</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                            <option value="IPA">IPA</option>
                            <option value="IPS">IPS</option>
                            <option value="Pemrograman">Pemrograman</option>
                        </select>
                    </div>
                    <button class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </div>
            
            <!-- Daftar Materi -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Semua Materi</h2>

                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded-lg p-4 mb-4 hover:shadow-md transition-shadow">
                            <div class="flex">
                                <div class="bg-blue-100 p-3 rounded-full h-12 w-12 flex items-center justify-center mr-4">
                                    <?php
                                    // Tampilkan icon berdasarkan ekstensi file
                                    $filePath = $row['file_path'];
                                    $ext = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : '';
                                    
                                    if (in_array($ext, ['pdf'])) {
                                        echo '<i class="fas fa-file-pdf text-blue-600"></i>';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        echo '<i class="fas fa-file-word text-blue-600"></i>';
                                    } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                        echo '<i class="fas fa-file-powerpoint text-blue-600"></i>';
                                    } else {
                                        echo '<i class="fas fa-book-open text-blue-600"></i>';
                                    }
                                    ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['title']); ?></h3>
                                        <span class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Kategori: <?php echo htmlspecialchars($row['category'] ?? 'Umum'); ?></p>
                                    <p class="text-gray-700 mt-2 text-sm"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                                    <?php if (!empty($row['file_path'])): ?>
                                        <div class="mt-3 flex items-center text-sm text-blue-600">
                                            <i class="fas fa-download mr-2"></i>
                                            <a href="<?php echo htmlspecialchars($row['file_path']); ?>" class="hover:underline" target="_blank">Download File</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">Belum ada materi yang tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>