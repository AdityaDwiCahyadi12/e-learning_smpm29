<?php
// Mulai session
session_start();

// Konfigurasi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

// Membuat koneksi PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ambil data user dari session jika ada
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

// Fungsi untuk handle upload file
function handleFileUpload($file, $uploadDir = 'uploads/') {
    // Direktori baru untuk penyimpanan file
    $newUploadDir = 'C:/xampp/htdocs/smpm29/public/guru/uploads/';
    
    // Buat kedua direktori jika belum ada
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
            'new_file_path' => $newFileDestination,
            'file_name' => $fileName,
            'file_type' => $fileType
        ];
    } else {
        return ['error' => 'Failed to move uploaded file.'];
    }
}

// Proses pembuatan diskusi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['judul'])) {
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];
    
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
    
    try {
        $stmt = $pdo->prepare("INSERT INTO discussions (user_id, title, content, file_path, file_name, file_type) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $judul, $konten, $filePath, $fileName, $fileType]);
        
        header("Location: forum_diskusi.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating discussion: " . $e->getMessage());
    }
}

// Proses balasan diskusi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diskusi_id'])) {
    $diskusiId = $_POST['diskusi_id'];
    $konten = $_POST['konten'];
    
    $filePath = null;
    $fileName = null;
    $fileType = null;
    
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === 0) {
        $uploadResult = handleFileUpload($_FILES['file_lampiran']);
        if (isset($uploadResult['error'])) {
            die($uploadResult['error']);
        }
        $filePath = $uploadResult['file_path'];
        $fileName = $uploadResult['file_name'];
        $fileType = $uploadResult['file_type'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO replies (discussion_id, user_id, content, file_path, file_name, file_type) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$diskusiId, $userId, $konten, $filePath, $fileName, $fileType]);
        
        header("Location: forum_diskusi.php");
        exit();
    } catch (PDOException $e) {
        die("Error posting reply: " . $e->getMessage());
    }
}

// Proses penghapusan diskusi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_discussion'])) {
    $discussionId = $_POST['discussion_id'];
    
    try {
        // Periksa apakah diskusi milik user yang login
        $stmt = $pdo->prepare("SELECT user_id FROM discussions WHERE id = ?");
        $stmt->execute([$discussionId]);
        $discussion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($discussion && $discussion['user_id'] == $userId) {
            // Hapus semua balasan terlebih dahulu
            $pdo->prepare("DELETE FROM replies WHERE discussion_id = ?")->execute([$discussionId]);
            // Hapus diskusi
            $pdo->prepare("DELETE FROM discussions WHERE id = ?")->execute([$discussionId]);
            
            // Redirect untuk refresh halaman
            header("Location: forum_diskusi.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Error menghapus diskusi: " . $e->getMessage());
    }
}

// Proses penghapusan balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reply'])) {
    $replyId = $_POST['reply_id'];
    
    try {
        // Periksa apakah balasan milik user yang login
        $stmt = $pdo->prepare("SELECT user_id FROM replies WHERE id = ?");
        $stmt->execute([$replyId]);
        $reply = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reply && $reply['user_id'] == $userId) {
            // Hapus balasan
            $pdo->prepare("DELETE FROM replies WHERE id = ?")->execute([$replyId]);
            
            // Redirect untuk refresh halaman
            header("Location: forum_diskusi.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Error menghapus balasan: " . $e->getMessage());
    }
}

// Check if user photo exists
$photoPath = file_exists("uploads/user_$userId.jpg") ? "uploads/user_$userId.jpg" : "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User') . "&background=3b82f6&color=fff";

