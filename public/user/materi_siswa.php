<?php 
session_start(); 

// Database connection
$host = 'localhost'; 
$user = 'root'; 
$password = ''; 
$dbname = 'smpm29'; 
$mysqli = new mysqli($host, $user, $password, $dbname); 

if ($mysqli->connect_error) { 
    die("Connection failed: " . $mysqli->connect_error); 
} 

// Query to fetch the latest materials
$sql = "SELECT * FROM materials ORDER BY created_at DESC"; 
$result = $mysqli->query($sql); 

// Set base path for file upload
$baseUploadPath = __DIR__ . '/uploads/';
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
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; } 
        .sidebar { transition: all 0.3s ease; } 
        .sidebar-item { transition: all 0.2s ease; } 
        .sidebar-item:hover { background-color: #f1f5f9; border-left: 3px solid #3b82f6; } 
        .sidebar-item.active { background-color: #e0e7ff; color: #3b82f6; font-weight: 500; border-left: 3px solid #3b82f6; } 
        .preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .preview-content {
            background: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
        }
    </style> 
</head> 
<body> 
<div class="flex"> 
    <!-- Sidebar for students --> 
    <div class="w-64 h-screen bg-white shadow-lg fixed left-0 sidebar"> 
        <div class="flex items-center justify-center p-4 border-b"> 
            <span class="text-xl font-bold text-gray-800">E-Learning Class</span> 
        </div> 
        <div class="p-4"> 
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p> 
            <a href="dashboard_siswa.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1"> 
                <i class="fas fa-tachometer-alt w-5 mr-3"></i> 
                <span>Dashboard</span> 
            </a>
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
            <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-comments w-5 mr-3"></i>
                <span>Forum Diskusi</span>
            </a>
            <a href="materi_siswa.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-book-open w-5 mr-3"></i>
                <span>Materi Pembelajaran</span>
            </a>
            <a href="kuis_siswa.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                <i class="fas fa-question-circle w-5 mr-3"></i>
                <span>Kuis</span>
            </a>
            <a href="tugas_siswa.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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
            <p class="text-sm text-gray-600">Pelajari materi dari guru Anda</p>
        </div>

        <!-- Material List -->
        <div class="space-y-6">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex">
                            <div class="bg-blue-100 p-3 rounded-full h-12 w-12 flex items-center justify-center mr-4">
                                <?php
                                $filePath = $row['file_path'];
                                $ext = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : '';
                                
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
                                $filePath = 'uploads/' . htmlspecialchars($row['file_path']);
                                $fullPath = $baseUploadPath . $row['file_path'];
                                if (file_exists($fullPath)): 
                                    $fileSize = filesize($fullPath);
                                    $fileUrl = htmlspecialchars($filePath);
                                ?>
                                    <div class="mt-3">
                                        <div class="flex items-center space-x-4">
                                            <a href="<?= $fileUrl ?>" download class="text-sm text-blue-600 hover:underline flex items-center">
                                                <i class="fas fa-download mr-2"></i>
                                                Download File (<?= round($fileSize / 1024 / 1024, 2) ?> MB)
                                            </a>
                                            <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])): ?>
                                                <button onclick="previewFile('<?= $fileUrl ?>', '<?= $ext ?>')" class="text-sm text-green-600 hover:underline flex items-center">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    Lihat File
                                                </button>
                                            <?php elseif (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                                                <button onclick="previewFile('<?= $fileUrl ?>', '<?= $ext ?>')" class="text-sm text-green-600 hover:underline flex items-center">
                                                    <i class="fas fa-play mr-2"></i>
                                                    Putar Video
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-3 text-sm text-red-500">
                                        File tidak tersedia di server
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">Belum ada materi yang tersedia.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal hidden">
    <div class="preview-content bg-white rounded-lg p-6 max-w-4xl w-full max-h-screen overflow-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Pratinjau File</h3>
            <button onclick="closePreview()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="previewContent" class="mt-4">
            <!-- Preview content will be inserted here -->
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="closePreview()" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Tutup</button>
        </div>
    </div>
</div>

<script>
    // File preview functions
    function previewFile(fileUrl, fileType) {
        const previewModal = document.getElementById('previewModal');
        const previewContent = document.getElementById('previewContent');
        
        previewContent.innerHTML = '';
        
        if (fileType.match(/^(jpg|jpeg|png|gif)$/)) {
            previewContent.innerHTML = `<img src="${fileUrl}" class="max-w-full h-auto mx-auto">`;
        } else if (fileType === 'pdf') {
            previewContent.innerHTML = `
                <iframe src="${fileUrl}#toolbar=0" class="w-full h-96" frameborder="0"></iframe>
                <p class="text-sm text-gray-500 mt-2">PDF preview mungkin tidak tersedia di semua browser. Silakan download untuk melihat lengkap.</p>
            `;
        } else if (fileType.match(/^(mp4|mov|avi)$/)) {
            previewContent.innerHTML = `
                <video controls autoplay class="w-full">
                    <source src="${fileUrl}" type="video/${fileType}">
                    Browser Anda tidak mendukung pemutaran video.
                </video>
            `;
        } else {
            previewContent.innerHTML = `
                <div class="p-4 bg-gray-100 rounded-lg">
                    <p class="text-gray-700">Pratinjau tidak tersedia untuk jenis file ini.</p>
                    <a href="${fileUrl}" download class="text-blue-600 hover:underline mt-2 inline-block">
                        <i class="fas fa-download mr-2"></i>Download File
                    </a>
                </div>
            `;
        }
        
        previewModal.classList.remove('hidden');
    }

    function closePreview() {
        document.getElementById('previewModal').classList.add('hidden');
        
        // Stop all videos when closing preview
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.pause();
        });
    }

    // Close modal when clicking outside
    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreview();
        }
    });
</script>
</body> 
</html> 

<?php $mysqli->close(); ?>