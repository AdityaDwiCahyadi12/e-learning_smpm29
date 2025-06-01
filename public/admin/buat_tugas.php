<?php
// Include koneksi database
$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Start session (jika Anda menggunakan session untuk menyimpan ID guru yang login)
session_start();

// Asumsi ID guru yang sedang login. Ganti dengan ID guru yang sebenarnya dari session Anda.
// Misalnya, $_SESSION['user_id'] setelah guru login.
$teacher_id = $_SESSION['user']['id'] ?? 1; // Contoh: Asumsikan guru dengan ID 1 sedang login

$message = '';
$message_type = ''; // 'success' or 'error'

// --- LOGIKA UNTUK MEMBUAT / MENGEDIT TUGAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_task' || $_POST['action'] === 'edit_task') {
        $task_id = $_POST['task_id'] ?? null;
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $due_date = $_POST['due_date'];
        $course_id = $_POST['course_id']; // Anda perlu menambahkan input course_id di form
        $max_score = $_POST['max_score'];

        if ($_POST['action'] === 'create_task') {
            try {
                $stmt = $pdo->prepare("INSERT INTO tasks (course_id, teacher_id, title, description, due_date, max_score, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$course_id, $teacher_id, $title, $description, $due_date, $max_score]);
                $task_id = $pdo->lastInsertId();
                $message = 'Tugas berhasil dibuat!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Gagal membuat tugas: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'edit_task') {
            try {
                $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, course_id = ?, max_score = ? WHERE id = ? AND teacher_id = ?");
                $stmt->execute([$title, $description, $due_date, $course_id, $max_score, $task_id, $teacher_id]);
                $message = 'Tugas berhasil diperbarui!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Gagal memperbarui tugas: ' . $e->getMessage();
                $message_type = 'error';
            }
        }

        // Handle file uploads (for new task or editing existing task)
        if ($task_id && isset($_FILES['task_file']) && $_FILES['task_file']['error'] === UPLOAD_ERR_OK) {
            $file_name = basename($_FILES['task_file']['name']);
            $target_dir = "uploads/tasks/"; // Folder penyimpanan lama
            $new_target_dir = "C:/xampp/htdocs/smpm29/public/user/uploads/tasks/"; // Folder penyimpanan baru
            
            // Buat folder jika belum ada (untuk kedua lokasi)
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            if (!is_dir($new_target_dir)) {
                mkdir($new_target_dir, 0777, true);
            }
            
            $unique_id = uniqid();
            $target_file = $target_dir . $unique_id . '_' . $file_name; // File path lama
            $new_target_file = $new_target_dir . $unique_id . '_' . $file_name; // File path baru

            if (move_uploaded_file($_FILES['task_file']['tmp_name'], $target_file)) {
                // Salin file ke lokasi baru
                if (copy($target_file, $new_target_file)) {
                    try {
                        // Check if there's an existing file for this task
                        $stmt_check_file = $pdo->prepare("SELECT id FROM task_files WHERE task_id = ?");
                        $stmt_check_file->execute([$task_id]);
                        if ($stmt_check_file->fetch()) {
                            // Update existing file entry
                            $stmt_update_file = $pdo->prepare("UPDATE task_files SET file_path = ?, file_name = ?, uploaded_at = NOW() WHERE task_id = ?");
                            $stmt_update_file->execute([$target_file, $file_name, $task_id]);
                        } else {
                            // Insert new file entry
                            $stmt_insert_file = $pdo->prepare("INSERT INTO task_files (task_id, file_path, file_name, uploaded_at) VALUES (?, ?, ?, NOW())");
                            $stmt_insert_file->execute([$task_id, $target_file, $file_name]);
                        }
                        $message .= ' File tugas berhasil diunggah/diperbarui.';
                    } catch (PDOException $e) {
                        $message .= ' Gagal menyimpan info file ke database: ' . $e->getMessage();
                        $message_type = 'error';
                    }
                } else {
                    $message .= ' Gagal menyalin file ke lokasi baru.';
                    $message_type = 'error';
                }
            } else {
                $message .= ' Gagal mengunggah file tugas.';
                $message_type = 'error';
            }
        }
    }
}

