<?php
session_start();

// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'smpm29';

// Create connection
$mysqli = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'guru') {
    die("Hanya guru yang dapat mengakses halaman ini.");
}

// Get current user ID
$userId = (int)$_SESSION['user']['id'];

// Verify user exists in database
$checkUser = $mysqli->prepare("SELECT id FROM users WHERE id = ?");
$checkUser->bind_param("i", $userId);
$checkUser->execute();
$checkUser->store_result();

if ($checkUser->num_rows === 0) {
    die("User tidak valid. Silakan login kembali.");
}
$checkUser->close();

// Function to handle file uploads
function handleFileUpload($file, $uploadDir = 'uploads/') {
    // New upload directory
    $newUploadDir = 'C:/xampp/htdocs/smpm29/public/user/uploads/';
    
    // Create both directories if they don't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    if (!file_exists($newUploadDir)) {
        mkdir($newUploadDir, 0777, true);
    }
    
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    
    // Check for errors
    if ($fileError !== 0) {
        return ['error' => 'Error uploading file.'];
    }
    
    // Check file size (2MB max)
    if ($fileSize > 2097152) {
        return ['error' => 'File size exceeds 2MB limit.'];
    }
    
    // Generate unique filename
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'ppt', 'pptx'];
    
    if (!in_array($fileExt, $allowedExtensions)) {
        return ['error' => 'File type not allowed.'];
    }
    
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $fileDestination = $uploadDir . $newFileName;
    $newFileDestination = $newUploadDir . $newFileName;
    
    // Move uploaded file to both locations
    if (move_uploaded_file($fileTmp, $fileDestination)) {
        // Copy to new directory
        copy($fileDestination, $newFileDestination);
        
        return [
            'success' => true,
            'file_path' => $fileDestination,
            'file_name' => $fileName,
            'file_type' => $fileType
        ];
    } else {
        return ['error' => 'Failed to move uploaded file.'];
    }
}

// Process new discussion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['judul'])) {
    $judul = $mysqli->real_escape_string($_POST['judul']);
    $konten = $mysqli->real_escape_string($_POST['konten']);
    
    $filePath = null;
    $fileName = null;
    $fileType = null;
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $uploadResult = handleFileUpload($_FILES['attachment']);
        if (isset($uploadResult['error'])) {
            die($uploadResult['error']);
        }
        $filePath = $uploadResult['file_path'];
        $fileName = $uploadResult['file_name'];
        $fileType = $uploadResult['file_type'];
    }
    
    $sql = "INSERT INTO discussions (user_id, title, content, file_path, file_name, file_type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("isssss", $userId, $judul, $konten, $filePath, $fileName, $fileType);
    
    if ($stmt->execute()) {
        header("Location: forum_diskusi.php");
        exit();
    } else {
        die("Error creating discussion: " . $stmt->error);
    }
}

// Process reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discussion_id'])) {
    $discussionId = (int)$_POST['discussion_id'];
    $content = $mysqli->real_escape_string($_POST['content']);
    $userId = (int)$_SESSION['user']['id'];
    
    $filePath = null;
    $fileName = null;
    $fileType = null;
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $uploadResult = handleFileUpload($_FILES['attachment']);
        if (isset($uploadResult['error'])) {
            die($uploadResult['error']);
        }
        $filePath = $uploadResult['file_path'];
        $fileName = $uploadResult['file_name'];
        $fileType = $uploadResult['file_type'];
    }
    
    $sql = "INSERT INTO replies (discussion_id, user_id, content, file_path, file_name, file_type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iissss", $discussionId, $userId, $content, $filePath, $fileName, $fileType);
    
    if ($stmt->execute()) {
        header("Location: forum_diskusi.php");
        exit();
    } else {
        die("Error posting reply: " . $stmt->error);
    }
}

