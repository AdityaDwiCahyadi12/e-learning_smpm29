<?php
session_start();
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';
$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Proses form tambah materi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'] ?: $_POST['new_kategori'];
    $konten = $_POST['konten'];
    $file_path = '';

    // Cek apakah ada file yang diunggah
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_materi']['tmp_name'];
        $file_name = basename($_FILES['file_materi']['name']);
        $file_dir = 'uploads/';
        $file_path = $file_dir . $file_name;

        // Pindahkan file ke folder uploads
        if (!move_uploaded_file($file_tmp, $file_path)) {
            $_SESSION['message'] = "Gagal mengunggah file.";
            $_SESSION['message_type'] = "error";
            header("Location: tambah_materi.php");
            exit();
        }
    }

    // Simpan data materi ke database
    $stmt = $mysqli->prepare("INSERT INTO materials (title, category, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $judul, $kategori, $konten, $file_path);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Materi berhasil ditambahkan!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menambahkan materi: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    header("Location: tambah_materi.php");
    exit();
}

// Proses hapus materi
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Ambil file_path dari database untuk dihapus
    $stmt = $mysqli->prepare("SELECT file_path FROM materials WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $file_path = $row['file_path'];
        
        // Hapus materi dari database
        $stmt = $mysqli->prepare("DELETE FROM materials WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            // Hapus file dari server
            if (!empty($file_path)) {
                $full_path = __DIR__ . '/' . $file_path;
                if (file_exists($full_path)) {
                    unlink($full_path); // Hapus file
                }
            }
            $_SESSION['message'] = "Materi berhasil dihapus!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menghapus materi: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Materi tidak ditemukan.";
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
    header("Location: tambah_materi.php");
    exit();
}

// Query untuk mengambil materi terbaru
$sql = "SELECT * FROM materials ORDER BY created_at DESC LIMIT 10";
$result = $mysqli->query($sql);

// Ambil kategori yang ada
$categories = [];
$category_query = $mysqli->query("SELECT DISTINCT category FROM materials WHERE category IS NOT NULL AND category != ''");
while ($row = $category_query->fetch_assoc()) {
    $categories[] = $row['category'];
}

?>

<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Tambah Materi - E-Learning Class</title> 
    <script src="https://cdn.tailwindcss.com"></script> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <style> 
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; } 
        .sidebar { transition: all 0.3s ease; } 
        .sidebar-item { transition: all 0.2s ease; } 
        .sidebar-item:hover { background-color: #f1f5f9; border-left: 3px solid #3b82f6; } 
        .sidebar-item.active { background-color: #e0e7ff; color: #3b82f6; font-weight: 500; border-left: 3px solid #3b82f6; } 
        .file-upload { border: 2px dashed #cbd5e0; transition: all 0.3s ease; } 
        .file-upload:hover { border-color: #3b82f6; background-color: #f8fafc; } 
        .action-btn { transition: all 0.2s ease; } 
        .action-btn:hover { transform: scale(1.1); } 
        .file-preview-container { max-height: 300px; overflow-y: auto; } 
        .file-preview { border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 1rem; margin-bottom: 1rem; } 
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
            <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'forum_diskusi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
            </a>
            <a href="tambah_materi.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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
            <h1 class="text-xl font-semibold text-gray-800">Tambah Materi Pembelajaran</h1>
            <p class="text-sm text-gray-600">Unggah materi pembelajaran dalam bentuk teks atau file</p>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="mt-4 p-3 bg-<?php echo $_SESSION['message_type'] === 'success' ? 'green' : 'red'; ?>-100 text-<?php echo $_SESSION['message_type'] === 'success' ? 'green' : 'red'; ?>-700 rounded-lg">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>
        </div>
        
        <!-- Create New Material -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Form Tambah Materi</h2>
            <form action="proses_tambah_materi.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="judul" class="block text-sm font-semibold text-gray-600">Judul Materi</label>
                    <input type="text" id="judul" name="judul" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Masukkan judul materi" required>
                </div>
                
                <div class="mb-4">
                    <label for="kategori" class="block text-sm font-semibold text-gray-600">Kategori Materi</label>
                    <div class="flex">
                        <select id="kategori" name="kategori" class="w-full p-3 border border-gray-300 rounded-lg mr-2">
                            <option value="">Pilih kategori atau buat baru</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                            <option value="IPA">IPA</option>
                            <option value="IPS">IPS</option>
                            <option value="Kemuhammadiyahan">Kemuhammadiyahan</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="new_kategori" name="new_kategori" class="w-full p-3 border border-gray-300 rounded-lg hidden" placeholder="Kategori baru">
                        <button type="button" id="toggle_kategori" class="bg-blue-500 text-white px-4 rounded-lg hover:bg-blue-600">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="konten" class="block text-sm font-semibold text-gray-600">Deskripsi Materi</label>
                    <textarea id="konten" name="konten" rows="4" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Tulis deskripsi materi Anda" required></textarea>
                </div>
                
                <!-- File Upload Section -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Lampiran Materi (PDF/DOC/PPT/IMG/VIDEO)</label>
                    <div class="file-upload rounded-lg p-8 text-center">
                        <input type="file" id="file_materi" name="file_materi" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.mov,.avi">
                        <div id="file-upload-content" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500">Klik untuk mengunggah file atau drag & drop</p>
                            <p class="text-xs text-gray-400 mt-1">Format yang didukung: PDF, DOC, PPT, JPG, PNG, MP4 (Maks. 25MB)</p>
                        </div>
                        <div id="file-info" class="hidden mt-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-3"></i>
                                    <div>
                                        <p id="file-name" class="text-sm font-medium text-gray-700"></p>
                                        <p id="file-size" class="text-xs text-gray-500"></p>
                                    </div>
                                </div>
                                <button type="button" id="remove-file" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="file-preview-container" class="file-preview-container mt-4 hidden">
                        <div id="file-preview" class="file-preview"></div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="reset" class="bg-gray-200 text-gray-700 py-2 px-6 rounded-lg hover:bg-gray-300">Reset</button>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700">Simpan Materi</button>
                </div>
            </form>
        </div>
        
        <!-- Recent Materials -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Materi Terbaru</h2>
                <span class="text-sm text-gray-500">Total: <?php echo $result ? $result->num_rows : 0; ?> materi</span>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
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
                                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                        <div class="flex space-x-2">
                                            <a href="edit_materi.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="tambah_materi.php?delete_id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 action-btn" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Kategori: <?php echo htmlspecialchars($row['category'] ?? 'Umum'); ?></p>
                                <p class="gray-700 mt-2 text-sm"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                                <?php if (!empty($row['file_path'])): ?>
                                    <?php
                                    $fullPath = __DIR__ . '/uploads/' . $row['file_path'];
                                    if (file_exists($fullPath)) {
                                        $fileSize = filesize($fullPath);
                                        $fileUrl = 'uploads/' . $row['file_path'];
                                    ?>
                                        <div class="mt-3">
                                            <div class="flex items-center space-x-4">
                                                <a href="<?= htmlspecialchars($fileUrl) ?>" download class="text-sm text-blue-600 hover:underline flex items-center">
                                                    <i class="fas fa-download mr-2"></i>
                                                    Download File (<?= round($fileSize / 1024 / 1024, 2) ?> MB)
                                                </a>
                                                <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])): ?>
                                                    <button onclick="previewFile('<?= htmlspecialchars($fileUrl) ?>', '<?= $ext ?>')" class="text-sm text-green-600 hover:underline flex items-center">
                                                        <i class="fas fa-eye mr-2"></i>
                                                        Lihat File
                                                    </button>
                                                <?php elseif (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                                                    <button onclick="previewFile('<?= htmlspecialchars($fileUrl) ?>', '<?= $ext ?>')" class="text-sm text-green-600 hover:underline flex items-center">
                                                        <i class="fas fa-play mr-2"></i>
                                                        Putar Video
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="mt-3 text-sm text-red-500">
                                            File tidak tersedia di server
                                        </div>
                                    <?php } ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">Belum ada materi.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-4xl w-full max-h-screen overflow-auto">
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
    // File upload handling
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file_materi');
        const fileUploadContent = document.getElementById('file-upload-content');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const removeFileBtn = document.getElementById('remove-file');
        const filePreviewContainer = document.getElementById('file-preview-container');
        const filePreview = document.getElementById('file-preview');
        const kategoriSelect = document.getElementById('kategori');
        const newKategoriInput = document.getElementById('new_kategori');
        const toggleKategoriBtn = document.getElementById('toggle_kategori');

        // Toggle between select and input for category
        toggleKategoriBtn.addEventListener('click', function() {
            if (kategoriSelect.classList.contains('hidden')) {
                // Switch back to select
                kategoriSelect.classList.remove('hidden');
                newKategoriInput.classList.add('hidden');
                toggleKategoriBtn.innerHTML = '<i class="fas fa-plus"></i>';
            } else {
                // Switch to new category input
                kategoriSelect.classList.add('hidden');
                newKategoriInput.classList.remove('hidden');
                toggleKategoriBtn.innerHTML = '<i class="fas fa-list"></i>';
            }
        });

        fileUploadContent.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileType = file.type;
                const validTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'video/mp4',
                    'video/quicktime',
                    'video/x-msvideo'
                ];

                if (!validTypes.includes(fileType) && !file.name.match(/\.(pdf|doc|docx|ppt|pptx|jpg|jpeg|png|gif|mp4|mov|avi)$/i)) {
                    alert('Format file tidak didukung. Harap unggah file PDF, DOC, PPT, JPG, PNG, atau MP4.');
                    fileInput.value = ''; // reset input
                    return;
                }

                if (file.size > 25 * 1024 * 1024) { // 25MB limit
                    alert('Ukuran file terlalu besar. Maksimal 25MB.');
                    fileInput.value = ''; // reset input
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileUploadContent.classList.add('hidden');
                fileInfo.classList.remove('hidden');
                
                // Show preview if it's an image
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `<img src="${e.target.result}" class="max-w-full h-auto rounded-lg">`;
                        filePreviewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else if (file.type.match('video.*')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `
                            <video controls class="w-full rounded-lg">
                                <source src="${e.target.result}" type="${file.type}">
                                Browser Anda tidak mendukung pemutaran video.
                            </video>
                        `;
                        filePreviewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `
                            <iframe src="${e.target.result}" class="w-full h-96 rounded-lg" frameborder="0"></iframe>
                            <p class="text-sm text-gray-500 mt-2">PDF preview mungkin tidak tersedia di semua browser. Silakan download untuk melihat lengkap.</p>
                        `;
                        filePreviewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    filePreviewContainer.classList.add('hidden');
                }
            }
        });

        removeFileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            fileInput.value = '';
            fileUploadContent.classList.remove('hidden');
            fileInfo.classList.add('hidden');
            filePreviewContainer.classList.add('hidden');
            filePreview.innerHTML = '';
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Drag and drop functionality
        const fileUpload = document.querySelector('.file-upload');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUpload.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            fileUpload.classList.add('border-blue-500', 'bg-blue-50');
        }

        function unhighlight() {
            fileUpload.classList.remove('border-blue-500', 'bg-blue-50');
        }

        fileUpload.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;

            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });

    // File preview functions
    function previewFile(fileUrl, fileType) {
        const previewModal = document.getElementById('previewModal');
        const previewContent = document.getElementById('previewContent');
        
        previewContent.innerHTML = '';
        
        if (fileType.match(/^image/)) {
            previewContent.innerHTML = `<img src="${fileUrl}" class="max-w-full h-auto mx-auto">`;
        } else if (fileType === 'pdf') {
            previewContent.innerHTML = `
                <iframe src="${fileUrl}" class="w-full h-96" frameborder="0"></iframe>
                <p class="text-sm text-gray-500 mt-2">PDF preview mungkin tidak tersedia di semua browser. Silakan download untuk melihat lengkap.</p>
            `;
        } else if (fileType.match(/^video/)) {
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
    }
</script>
</body> 
</html>