// Ambil semua diskusi terbaru beserta nama dan role user yang mengunggah
$stmt = $pdo->query("SELECT d.id, d.user_id, u.full_name, u.role, d.title, d.content, d.created_at, d.file_path, d.file_name, d.file_type FROM discussions d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
$discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua balasan untuk diskusi beserta nama user yang membalas dan file lampiran
$repliesStmt = $pdo->query("SELECT r.id, r.user_id, r.discussion_id, u.full_name, u.role, r.content, r.file_path, r.file_name, r.file_type, r.created_at FROM replies r JOIN users u ON r.user_id = u.id ORDER BY r.created_at ASC");
$replies = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forum Diskusi - E-Learning Class</title>
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
        .forum-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .forum-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
        .file-icon {
            color: #3b82f6;
            margin-right: 8px;
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
        .reply-card {
            border-left: 3px solid #e2e8f0;
            padding-left: 15px;
            margin-left: 15px;
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
                    <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profil" class="w-10 h-10 rounded-full mr-3 object-cover">
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
                <a href="forum_diskusi.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">Forum Diskusi Kelas</h1>
                        <p class="text-sm text-gray-600">Diskusikan materi pembelajaran dengan teman sekelas dan guru</p>
                    </div>
                    <button id="btnNewDiscussion" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-plus mr-2"></i> Diskusi Baru
                    </button>
                </div>
            </div>

            <!-- Form Diskusi Baru -->
            <div class="forum-card bg-white rounded-lg shadow-sm p-6 mb-6 hidden" id="newDiscussionForm">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-edit mr-2 text-blue-500"></i> Buat Diskusi Baru
                </h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="judul" class="block text-sm font-semibold text-gray-600 mb-1">Judul Diskusi</label>
                        <input type="text" id="judul" name="judul" class="w-full p-3 border border-gray-300 rounded-lg input-field" placeholder="Masukkan judul diskusi" required />
                    </div>
                    <div class="mb-4">
                        <label for="konten" class="block text-sm font-semibold text-gray-600 mb-1">Isi Diskusi</label>
                        <textarea id="konten" name="konten" rows="4" class="w-full p-3 border border-gray-300 rounded-lg input-field" placeholder="Tulis isi diskusi Anda secara detail" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Lampiran (opsional, maks 2MB)</label>
                        <input type="file" name="attachment" id="attachment" class="w-full p-2 border border-gray-300 rounded-lg" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx">
                        <div id="file-preview" class="file-preview">
                            <span id="file-name"></span>
                            <button type="button" onclick="clearFileInput()" class="ml-2 text-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <img id="image-preview" class="image-preview" src="#" alt="Preview gambar">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancelDiscussion" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg">
                            Posting Diskusi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Diskusi -->
            <div class="space-y-6">
                <?php if (empty($discussions)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                        <i class="fas fa-comments text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">Belum ada diskusi. Jadilah yang pertama memulai diskusi!</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($discussions as $discussion): ?>
                    <div class="forum-card bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <i class="fas fa-comments text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($discussion['title']) ?></h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Oleh : <?= htmlspecialchars($discussion['full_name']) ?> (<?= htmlspecialchars($discussion['role']) ?>) • 
                                            <span class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($discussion['created_at'])) ?></span>
                                        </p>
                                    </div>
                                    <?php if ($discussion['user_id'] == $userId): ?>
                                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus diskusi ini?')">
                                        <input type="hidden" name="discussion_id" value="<?= $discussion['id'] ?>">
                                        <input type="hidden" name="delete_discussion" value="1">
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-700 mt-3"><?= nl2br(htmlspecialchars($discussion['content'])) ?></p>

                                <!-- Tampilkan lampiran diskusi -->
                                <?php if (!empty($discussion['file_path'])): ?>
                                    <div class="attachment-container mt-3">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Lampiran:</p>
                                        <?php 
                                        $fileExt = pathinfo($discussion['file_name'], PATHINFO_EXTENSION);
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                        $docExtensions = ['doc', 'docx'];
                                        $pptExtensions = ['ppt', 'pptx'];
                                        $pdfExtensions = ['pdf'];
                                        ?>
                                        
                                        <?php if (in_array(strtolower($fileExt), $imageExtensions)): ?>
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-image text-blue-500 mr-2"></i>
                                                <span><?= htmlspecialchars($discussion['file_name']) ?></span>
                                            </div>
                                            <img src="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                 alt="Lampiran gambar" 
                                                 class="attachment-preview cursor-pointer"
                                                 onclick="window.open('<?= htmlspecialchars($discussion['file_path']) ?>', '_blank')">
                                            <div class="mt-2">
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   target="_blank" 
                                                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-external-link-alt mr-2"></i> Lihat Gambar
                                                </a>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   download="<?= htmlspecialchars($discussion['file_name']) ?>"
                                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 ml-4">
                                                    <i class="fas fa-download mr-2"></i> Unduh
                                                </a>
                                            </div>
                                            
                                        <?php elseif (in_array(strtolower($fileExt), $pdfExtensions)): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 mr-4">
                                                    <?= htmlspecialchars($discussion['file_name']) ?>
                                                </a>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   download="<?= htmlspecialchars($discussion['file_name']) ?>"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-download mr-1"></i> Unduh
                                                </a>
                                            </div>
                                            
                                        <?php elseif (in_array(strtolower($fileExt), $docExtensions)): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-file-word text-blue-600 mr-2"></i>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 mr-4">
                                                    <?= htmlspecialchars($discussion['file_name']) ?>
                                                </a>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   download="<?= htmlspecialchars($discussion['file_name']) ?>"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-download mr-1"></i> Unduh
                                                </a>
                                            </div>
                                            
                                        <?php elseif (in_array(strtolower($fileExt), $pptExtensions)): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-file-powerpoint text-orange-500 mr-2"></i>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 mr-4">
                                                    <?= htmlspecialchars($discussion['file_name']) ?>
                                                </a>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   download="<?= htmlspecialchars($discussion['file_name']) ?>"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-download mr-1"></i> Unduh
                                                </a>
                                            </div>
                                            
                                        <?php else: ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 mr-4">
                                                    <?= htmlspecialchars($discussion['file_name']) ?>
                                                </a>
                                                <a href="<?= htmlspecialchars($discussion['file_path']) ?>" 
                                                   download="<?= htmlspecialchars($discussion['file_name']) ?>"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-download mr-1"></i> Unduh
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Reply Form -->
                                <div class="mt-6">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="diskusi_id" value="<?= $discussion['id'] ?>" />
                                        <div class="mb-4">
                                            <textarea name="konten" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Tulis balasan Anda..." required></textarea>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-semibold text-gray-600 mb-1">Lampirkan File (PDF, JPG, PNG, DOC, PPT, maks 2MB)</label>
                                            <input type="file" name="file_lampiran" id="file_lampiran_<?= $discussion['id'] ?>" class="w-full p-2 border border-gray-300 rounded-lg" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx">
                                            <div id="file_preview_<?= $discussion['id'] ?>" class="file-preview">
                                                <span id="file_name_<?= $discussion['id'] ?>"></span>
                                                <button type="button" onclick="clearFileInput(<?= $discussion['id'] ?>)" class="ml-2 text-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <img id="image_preview_<?= $discussion['id'] ?>" class="image-preview" src="#" alt="Preview gambar">
                                        </div>
                                        <div class="flex justify-end space-x-2">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg">
                                                Kirim Balasan
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Tampilkan balasan -->
                                <div class="mt-4 space-y-4">
                                    <?php 
                                    $discussionReplies = array_filter($replies, function($reply) use ($discussion) {
                                        return $reply['discussion_id'] == $discussion['id'];
                                    });
                                    ?>
                                    
                                    <?php if (empty($discussionReplies)): ?>
                                        <p class="text-gray-500 text-sm">Belum ada balasan untuk diskusi ini.</p>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($discussionReplies as $reply): ?>
                                        <div class="reply-card">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold"><?= htmlspecialchars($reply['full_name']) ?> (<?= htmlspecialchars($reply['role']) ?>)</p>
                                                    <p class="text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($reply['created_at'])) ?></span>
                                                </div>
                                                <?php if ($reply['user_id'] == $userId): ?>
                                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus balasan ini?')">
                                                    <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                                                    <input type="hidden" name="delete_reply" value="1">
                                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-gray-700 mt-2"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                            
                                            <!-- Tampilkan lampiran balasan -->
                                            <?php if (!empty($reply['file_path'])): ?>
                                                <div class="attachment-container mt-3">
                                                    <p class="text-sm font-medium text-gray-700 mb-2">Lampiran:</p>
                                                    <?php 
                                                    $fileExt = pathinfo($reply['file_name'], PATHINFO_EXTENSION);
                                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                                    $docExtensions = ['doc', 'docx'];
                                                    $pptExtensions = ['ppt', 'pptx'];
                                                    $pdfExtensions = ['pdf'];
                                                    ?>
                                                    
                                                    <?php if (in_array(strtolower($fileExt), $imageExtensions)): ?>
                                                        <div class="flex items-center mb-2">
                                                            <i class="fas fa-image text-blue-500 mr-2"></i>
                                                            <span><?= htmlspecialchars($reply['file_name']) ?></span>
                                                        </div>
                                                        <img src="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                             alt="Lampiran gambar" 
                                                             class="attachment-preview cursor-pointer"
                                                             onclick="window.open('<?= htmlspecialchars($reply['file_path']) ?>', '_blank')">
                                                        <div class="mt-2">
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               target="_blank" 
                                                               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-external-link-alt mr-2"></i> Lihat Gambar
                                                            </a>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               download="<?= htmlspecialchars($reply['file_name']) ?>"
                                                               class="inline-flex items-center text-blue-600 hover:text-blue-800 ml-4">
                                                                <i class="fas fa-download mr-2"></i> Unduh
                                                            </a>
                                                        </div>
                                                        
                                                    <?php elseif (in_array(strtolower($fileExt), $pdfExtensions)): ?>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:text-blue-800 mr-4">
                                                                <?= htmlspecialchars($reply['file_name']) ?>
                                                            </a>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               download="<?= htmlspecialchars($reply['file_name']) ?>"
                                                               class="text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-download mr-1"></i> Unduh
                                                            </a>
                                                        </div>
                                                        
                                                    <?php elseif (in_array(strtolower($fileExt), $docExtensions)): ?>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-file-word text-blue-600 mr-2"></i>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:text-blue-800 mr-4">
                                                                <?= htmlspecialchars($reply['file_name']) ?>
                                                            </a>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               download="<?= htmlspecialchars($reply['file_name']) ?>"
                                                               class="text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-download mr-1"></i> Unduh
                                                            </a>
                                                        </div>
                                                        
                                                    <?php elseif (in_array(strtolower($fileExt), $pptExtensions)): ?>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-file-powerpoint text-orange-500 mr-2"></i>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:text-blue-800 mr-4">
                                                                <?= htmlspecialchars($reply['file_name']) ?>
                                                            </a>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               download="<?= htmlspecialchars($reply['file_name']) ?>"
                                                               class="text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-download mr-1"></i> Unduh
                                                            </a>
                                                        </div>
                                                        
                                                    <?php else: ?>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:text-blue-800 mr-4">
                                                                <?= htmlspecialchars($reply['file_name']) ?>
                                                            </a>
                                                            <a href="<?= htmlspecialchars($reply['file_path']) ?>" 
                                                               download="<?= htmlspecialchars($reply['file_name']) ?>"
                                                               class="text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-download mr-1"></i> Unduh
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan form diskusi baru
        document.getElementById('btnNewDiscussion').addEventListener('click', function() {
            document.getElementById('newDiscussionForm').classList.toggle('hidden');
        });

        // Fungsi untuk membatalkan form diskusi baru
        document.getElementById('cancelDiscussion').addEventListener('click', function() {
            document.getElementById('newDiscussionForm').classList.add('hidden');
        });

        // Fungsi untuk preview lampiran diskusi baru
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

        // Fungsi untuk menghapus input file
        function clearFileInput(discussionId = null) {
            if (discussionId) {
                document.getElementById('file_lampiran_' + discussionId).value = '';
                document.getElementById('file_preview_' + discussionId).classList.remove('show');
                document.getElementById('image_preview_' + discussionId).classList.remove('show');
                document.getElementById('image_preview_' + discussionId).src = '#';
            } else {
                document.getElementById('attachment').value = '';
                document.getElementById('file-preview').classList.remove('show');
                document.getElementById('image-preview').classList.remove('show');
                document.getElementById('image-preview').src = '#';
            }
        }

        // Fungsi untuk preview lampiran balasan
        document.querySelectorAll('input[type="file"]').forEach(input => {
            if (input.id.startsWith('file_lampiran_')) {
                input.addEventListener('change', function(e) {
                    const discussionId = this.id.split('_')[2];
                    const file = e.target.files[0];
                    const filePreview = document.getElementById('file_preview_' + discussionId);
                    const fileName = document.getElementById('file_name_' + discussionId);
                    const imagePreview = document.getElementById('image_preview_' + discussionId);
                    
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
            }
        });

        // Animasi untuk forum card saat dihover
        document.querySelectorAll('.forum-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });

        // Smooth scroll untuk halaman yang panjang
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Validasi form sebelum submit
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const textareas = this.querySelectorAll('textarea');
                let isValid = true;
                
                textareas.forEach(textarea => {
                    if (textarea.value.trim() === '') {
                        textarea.style.borderColor = 'red';
                        isValid = false;
                    } else {
                        textarea.style.borderColor = '';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Silakan isi semua field yang diperlukan!');
                }
            });
        });

        // Auto-resize textarea saat mengetik
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
</body>
</html>