// --- LOGIKA UNTUK MEMBERI NILAI TUGAS SISWA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'grade_submission') {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = htmlspecialchars($_POST['feedback'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE task_submissions SET grade = ?, feedback = ?, graded_at = NOW() WHERE id = ?");
        $stmt->execute([$grade, $feedback, $submission_id]);
        $message = 'Nilai berhasil disimpan!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menyimpan nilai: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// --- LOGIKA UNTUK MENGHAPUS TUGAS ---
if (isset($_GET['delete_task_id'])) {
    $delete_task_id = $_GET['delete_task_id'];
    try {
        // Hapus file terkait dari server (opsional, tergantung kebutuhan)
        $stmt_file = $pdo->prepare("SELECT file_path FROM task_files WHERE task_id = ?");
        $stmt_file->execute([$delete_task_id]);
        $file_to_delete = $stmt_file->fetch(PDO::FETCH_ASSOC);
        if ($file_to_delete && file_exists($file_to_delete['file_path'])) {
            unlink($file_to_delete['file_path']);
            // Hapus juga file di lokasi baru
            $new_file_path = str_replace('uploads/tasks/', 'C:/xampp/htdocs/smpm29/public/user/uploads/tasks/', $file_to_delete['file_path']);
            if (file_exists($new_file_path)) {
                unlink($new_file_path);
            }
        }

        // Hapus entri file dari database
        $stmt_delete_files = $pdo->prepare("DELETE FROM task_files WHERE task_id = ?");
        $stmt_delete_files->execute([$delete_task_id]);

        // Hapus semua submission terkait
        $stmt_delete_submissions = $pdo->prepare("DELETE FROM task_submissions WHERE task_id = ?");
        $stmt_delete_submissions->execute([$delete_task_id]);

        // Hapus tugas utama
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$delete_task_id, $teacher_id]);
        $message = 'Tugas berhasil dihapus!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus tugas: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// --- AMBIL DATA TUGAS UNTUK DITAMPILKAN ---