// Process delete discussion
if (isset($_GET['delete_discussion'])) {
    if (!isset($_SESSION['user']['id'])) {
        die("Anda harus login untuk menghapus diskusi");
    }
    
    $discussionId = (int)$_GET['delete_discussion'];
    $userId = (int)$_SESSION['user']['id'];
    
    // Check if discussion belongs to logged in user
    $checkSql = "SELECT user_id FROM discussions WHERE id = ?";
    $checkStmt = $mysqli->prepare($checkSql);
    $checkStmt->bind_param("i", $discussionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        die("Diskusi tidak ditemukan");
    }
    
    $discussion = $checkResult->fetch_assoc();
    if ($discussion['user_id'] != $userId) {
        die("Anda tidak memiliki izin untuk menghapus diskusi ini");
    }
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
        // First delete all replies
        $deleteRepliesSql = "DELETE FROM replies WHERE discussion_id = ?";
        $deleteRepliesStmt = $mysqli->prepare($deleteRepliesSql);
        $deleteRepliesStmt->bind_param("i", $discussionId);
        $deleteRepliesStmt->execute();
        
        // Then delete discussion
        $deleteDiscussionSql = "DELETE FROM discussions WHERE id = ?";
        $deleteDiscussionStmt = $mysqli->prepare($deleteDiscussionSql);
        $deleteDiscussionStmt->bind_param("i", $discussionId);
        $deleteDiscussionStmt->execute();
        
        // Commit transaction
        $mysqli->commit();
        
        header("Location: forum_diskusi.php");
        exit();
    } catch (Exception $e) {
        // Rollback if error
        $mysqli->rollback();
        die("Error menghapus diskusi: " . $e->getMessage());
    }
}

// Process delete reply
if (isset($_GET['delete_reply'])) {
    if (!isset($_SESSION['user']['id'])) {
        die("Anda harus login untuk menghapus balasan");
    }
    
    $replyId = (int)$_GET['delete_reply'];
    $userId = (int)$_SESSION['user']['id'];
    
    // Check if reply belongs to logged in user
    $checkSql = "SELECT user_id FROM replies WHERE id = ?";
    $checkStmt = $mysqli->prepare($checkSql);
    $checkStmt->bind_param("i", $replyId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        die("Balasan tidak ditemukan");
    }
    
    $reply = $checkResult->fetch_assoc();
    if ($reply['user_id'] != $userId) {
        die("Anda tidak memiliki izin untuk menghapus balasan ini");
    }
    
    // Delete reply
    $deleteSql = "DELETE FROM replies WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteSql);
    $deleteStmt->bind_param("i", $replyId);
    
    if ($deleteStmt->execute()) {
        header("Location: forum_diskusi.php");
        exit();
    } else {
        die("Error menghapus balasan: " . $deleteStmt->error);
    }
}

// Get all discussions with user info
$sqlDiskusi = "SELECT d.id, d.user_id, d.title AS judul, d.content AS konten, d.created_at, 
               d.file_path, d.file_name, d.file_type, u.full_name, u.role 
               FROM discussions d 
               JOIN users u ON d.user_id = u.id 
               ORDER BY d.created_at DESC";
$resultDiskusi = $mysqli->query($sqlDiskusi);

if (!$resultDiskusi) {
    die("Query diskusi gagal: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forum Diskusi - E-Learning Class Guru</title>
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
        .discussion-card {
            transition: all 0.3s ease;
        }
        .discussion-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .reply-card {
            border-left: 3px solid #e2e8f0;
        }
        .file-preview {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
        .file-preview.show {
            display: block;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        .image-preview.show {
            display: block;
        }
        .attachment-container {
            margin-top: 15px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
        .attachment-preview {
            max-width: 100%;
            max-height: 300px;
            margin-top: 5px;
            cursor: pointer;
        }
        .delete-btn {
            transition: all 0.2s ease;
        }
        .delete-btn:hover {
            transform: scale(1.05);
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
            <!-- Menu Utama -->
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
            <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'dashboard.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i><span>Dashboard</span>
            </a>

            <!-- Manajemen -->
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">MANAJEMEN</p>
            <a href="profil.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'profil.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-user w-5 mr-3"></i><span>Profil</span>
            </a>
            <a href="tambah_anggota.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_anggota.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-user-plus w-5 mr-3"></i><span>Tambah Anggota</span>
            </a>

            <!-- Pembelajaran -->
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
            <a href="forum_diskusi.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
            </a>
            <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_materi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-book-open w-5 mr-3"></i><span>Tambah Materi</span>
            </a>
            <a href="buat_kuis.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-question-circle w-5 mr-3"></i><span>Buat Kuis</span>
            </a>
            <a href="buat_tugas.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_tugas.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-tasks w-5 mr-3"></i><span>Buat Tugas</span>
            </a>

            <!-- Logout -->
            <div class="border-t mt-4 pt-2">
                <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i><span>Keluar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 w-full p-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Forum Diskusi Guru</h1>
            <p class="text-sm text-gray-600">Diskusikan materi pembelajaran dengan anggota lain</p>
        </div>

        <!-- Create New Discussion -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Buat Diskusi Baru</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="judul" class="block text-sm font-semibold text-gray-600">Judul Diskusi</label>
                    <input type="text" id="judul" name="judul" class="w-full p-3 border border-gray-300 rounded-lg" required />
                </div>
                <div class="mb-4">
                    <label for="konten" class="block text-sm font-semibold text-gray-600">Isi Diskusi</label>
                    <textarea id="konten" name="konten" rows="4" class="w-full p-3 border border-gray-300 rounded-lg" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="attachment" class="block text-sm font-semibold text-gray-600">Lampiran (opsional, maks 2MB)</label>
                    <input type="file" id="attachment" name="attachment" class="text-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx" />
                    <div id="file-preview" class="file-preview">
                        <span id="file-name"></span>
                        <button type="button" onclick="clearFileInput()" class="ml-2 text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <img id="image-preview" class="image-preview" src="#" alt="Preview gambar">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-200">Posting Diskusi</button>
                </div>
            </form>
        </div>

        <!-- Discussion List -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Diskusi Terkini</h2>

            <?php if ($resultDiskusi->num_rows > 0): ?>
                <?php while($diskusi = $resultDiskusi->fetch_assoc()): ?>
                    <div class="discussion-card bg-white border border-gray-200 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($diskusi['judul']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    Oleh : <?php echo htmlspecialchars($diskusi['full_name']); ?> (<?php echo htmlspecialchars($diskusi['role']); ?>) - 
                                    <?php echo date('d M Y, H:i', strtotime($diskusi['created_at'])); ?>
                                </p>
                            </div>
                            <?php if (isset($_SESSION['user']['id']) && $diskusi['user_id'] == $_SESSION['user']['id']): ?>
                            <div class="delete-btn">
                                <a href="?delete_discussion=<?php echo $diskusi['id']; ?>" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus diskusi ini? Semua balasan juga akan dihapus.');" 
                                   class="flex items-center text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash mr-1"></i>
                                    <span class="text-sm">Hapus</span>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p class="mt-2 text-gray-700"><?php echo nl2br(htmlspecialchars($diskusi['konten'])); ?></p>

                        <?php if (!empty($diskusi['file_path'])): ?>
                            <div class="attachment-container mt-3">
                                <p class="text-sm font-medium text-gray-700 mb-2">Lampiran:</p>
                                <?php 
                                $fileExt = pathinfo($diskusi['file_name'], PATHINFO_EXTENSION);
                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                $docExtensions = ['doc', 'docx'];
                                $pptExtensions = ['ppt', 'pptx'];
                                $pdfExtensions = ['pdf'];
                                ?>
                                
                                <?php if (in_array(strtolower($fileExt), $imageExtensions)): ?>
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-image text-blue-500 mr-2"></i>
                                        <span><?php echo htmlspecialchars($diskusi['file_name']); ?></span>
                                    </div>
                                    <img src="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                         alt="Lampiran gambar" 
                                         class="attachment-preview cursor-pointer"
                                         onclick="window.open('<?php echo htmlspecialchars($diskusi['file_path']); ?>', '_blank')">
                                    <div class="mt-2">
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           target="_blank" 
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-external-link-alt mr-2"></i> Lihat Gambar
                                        </a>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($diskusi['file_name']); ?>"
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 ml-4">
                                            <i class="fas fa-download mr-2"></i> Unduh
                                        </a>
                                    </div>
                                    
                                <?php elseif (in_array(strtolower($fileExt), $pdfExtensions)): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                            <?php echo htmlspecialchars($diskusi['file_name']); ?>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($diskusi['file_name']); ?>"
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-download mr-1"></i> Unduh
                                        </a>
                                    </div>
                                    
                                <?php elseif (in_array(strtolower($fileExt), $docExtensions)): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-file-word text-blue-600 mr-2"></i>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                            <?php echo htmlspecialchars($diskusi['file_name']); ?>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($diskusi['file_name']); ?>"
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-download mr-1"></i> Unduh
                                        </a>
                                    </div>
                                    
                                <?php elseif (in_array(strtolower($fileExt), $pptExtensions)): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-file-powerpoint text-orange-500 mr-2"></i>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                            <?php echo htmlspecialchars($diskusi['file_name']); ?>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($diskusi['file_name']); ?>"
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-download mr-1"></i> Unduh
                                        </a>
                                    </div>
                                    
                                <?php else: ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                            <?php echo htmlspecialchars($diskusi['file_name']); ?>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($diskusi['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($diskusi['file_name']); ?>"
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-download mr-1"></i> Unduh
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        $diskusiId = $diskusi['id'];
                        $stmtBalasan = $mysqli->prepare("SELECT r.id, r.content, r.created_at, r.file_path, r.file_name, r.file_type, u.full_name, u.role, r.user_id 
                                                         FROM replies r 
                                                         JOIN users u ON r.user_id = u.id 
                                                         WHERE r.discussion_id = ? 
                                                         ORDER BY r.created_at ASC");
                        $stmtBalasan->bind_param("i", $diskusiId);
                        $stmtBalasan->execute();
                        $resultBalasan = $stmtBalasan->get_result();
                        ?>

                        <div class="mt-4 pl-4 border-l-4 border-gray-300">
                            <h4 class="font-semibold mb-2">Balasan:</h4>
                            <?php if ($resultBalasan->num_rows > 0): ?>
                                <?php while($balasan = $resultBalasan->fetch_assoc()): ?>
                                    <div class="reply-card bg-gray-50 p-3 rounded-lg mb-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">
                                                    Oleh : <?php echo htmlspecialchars($balasan['full_name']); ?> (<?php echo htmlspecialchars($balasan['role']); ?>)
                                                    <span class="text-xs text-gray-400 ml-2"><?php echo date('d M Y, H:i', strtotime($balasan['created_at'])); ?></span>
                                                </p>
                                            </div>
                                            <?php if ($balasan['user_id'] == ($_SESSION['user']['id'] ?? null)): ?>
                                            <div class="delete-btn">
                                                <a href="?delete_reply=<?php echo $balasan['id']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus balasan ini?');" 
                                                   class="flex items-center text-red-600 hover:text-red-800 text-sm">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    <span class="text-xs">Hapus</span>
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($balasan['content'])); ?></p>

                                        <?php if (!empty($balasan['file_path'])): ?>
                                            <div class="attachment-container mt-2">
                                                <p class="text-sm font-medium text-gray-700 mb-2">Lampiran:</p>
                                                <?php 
                                                $fileExt = pathinfo($balasan['file_name'], PATHINFO_EXTENSION);
                                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                                $docExtensions = ['doc', 'docx'];
                                                $pptExtensions = ['ppt', 'pptx'];
                                                $pdfExtensions = ['pdf'];
                                                ?>
                                                
                                                <?php if (in_array(strtolower($fileExt), $imageExtensions)): ?>
                                                    <div class="flex items-center mb-2">
                                                        <i class="fas fa-image text-blue-500 mr-2"></i>
                                                        <span><?php echo htmlspecialchars($balasan['file_name']); ?></span>
                                                    </div>
                                                    <img src="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                         alt="Lampiran gambar" 
                                                         class="attachment-preview cursor-pointer"
                                                         onclick="window.open('<?php echo htmlspecialchars($balasan['file_path']); ?>', '_blank')">
                                                    <div class="mt-2">
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           target="_blank" 
                                                           class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                                            <i class="fas fa-external-link-alt mr-2"></i> Lihat Gambar
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           download="<?php echo htmlspecialchars($balasan['file_name']); ?>"
                                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 ml-4">
                                                            <i class="fas fa-download mr-2"></i> Unduh
                                                        </a>
                                                    </div>
                                                    
                                                <?php elseif (in_array(strtolower($fileExt), $pdfExtensions)): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           target="_blank" 
                                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                                            <?php echo htmlspecialchars($balasan['file_name']); ?>
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           download="<?php echo htmlspecialchars($balasan['file_name']); ?>"
                                                           class="text-blue-600 hover:text-blue-800">
                                                            <i class="fas fa-download mr-1"></i> Unduh
                                                        </a>
                                                    </div>
                                                    
                                                <?php elseif (in_array(strtolower($fileExt), $docExtensions)): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-file-word text-blue-600 mr-2"></i>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           target="_blank" 
                                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                                            <?php echo htmlspecialchars($balasan['file_name']); ?>
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           download="<?php echo htmlspecialchars($balasan['file_name']); ?>"
                                                           class="text-blue-600 hover:text-blue-800">
                                                            <i class="fas fa-download mr-1"></i> Unduh
                                                        </a>
                                                    </div>
                                                    
                                                <?php elseif (in_array(strtolower($fileExt), $pptExtensions)): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-file-powerpoint text-orange-500 mr-2"></i>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           target="_blank" 
                                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                                            <?php echo htmlspecialchars($balasan['file_name']); ?>
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           download="<?php echo htmlspecialchars($balasan['file_name']); ?>"
                                                           class="text-blue-600 hover:text-blue-800">
                                                            <i class="fas fa-download mr-1"></i> Unduh
                                                        </a>
                                                    </div>
                                                    
                                                <?php else: ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           target="_blank" 
                                                           class="text-blue-600 hover:text-blue-800 mr-4">
                                                            <?php echo htmlspecialchars($balasan['file_name']); ?>
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($balasan['file_path']); ?>" 
                                                           download="<?php echo htmlspecialchars($balasan['file_name']); ?>"
                                                           class="text-blue-600 hover:text-blue-800">
                                                            <i class="fas fa-download mr-1"></i> Unduh
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-gray-500">Belum ada balasan.</p>
                            <?php endif; ?>
                            <?php $stmtBalasan->close(); ?>

                            <!-- Reply Form -->
                            <form method="POST" enctype="multipart/form-data" class="mt-4">
                                <input type="hidden" name="discussion_id" value="<?php echo $diskusiId; ?>" />
                                <div>
                                    <textarea name="content" rows="2" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Tulis balasan Anda" required></textarea>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Lampiran (opsional, maks 2MB)</label>
                                    <input type="file" name="attachment" class="text-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx" />
                                </div>
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="bg-blue-600 text-white py-1 px-4 rounded-lg hover:bg-blue-700 transition duration-200">Balas</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">Belum ada diskusi.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // File preview functionality
        document.getElementById('attachment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const imagePreview = document.getElementById('image-preview');
        
        // Reset previews
        filePreview.classList.remove('show');
        imagePreview.classList.remove('show');
        imagePreview.src = '#';
        
        if (file) {
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.add('show');
                }
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = file.name;
                filePreview.classList.add('show');
            }
        }
    });

    function clearFileInput() {
        document.getElementById('attachment').value = '';
        document.getElementById('file-preview').classList.remove('show');
        document.getElementById('image-preview').classList.remove('show');
        document.getElementById('image-preview').src = '#';
    }
</script>
</body>
</html>

<?php
$mysqli->close();
?>