$tasks = [];
try {
    // Ambil tugas yang dibuat oleh guru yang sedang login
    $stmt = $pdo->prepare("SELECT t.*, c.name AS course_name FROM tasks t JOIN courses c ON t.course_id = c.id WHERE t.teacher_id = ? ORDER BY t.created_at DESC");
    $stmt->execute([$teacher_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Gagal mengambil daftar tugas: ' . $e->getMessage();
    $message_type = 'error';
}

// Ambil daftar course untuk dropdown
$courses = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM courses ORDER BY name");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error, maybe log it
}

// Untuk mode edit tugas
$edit_task = null;
$edit_task_file = null;
if (isset($_GET['edit_task_id'])) {
    $edit_task_id = $_GET['edit_task_id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$edit_task_id, $teacher_id]);
        $edit_task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($edit_task) {
            $stmt_file = $pdo->prepare("SELECT * FROM task_files WHERE task_id = ?");
            $stmt_file->execute([$edit_task_id]);
            $edit_task_file = $stmt_file->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $message = 'Gagal mengambil data tugas untuk diedit: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Untuk melihat detail tugas dan submisi
$selected_task = null;
$submissions = [];
if (isset($_GET['view_task_id'])) {
    $view_task_id = $_GET['view_task_id'];
    try {
        $stmt = $pdo->prepare("SELECT t.*, tf.file_path, tf.file_name FROM tasks t LEFT JOIN task_files tf ON t.id = tf.task_id WHERE t.id = ? AND t.teacher_id = ?");
        $stmt->execute([$view_task_id, $teacher_id]);
        $selected_task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($selected_task) {
            $stmt_submissions = $pdo->prepare("SELECT ts.*, u.full_name AS student_name, u.id AS student_id, 
                sf.file_path AS submission_file_path, sf.file_name AS submission_file_name
                FROM task_submissions ts
                JOIN users u ON ts.student_id = u.id
                LEFT JOIN submission_files sf ON ts.id = sf.submission_id
                WHERE ts.task_id = ? ORDER BY ts.submitted_at DESC");
            $stmt_submissions->execute([$view_task_id]);
            $submissions = $stmt_submissions->fetchAll(PDO::FETCH_ASSOC);

            // Fix file paths for student submissions
            foreach ($submissions as &$submission) {
                if ($submission['submission_file_path']) {
                    // Convert path to use forward slashes and ensure it's accessible via web
                    $submission['submission_file_path'] = str_replace('\\', '/', $submission['submission_file_path']);
                    // Remove any dangerous path traversals
                    $submission['submission_file_path'] = preg_replace('/\.\.\//', '', $submission['submission_file_path']);
                }
            }
            unset($submission); // Break the reference
        }
    } catch (PDOException $e) {
        $message = 'Gagal mengambil detail tugas atau submisi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Set active tab based on GET parameters
$active_tab = 'create';
if (isset($_GET['edit_task_id'])) {
    $active_tab = 'create'; // Still use the create/edit form
} elseif (isset($_GET['view_task_id'])) {
    $active_tab = 'view_submissions';
} else {
    $active_tab = 'create'; // Default tab
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat & Kelola Tugas - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .sidebar { transition: all 0.3s ease; }
        .sidebar-item { transition: all 0.2s ease; }
        .sidebar-item:hover { background-color: #f1f5f9; border-left: 3px solid #3b82f6; }
        .sidebar-item.active { background-color: #e0e7ff; color: #3b82f6; font-weight: 500; border-left: 3px solid #3b82f6; }
        .task-card { transition: all 0.3s ease; border-left: 4px solid transparent; }
        .task-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .task-card.active { border-left-color: #3b82f6; background-color: #f8fafc; }
        .datetime-picker { position: relative; }
        .datetime-picker input[type="datetime-local"]::-webkit-calendar-picker-indicator { position: absolute; right: 0; padding: 1.5rem; opacity: 0; }
        .submission-card { border-left: 4px solid #3b82f6; }
        .submission-card.late { border-left-color: #ef4444; }
        .submission-card.graded { border-left-color: #10b981; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .grade-input { width: 70px; }
        .file-preview-container { max-height: 500px; overflow-y: auto; }
        .file-preview { border: 1px solid #e2e8f0; border-radius: 0.375rem; margin-bottom: 1rem; }
        .file-preview img, .file-preview iframe { max-width: 100%; }
        .preview-container { background-color: #f8fafc; padding: 1rem; border-radius: 0.375rem; }
        .attachment-badge { 
            display: inline-flex; 
            align-items: center; 
            padding: 0.25rem 0.5rem; 
            background-color: #f3f4f6; 
            border-radius: 0.375rem; 
            font-size: 0.75rem; 
            color: #4b5563; 
            margin-right: 0.5rem; 
            margin-bottom: 0.5rem; 
        }
        .file-preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .file-preview-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            position: relative;
        }
        .file-preview-container {
            width: 100%;
            height: 100%;
        }
        .file-preview-container img,
        .file-preview-container iframe {
            max-width: 100%;
            max-height: 80vh;
        }
    </style>
</head>
<body>
    <div class="flex">
        <div class="w-64 h-screen bg-white shadow-lg fixed left-0 sidebar">
            <div class="flex items-center justify-center p-4 border-b">
                <span class="text-xl font-bold text-gray-800">E-Learning Class</span>
            </div>
            <div class="p-4 overflow-y-auto" style="height: calc(100vh - 64px);">
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
                <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 text-gray-700">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i><span>Dashboard</span>
                </a>
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">MANAJEMEN</p>
                <a href="profil.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 text-gray-700">
                    <i class="fas fa-user w-5 mr-3"></i><span>Profil</span>
                </a>
                <a href="tambah_anggota.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 text-gray-700">
                    <i class="fas fa-user-plus w-5 mr-3"></i><span>Tambah Anggota</span>
                </a>
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 text-gray-700">
                    <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
                </a>
                <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 text-gray-700">
                    <i class="fas fa-book-open w-5 mr-3"></i><span>Tambah Materi</span>
                </a>
                <a href="buat_kuis.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 text-gray-700">
                    <i class="fas fa-question-circle w-5 mr-3"></i><span>Buat Kuis</span>
                </a>
                <a href="buat_tugas.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-tasks w-5 mr-3"></i><span>Buat Tugas</span>
                </a>
                <div class="border-t mt-4 pt-2">
                    <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i><span>Keluar</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-1 ml-64 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Tugas</h1>

            <?php if ($message): ?>
                <div class="p-4 mb-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <div class="flex border-b border-gray-200">
                    <button class="px-4 py-2 text-sm font-medium focus:outline-none <?php echo $active_tab === 'create' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'; ?>" onclick="openTab(event, 'create-task')">
                        <?php echo $edit_task ? 'Edit Tugas' : 'Buat Tugas Baru'; ?>
                    </button>
                    <button class="px-4 py-2 text-sm font-medium focus:outline-none <?php echo $active_tab === 'list-tasks' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'; ?>" onclick="openTab(event, 'list-tasks')">
                        Daftar Tugas
                    </button>
                    <button class="px-4 py-2 text-sm font-medium focus:outline-none <?php echo $active_tab === 'view_submissions' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'; ?>" onclick="openTab(event, 'view-submissions')">
                        Lihat Submisi
                    </button>
                </div>

                <div id="create-task" class="tab-content pt-4 <?php echo $active_tab === 'create' ? 'active' : ''; ?>">
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4"><?php echo $edit_task ? 'Edit Tugas' : 'Buat Tugas Baru'; ?></h2>
                    <form action="buat_tugas.php" method="POST" enctype="multipart/form-data">
                        <?php if ($edit_task): ?>
                            <input type="hidden" name="action" value="edit_task">
                            <input type="hidden" name="task_id" value="<?php echo $edit_task['id']; ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="create_task">
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="course_id" class="block text-gray-700 text-sm font-bold mb-2">Pilih Mata Pelajaran:</label>
                            <select id="course_id" name="course_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo ($edit_task && $edit_task['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                        <?php echo $course['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas:</label>
                            <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $edit_task ? htmlspecialchars($edit_task['title']) : ''; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Tugas:</label>
                            <textarea id="description" name="description" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo $edit_task ? htmlspecialchars($edit_task['description']) : ''; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Batas Akhir Pengumpulan:</label>
                            <input type="datetime-local" id="due_date" name="due_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $edit_task ? date('Y-m-d\TH:i', strtotime($edit_task['due_date'])) : ''; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="max_score" class="block text-gray-700 text-sm font-bold mb-2">Nilai Maksimal:</label>
                            <input type="number" id="max_score" name="max_score" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $edit_task ? htmlspecialchars($edit_task['max_score']) : ''; ?>" min="0" required>
                        </div>

                        <div class="mb-4">
                            <label for="task_file" class="block text-gray-700 text-sm font-bold mb-2">Unggah File Tugas (Opsional):</label>
                            <input type="file" id="task_file" name="task_file" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if ($edit_task_file): ?>
                                <p class="text-sm text-gray-600 mt-2">File saat ini: <a href="#" onclick="openFilePreviewModal('<?php echo htmlspecialchars($edit_task_file['file_path']); ?>', '<?php echo htmlspecialchars($edit_task_file['file_name']); ?>')" class="text-blue-500 hover:underline"><?php echo htmlspecialchars($edit_task_file['file_name']); ?></a> (<a href="download.php?file=<?php echo urlencode($edit_task_file['file_path']); ?>" class="text-green-500 hover:underline">Download</a>)</p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <?php echo $edit_task ? 'Perbarui Tugas' : 'Buat Tugas'; ?>
                        </button>
                        <?php if ($edit_task): ?>
                            <a href="buat_tugas.php" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div id="list-tasks" class="tab-content pt-4 <?php echo $active_tab === 'list-tasks' ? 'active' : ''; ?>">
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Daftar Tugas Anda</h2>
                    <?php if (empty($tasks)): ?>
                        <p class="text-gray-600">Belum ada tugas yang dibuat.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($tasks as $task): ?>
                                <div class="task-card bg-gray-50 p-4 rounded-lg shadow-sm flex flex-col justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($task['title']); ?></h3>
                                        <p class="text-sm text-gray-600">Mata Pelajaran: <?php echo htmlspecialchars($task['course_name']); ?></p>
                                        <p class="text-sm text-gray-600">Batas Akhir: <?php echo date('d M Y H:i', strtotime($task['due_date'])); ?></p>
                                        <p class="text-sm text-gray-600">Nilai Maksimal: <?php echo htmlspecialchars($task['max_score']); ?></p>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="buat_tugas.php?edit_task_id=<?php echo $task['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1 rounded flex items-center">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                        <a href="buat_tugas.php?view_task_id=<?php echo $task['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded flex items-center">
                                            <i class="fas fa-eye mr-1"></i> Lihat Submisi
                                        </a>
                                        <a href="buat_tugas.php?delete_task_id=<?php echo $task['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini? Semua submisi terkait juga akan terhapus.');" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded flex items-center">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </a>
                                        <?php
                                            // Cek apakah ada file tugas
                                            $stmt_check_file = $pdo->prepare("SELECT file_path, file_name FROM task_files WHERE task_id = ?");
                                            $stmt_check_file->execute([$task['id']]);
                                            $task_file = $stmt_check_file->fetch(PDO::FETCH_ASSOC);
                                            if ($task_file):
                                        ?>
                                            <a href="#" onclick="openFilePreviewModal('<?php echo htmlspecialchars($task_file['file_path']); ?>', '<?php echo htmlspecialchars($task_file['file_name']); ?>')" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded flex items-center">
                                                <i class="fas fa-file-alt mr-1"></i> Lihat File
                                            </a>
                                            <a href="download.php?file=<?php echo urlencode($task_file['file_path']); ?>" class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded flex items-center">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="view-submissions" class="tab-content pt-4 <?php echo $active_tab === 'view_submissions' ? 'active' : ''; ?>">
                    <?php if ($selected_task): ?>
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Submisi untuk Tugas: "<?php echo htmlspecialchars($selected_task['title']); ?>"</h2>

                        <div class="bg-gray-100 p-4 rounded-lg mb-6">
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Detail Tugas</h3>
                            <p class="text-gray-600"><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($selected_task['description'])); ?></p>
                            <p class="text-gray-600"><strong>Batas Akhir:</strong> <?php echo date('d M Y H:i', strtotime($selected_task['due_date'])); ?></p>
                            <p class="text-gray-600"><strong>Nilai Maksimal:</strong> <?php echo htmlspecialchars($selected_task['max_score']); ?></p>
                            <?php if ($selected_task['file_path']): ?>
                                <p class="text-gray-600"><strong>File Tugas:</strong> 
                                    <span class="attachment-badge cursor-pointer" onclick="openFilePreviewModal('<?php echo htmlspecialchars($selected_task['file_path']); ?>', '<?php echo htmlspecialchars($selected_task['file_name']); ?>')">
                                        <i class="fas fa-paperclip mr-1"></i>
                                        <?php echo htmlspecialchars($selected_task['file_name']); ?>
                                    </span>
                                    <a href="download.php?file=<?php echo urlencode($selected_task['file_path']); ?>" class="ml-2 text-blue-500 hover:underline"><i class="fas fa-download mr-1"></i>Download</a>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($submissions)): ?>
                            <p class="text-gray-600">Belum ada submisi untuk tugas ini.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($submissions as $submission):
                                    $is_late = strtotime($submission['submitted_at']) > strtotime($selected_task['due_date']);
                                    $is_graded = !is_null($submission['grade']);
                                ?>
                                    <div class="submission-card bg-white p-4 rounded-lg shadow-sm <?php echo $is_late ? 'late' : ''; ?> <?php echo $is_graded ? 'graded' : ''; ?>">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="text-md font-semibold text-gray-800"><?php echo htmlspecialchars($submission['student_name']); ?> (ID: <?php echo htmlspecialchars($submission['student_id']); ?>)</h4>
                                            <span class="text-sm text-gray-500">
                                                Dikumpulkan: <?php echo date('d M Y H:i', strtotime($submission['submitted_at'])); ?>
                                                <?php if ($is_late): ?>
                                                    <span class="text-red-500 ml-2">(Terlambat)</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-700 mb-2"><strong>Catatan Siswa:</strong> <?php echo nl2br(htmlspecialchars($submission['notes'])); ?></p>

                                        <?php if ($submission['submission_file_path']): ?>
                                            <div class="mb-2">
                                                <p class="text-gray-700"><strong>File Submisi:</strong> 
                                                    <span class="attachment-badge cursor-pointer" onclick="openFilePreviewModal('<?php echo htmlspecialchars($submission['submission_file_path']); ?>', '<?php echo htmlspecialchars($submission['submission_file_name']); ?>')">
                                                        <i class="fas fa-paperclip mr-1"></i>
                                                        <?php echo htmlspecialchars($submission['submission_file_name']); ?>
                                                    </span>
                                                    <a href="download.php?file=<?php echo urlencode($submission['submission_file_path']); ?>" class="ml-2 text-blue-500 hover:underline"><i class="fas fa-download mr-1"></i>Download</a>
                                                </p>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-gray-600">Tidak ada file yang dilampirkan.</p>
                                        <?php endif; ?>

                                        <form action="buat_tugas.php?view_task_id=<?php echo $selected_task['id']; ?>" method="POST" class="mt-3">
                                            <input type="hidden" name="action" value="grade_submission">
                                            <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                            <div class="flex items-center space-x-2">
                                                <label for="grade_<?php echo $submission['id']; ?>" class="text-gray-700 text-sm">Nilai:</label>
                                                <input type="number" id="grade_<?php echo $submission['id']; ?>" name="grade" class="grade-input shadow appearance-none border rounded py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" min="0" max="<?php echo $selected_task['max_score']; ?>" value="<?php echo $submission['grade'] ?? ''; ?>" required>
                                                <span class="text-gray-600">/ <?php echo $selected_task['max_score']; ?></span>
                                            </div>
                                            <div class="mt-2">
                                                <label for="feedback_<?php echo $submission['id']; ?>" class="block text-gray-700 text-sm mb-1">Umpan Balik (Opsional):</label>
                                                <textarea id="feedback_<?php echo $submission['id']; ?>" name="feedback" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>
                                            </div>
                                            <button type="submit" class="mt-3 bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded focus:outline-none focus:shadow-outline">
                                                Simpan Nilai
                                            </button>
                                            <?php if ($is_graded): ?>
                                                <span class="ml-2 text-green-700 text-sm">Sudah Dinilai</span>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-600">Pilih tugas dari "Daftar Tugas" untuk melihat submisi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="filePreviewModal" class="file-preview-modal">
        <div class="file-preview-content w-full max-w-2xl lg:max-w-3xl xl:max-w-4xl p-6">
            <button class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 text-2xl" onclick="closeFilePreviewModal()">&times;</button>
            <h3 class="text-xl font-bold mb-4" id="previewFileName"></h3>
            <div class="file-preview-container bg-gray-100 p-4 rounded-md flex items-center justify-center">
                <div id="filePreviewContent" class="preview-container">
                    <!-- File preview will be inserted here -->
                </div>
            </div>
            <a id="downloadFileLink" href="#" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" download><i class="fas fa-download mr-2"></i>Download File</a>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("px-4 py-2");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("text-blue-600", "border-b-2", "border-blue-600");
                tablinks[i].classList.add("text-gray-500", "hover:text-gray-700");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.remove("text-gray-500", "hover:text-gray-700");
            evt.currentTarget.classList.add("text-blue-600", "border-b-2", "border-blue-600");

            // Update URL hash to maintain tab state on refresh
            if (tabName === 'create-task') {
                history.pushState(null, '', 'buat_tugas.php');
            } else if (tabName === 'list-tasks') {
                history.pushState(null, '', 'buat_tugas.php?tab=list');
            } else if (tabName === 'view-submissions') {
                history.pushState(null, '', 'buat_tugas.php?tab=submissions');
            }
        }

        // Logic to activate tab based on URL parameters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('edit_task_id')) {
                document.querySelector('[onclick="openTab(event, \'create-task\')"]').click();
            } else if (urlParams.has('view_task_id')) {
                document.querySelector('[onclick="openTab(event, \'view-submissions\')"]').click();
            } else if (urlParams.get('tab') === 'list') {
                document.querySelector('[onclick="openTab(event, \'list-tasks\')"]').click();
            } else if (urlParams.get('tab') === 'submissions') {
                document.querySelector('[onclick="openTab(event, \'view-submissions\')"]').click();
            } else {
                // Default to 'create-task' tab if no specific parameter
                document.querySelector('[onclick="openTab(event, \'create-task\')"]').click();
            }
        });

        // File Preview Modal Logic
        const filePreviewModal = document.getElementById('filePreviewModal');
        const previewFileName = document.getElementById('previewFileName');
        const filePreviewContent = document.getElementById('filePreviewContent');
        const downloadFileLink = document.getElementById('downloadFileLink');

        function openFilePreviewModal(filePath, fileName) {
            previewFileName.textContent = fileName;
            downloadFileLink.href = 'download.php?file=' + encodeURIComponent(filePath);

            filePreviewContent.innerHTML = ''; // Clear previous content
            const fileExtension = fileName.split('.').pop().toLowerCase();

            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            const pdfExtensions = ['pdf'];
            const textExtensions = ['txt', 'html', 'css', 'js', 'php', 'json', 'xml', 'csv'];
            const officeExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

            if (imageExtensions.includes(fileExtension)) {
                const img = document.createElement('img');
                img.src = filePath;
                img.alt = fileName;
                img.classList.add('max-w-full', 'h-auto', 'mx-auto');
                filePreviewContent.appendChild(img);
            } else if (pdfExtensions.includes(fileExtension)) {
                const iframe = document.createElement('iframe');
                iframe.src = filePath;
                iframe.style.width = '100%';
                iframe.style.height = '500px';
                iframe.setAttribute('frameborder', '0');
                filePreviewContent.appendChild(iframe);
            } else if (textExtensions.includes(fileExtension)) {
                fetch(filePath)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(text => {
                        const pre = document.createElement('pre');
                        pre.classList.add('whitespace-pre-wrap', 'break-words', 'p-4', 'bg-gray-50', 'rounded-md', 'text-sm', 'max-h-96', 'overflow-auto');
                        pre.textContent = text;
                        filePreviewContent.appendChild(pre);
                    })
                    .catch(error => {
                        filePreviewContent.innerHTML = `<p class="text-red-500">Gagal memuat pratinjau file teks. (${error.message})</p>`;
                        console.error('Error fetching file content:', error);
                    });
            } else if (officeExtensions.includes(fileExtension)) {
                // For Office files, we'll show a message and download link
                filePreviewContent.innerHTML = `
                    <p class="text-gray-700 mb-4">Tidak ada pratinjau yang tersedia untuk file Office.</p>
                    <p class="text-gray-600">Silakan download file untuk melihat isinya.</p>
                `;
            } else {
                filePreviewContent.innerHTML = `<p class="text-gray-700">Tidak ada pratinjau yang tersedia untuk tipe file ini.</p>`;
            }
            filePreviewModal.style.display = 'flex';
        }

        function closeFilePreviewModal() {
            filePreviewModal.style.display = 'none';
            filePreviewContent.innerHTML = ''; // Clear content when closing
        }

        window.onclick = function(event) {
            if (event.target == filePreviewModal) {
                closeFilePreviewModal();
            }
        }
    </script>
</body>
</html>