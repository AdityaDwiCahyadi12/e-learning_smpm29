<?php
// --- Konfigurasi Koneksi Database ---
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Set default fetch mode to associative array
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

session_start();

// Initialize variables
$message = '';
$message_type = '';
$student_id = $_SESSION['user']['id'] ?? null;
$userData = [];

// Ambil data user
if ($student_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$student_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set default photo path if not exists
        $userData['photo_path'] = $userData['photo_path'] ?? 'assets/default_avatar.jpg';
        if (!empty($userData['photo_path']) && !file_exists($userData['photo_path'])) {
            $userData['photo_path'] = 'assets/default_avatar.jpg';
        }
    } catch (PDOException $e) {
        die("Error mengambil data user: " . $e->getMessage());
    }
}

// Check if user photo exists - FIXED: Changed $userId to $student_id
$photoPath = file_exists("uploads/user_$student_id.jpg") ? "uploads/user_$student_id.jpg" : "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User') . "&background=3b82f6&color=fff";

// --- LOGIKA UNTUK SUBMIT TUGAS OLEH SISWA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_task' && $student_id) {
    $task_id = $_POST['task_id'] ?? null;
    $notes = htmlspecialchars($_POST['notes'] ?? '');

    if ($task_id) {
        try {
            $pdo->beginTransaction();

            // Cek apakah siswa sudah pernah submit tugas ini sebelumnya
            $stmt_check_submission = $pdo->prepare("SELECT id FROM task_submissions WHERE task_id = ? AND student_id = ?");
            $stmt_check_submission->execute([$task_id, $student_id]);
            $existing_submission = $stmt_check_submission->fetch();

            if ($existing_submission) {
                // Update submission
                $submission_id = $existing_submission['id'];
                $stmt = $pdo->prepare("UPDATE task_submissions SET notes = ?, submitted_at = NOW(), grade = NULL, feedback = NULL, graded_at = NULL WHERE id = ?");
                $stmt->execute([$notes, $submission_id]);
                $message = 'Tugas berhasil diperbarui!';
            } else {
                // Insert new submission
                $stmt = $pdo->prepare("INSERT INTO task_submissions (task_id, student_id, notes, submitted_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$task_id, $student_id, $notes]);
                $submission_id = $pdo->lastInsertId();
                $message = 'Tugas berhasil dikirim!';
            }
            $message_type = 'success';

            // Handle file uploads for submission
            if ($submission_id && isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['submission_file']['name']);
                $file_type = $_FILES['submission_file']['type'];
                $file_size = $_FILES['submission_file']['size'];
                $target_dir = "uploads/submissions/";
                $guru_target_dir = "C:/xampp/htdocs/smpm29/public/guru/uploads/submissions/";
                
                // Buat folder jika belum ada
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (!is_dir($guru_target_dir)) {
                    mkdir($guru_target_dir, 0777, true);
                }
                
                $unique_id = uniqid();
                $target_file = $target_dir . $unique_id . '_' . $file_name;
                $guru_target_file = $guru_target_dir . $unique_id . '_' . $file_name;

                if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_file)) {
                    // Salin file ke lokasi folder guru
                    if (copy($target_file, $guru_target_file)) {
                        // Hapus file lama jika ada
                        $stmt_old_file = $pdo->prepare("SELECT file_path FROM submission_files WHERE submission_id = ?");
                        $stmt_old_file->execute([$submission_id]);
                        $old_file = $stmt_old_file->fetch();
                        if ($old_file && file_exists($old_file['file_path'])) {
                            unlink($old_file['file_path']);
                            // Hapus juga file di lokasi guru jika ada
                            $old_guru_file = str_replace('uploads/submissions/', 'C:/xampp/htdocs/smpm29/public/guru/uploads/submissions/', $old_file['file_path']);
                            if (file_exists($old_guru_file)) {
                                unlink($old_guru_file);
                            }
                        }

                        // Simpan ke tabel uploads
                        $stmt_upload = $pdo->prepare("INSERT INTO uploads (file_name, file_path, file_type, file_size, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt_upload->execute([$file_name, $target_file, $file_type, $file_size]);
                        $upload_id = $pdo->lastInsertId();

                        // Cek apakah ada entri file yang sudah ada untuk submisi ini
                        $stmt_check_file = $pdo->prepare("SELECT id FROM submission_files WHERE submission_id = ?");
                        $stmt_check_file->execute([$submission_id]);
                        if ($stmt_check_file->fetch()) {
                            // Update existing file entry
                            $stmt_update_file = $pdo->prepare("UPDATE submission_files SET file_path = ?, file_name = ?, uploaded_at = NOW() WHERE submission_id = ?");
                            $stmt_update_file->execute([$target_file, $file_name, $submission_id]);
                        } else {
                            // Insert new file entry
                            $stmt_insert_file = $pdo->prepare("INSERT INTO submission_files (submission_id, file_path, file_name, uploaded_at) VALUES (?, ?, ?, NOW())");
                            $stmt_insert_file->execute([$submission_id, $target_file, $file_name]);
                        }
                        $message .= ' File submisi berhasil diunggah/diperbarui.';
                    } else {
                        $message .= ' Gagal menyalin file ke folder guru.';
                        $message_type = 'error';
                    }
                } else {
                    $message .= ' Gagal mengunggah file submisi.';
                    $message_type = 'error';
                }
            }
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Gagal mengirim/memperbarui tugas: ' . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = 'ID Tugas tidak valid.';
        $message_type = 'error';
    }
}

// --- LOGIKA UNTUK MENGAMBIL DATA TUGAS UNTUK SISWA ---
$tasks = [];
if ($student_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                t.id AS task_id,
                t.title,
                t.description,
                t.due_date,
                t.max_score,
                c.name AS course_name,
                tf.file_path AS task_file_path,
                tf.file_name AS task_file_name,
                ts.id AS submission_id,
                ts.notes AS student_notes,
                ts.submitted_at,
                ts.grade,
                ts.feedback,
                sf.file_path AS submission_file_path,
                sf.file_name AS submission_file_name
            FROM
                tasks t
            JOIN
                courses c ON t.course_id = c.id
            LEFT JOIN
                task_files tf ON t.id = tf.task_id
            LEFT JOIN
                task_submissions ts ON t.id = ts.task_id AND ts.student_id = ?
            LEFT JOIN
                submission_files sf ON ts.id = sf.submission_id
            ORDER BY
                t.due_date ASC, t.created_at DESC
        ");
        $stmt->execute([$student_id]);
        $tasks = $stmt->fetchAll();
    } catch (PDOException $e) {
        $message = 'Gagal mengambil daftar tugas: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Untuk modal detail tugas dan submisi
$selected_task_detail = null;
if (isset($_GET['view_task_id']) && $student_id) {
    $view_task_id = $_GET['view_task_id'];
    try {
        $stmt_detail = $pdo->prepare("
            SELECT
                t.id AS task_id,
                t.title,
                t.description,
                t.due_date,
                t.max_score,
                c.name AS course_name,
                tf.file_path AS task_file_path,
                tf.file_name AS task_file_name,
                ts.id AS submission_id,
                ts.notes AS student_notes,
                ts.submitted_at,
                ts.grade,
                ts.feedback,
                sf.file_path AS submission_file_path,
                sf.file_name AS submission_file_name
            FROM
                tasks t
            JOIN
                courses c ON t.course_id = c.id
            LEFT JOIN
                task_files tf ON t.id = tf.task_id
            LEFT JOIN
                task_submissions ts ON t.id = ts.task_id AND ts.student_id = ?
            LEFT JOIN
                submission_files sf ON ts.id = sf.submission_id
            WHERE
                t.id = ?
        ");
        $stmt_detail->execute([$student_id, $view_task_id]);
        $selected_task_detail = $stmt_detail->fetch();

        if ($selected_task_detail) {
            echo "<script>document.addEventListener('DOMContentLoaded', function() { openTaskDetailModal(" . json_encode($selected_task_detail) . "); });</script>";
        }

    } catch (PDOException $e) {
        $message = 'Gagal mengambil detail tugas: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Daftar Tugas - E-Learning Class</title>
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
    .task-card {
        position: relative;
        min-height: 200px;
    }
    .submission-status {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        font-size: 0.875rem;
    }
    .submitted {
        color: #10b981;
    }
    .not-submitted {
        color: #ef4444;
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
    .submission-item {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    .submission-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
</style>
</head>
<body class="bg-gray-50">
<div class="flex min-h-screen">
    <div class="w-64 h-screen bg-white shadow-lg fixed left-0 sidebar">
        <div class="flex items-center justify-center p-4 border-b">
            <span class="text-xl font-bold text-gray-800">E-Learning Class</span>
        </div>
        <div class="p-4">
            <div class="flex items-center mb-6 p-3 bg-gray-100 rounded-lg">
                <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profil" class="w-10 h-10 rounded-full mr-3 object-cover" />
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User Name') ?></p>
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

            <a href="tugas.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
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

    <div class="flex-1 ml-64 p-8">
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Daftar Tugas Siswa</h1>
            <p class="text-sm text-gray-600">Pantau dan kelola semua tugas yang diberikan oleh guru</p>
        </div>


        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($tasks)): ?>
            <p class="text-gray-600">Belum ada tugas yang diberikan.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($tasks as $task):
                    $is_submitted = !empty($task['submission_id']);
                    $is_graded = !empty($task['grade']);
                    $is_late = $is_submitted && (strtotime($task['submitted_at']) > strtotime($task['due_date']));
                    $status_text = '';
                    $status_class = '';

                    if ($is_submitted) {
                        $status_text = 'Sudah Dikumpulkan';
                        $status_class = 'submitted';
                        if ($is_late) {
                            $status_text .= ' (Terlambat)';
                            $status_class = 'not-submitted';
                        }
                        if ($is_graded) {
                            $status_text .= ' & Dinilai';
                            $status_class = 'submitted';
                        }
                    } else {
                        $status_text = 'Belum Dikumpulkan';
                        $status_class = 'not-submitted';
                    }
                ?>
                    <div class="bg-white p-6 rounded-lg shadow-md task-card">
                        <h2 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($task['title']); ?></h2>
                        <p class="text-sm text-gray-600 mb-1">Mata Pelajaran: <?= htmlspecialchars($task['course_name']); ?></p>
                        <p class="text-sm text-gray-600 mb-1">Batas Akhir: <?= date('d M Y H:i', strtotime($task['due_date'])); ?></p>
                        <p class="text-sm text-gray-600 mb-4">Nilai Maksimal: <?= htmlspecialchars($task['max_score']); ?></p>

                        <div class="mb-4">
                            <span class="font-medium text-gray-700 text-sm block mb-1">File Tugas:</span>
                            <?php if (!empty($task['task_file_path'])): ?>
                                <span class="attachment-badge cursor-pointer" onclick="openFilePreviewModal('<?= htmlspecialchars($task['task_file_path']); ?>', '<?= htmlspecialchars($task['task_file_name']); ?>')">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    <?= htmlspecialchars($task['task_file_name']); ?>
                                </span>
                                <a href="download.php?file=<?= urlencode($task['task_file_path']); ?>" class="ml-2 text-blue-500 hover:underline text-xs"><i class="fas fa-download mr-1"></i>Download</a>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">Tidak ada file.</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end mt-4">
                            <a href="tugas.php?view_task_id=<?= $task['task_id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-sm">
                                <?= $is_submitted ? 'Lihat/Edit Submisi' : 'Kirim Tugas'; ?>
                            </a>
                        </div>
                        <div class="submission-status <?= $status_class; ?>">
                            <span class="font-medium"><?= $status_text; ?></span>
                            <?php if ($is_graded): ?>
                                <span class="ml-1 text-gray-800">Nilai: <?= htmlspecialchars($task['grade']); ?> / <?= htmlspecialchars($task['max_score']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="taskDetailModal" class="file-preview-modal">
        <div class="file-preview-content w-full max-w-2xl lg:max-w-3xl xl:max-w-4xl p-6">
            <button class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 text-2xl" onclick="closeTaskDetailModal()">&times;</button>
            <h3 class="text-2xl font-bold text-gray-800 mb-4" id="taskDetailTitle"></h3>
            <p class="text-sm text-gray-600 mb-1" id="taskDetailCourse"></p>
            <p class="text-sm text-gray-600 mb-1" id="taskDetailDueDate"></p>
            <p class="text-sm text-gray-600 mb-4" id="taskDetailMaxScore"></p>
            <p class="text-gray-700 mb-4" id="taskDetailDescription"></p>

            <div class="mb-4">
                <span class="font-medium text-gray-700 text-sm block mb-1">File Tugas dari Guru:</span>
                <div id="taskDetailTeacherFile"></div>
            </div>

            <hr class="my-6">

            <h4 class="text-xl font-semibold text-gray-800 mb-4">Pengumpulan Tugas Anda</h4>
            <form id="submissionForm" action="tugas.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="submit_task">
                <input type="hidden" name="task_id" id="submissionTaskId">

                <div class="mb-4">
                    <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Catatan (Opsional):</label>
                    <textarea id="notes" name="notes" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="mb-4">
                    <label for="submission_file" class="block text-gray-700 text-sm font-bold mb-2">Unggah File Submisi (Opsional):</label>
                    <input type="file" id="submission_file" name="submission_file" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <div id="currentSubmissionFile" class="text-sm text-gray-600 mt-2"></div>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Kirim / Perbarui Tugas
                </button>
            </form>

            <div id="submissionStatusSection" class="mt-6 p-4 rounded-lg border">
                <h4 class="text-lg font-semibold text-gray-700 mb-2">Status Submisi Anda</h4>
                <div id="submissionInfo">
                    <p class="text-gray-700">Status: <span id="submissionStatusText" class="font-medium"></span></p>
                    <p class="text-gray-700" id="submittedAtInfo"></p>
                    <p class="text-gray-700" id="gradeInfo"></p>
                    <p class="text-gray-700" id="feedbackInfo"></p>
                    <div class="mt-2" id="studentSubmittedFile"></div>
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
                    </div>
            </div>
            <a id="downloadFileLink" href="#" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" download><i class="fas fa-download mr-2"></i>Download File</a>
        </div>
    </div>
</div>

<script>
    const taskDetailModal = document.getElementById('taskDetailModal');
    const filePreviewModal = document.getElementById('filePreviewModal');
    const filePreviewContent = document.getElementById('filePreviewContent');
    const previewFileName = document.getElementById('previewFileName');
    const downloadFileLink = document.getElementById('downloadFileLink');

    function openTaskDetailModal(task) {
        document.getElementById('taskDetailTitle').textContent = task.title;
        document.getElementById('taskDetailCourse').textContent = `Mata Pelajaran: ${task.course_name}`;
        document.getElementById('taskDetailDueDate').textContent = `Batas Akhir: ${new Date(task.due_date).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}`;
        document.getElementById('taskDetailMaxScore').textContent = `Nilai Maksimal: ${task.max_score}`;
        document.getElementById('taskDetailDescription').innerHTML = task.description.replace(/\n/g, '<br>');

        const teacherFileDiv = document.getElementById('taskDetailTeacherFile');
        teacherFileDiv.innerHTML = '';
        if (task.task_file_path) {
            teacherFileDiv.innerHTML = `
                <span class="attachment-badge cursor-pointer" onclick="openFilePreviewModal('${task.task_file_path}', '${task.task_file_name}')">
                    <i class="fas fa-paperclip mr-1"></i>
                    ${task.task_file_name}
                </span>
                <a href="download.php?file=${encodeURIComponent(task.task_file_path)}" class="ml-2 text-blue-500 hover:underline text-xs"><i class="fas fa-download mr-1"></i>Download</a>
            `;
        } else {
            teacherFileDiv.textContent = 'Tidak ada file.';
        }

        document.getElementById('submissionTaskId').value = task.task_id;
        document.getElementById('notes').value = task.student_notes || '';

        const currentSubmissionFileDiv = document.getElementById('currentSubmissionFile');
        currentSubmissionFileDiv.innerHTML = '';
        if (task.submission_file_path) {
            currentSubmissionFileDiv.innerHTML = `
                File saat ini:
                <span class="attachment-badge cursor-pointer" onclick="openFilePreviewModal('${task.submission_file_path}', '${task.submission_file_name}')">
                    <i class="fas fa-paperclip mr-1"></i>
                    ${task.submission_file_name}
                </span>
                <a href="download.php?file=${encodeURIComponent(task.submission_file_path)}" class="ml-2 text-blue-500 hover:underline text-xs"><i class="fas fa-download mr-1"></i>Download</a>
            `;
        }

        // Update submission status section
        const submissionStatusText = document.getElementById('submissionStatusText');
        const submittedAtInfo = document.getElementById('submittedAtInfo');
        const gradeInfo = document.getElementById('gradeInfo');
        const feedbackInfo = document.getElementById('feedbackInfo');
        const studentSubmittedFile = document.getElementById('studentSubmittedFile');

        if (task.submission_id) {
            let status = 'Sudah Dikumpulkan';
            const submittedDate = new Date(task.submitted_at);
            const dueDate = new Date(task.due_date);
            
            if (submittedDate > dueDate) {
                status += ' (Terlambat)';
                submissionStatusText.classList.add('text-red-500');
                submissionStatusText.classList.remove('text-green-500');
            } else {
                submissionStatusText.classList.add('text-green-500');
                submissionStatusText.classList.remove('text-red-500');
            }
            submissionStatusText.textContent = status;
            submittedAtInfo.textContent = `Dikumpulkan pada: ${submittedDate.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}`;

            if (task.grade !== null) {
                gradeInfo.textContent = `Nilai: ${task.grade} / ${task.max_score}`;
                gradeInfo.classList.add('font-bold');
            } else {
                gradeInfo.textContent = 'Nilai: Belum Dinilai';
                gradeInfo.classList.remove('font-bold');
            }
            feedbackInfo.textContent = `Umpan Balik: ${task.feedback || 'Belum ada umpan balik'}`;

            studentSubmittedFile.innerHTML = '';
            if (task.submission_file_path) {
                studentSubmittedFile.innerHTML = `
                    <span class="font-medium text-gray-700 text-sm block mb-1">File Submisi Anda:</span>
                    <span class="attachment-badge cursor-pointer" onclick="openFilePreviewModal('${task.submission_file_path}', '${task.submission_file_name}')">
                        <i class="fas fa-paperclip mr-1"></i>
                        ${task.submission_file_name}
                    </span>
                    <a href="download.php?file=${encodeURIComponent(task.submission_file_path)}" class="ml-2 text-blue-500 hover:underline text-xs"><i class="fas fa-download mr-1"></i>Download</a>
                `;
            } else {
                studentSubmittedFile.innerHTML = '<span class="text-sm text-gray-500">Tidak ada file submisi Anda.</span>';
            }

        } else {
            submissionStatusText.textContent = 'Belum Dikumpulkan';
            submissionStatusText.classList.add('text-red-500');
            submissionStatusText.classList.remove('text-green-500');
            submittedAtInfo.textContent = '';
            gradeInfo.textContent = '';
            feedbackInfo.textContent = '';
            studentSubmittedFile.innerHTML = '';
        }

        taskDetailModal.style.display = 'flex';
    }

    function closeTaskDetailModal() {
        taskDetailModal.style.display = 'none';
        const url = new URL(window.location.href);
        url.searchParams.delete('view_task_id');
        history.pushState({}, '', url.toString());
    }

    function openFilePreviewModal(filePath, fileName) {
        previewFileName.textContent = fileName;
        downloadFileLink.href = 'download.php?file=' + encodeURIComponent(filePath);

        filePreviewContent.innerHTML = '';
        const fileExtension = fileName.split('.').pop().toLowerCase();

        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        const pdfExtensions = ['pdf'];
        const textExtensions = ['txt', 'html', 'css', 'js', 'php', 'json', 'xml', 'csv'];

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
            iframe.style.height = '400px';
            iframe.setAttribute('frameborder', '0');
            filePreviewContent.appendChild(iframe);
        } else if (textExtensions.includes(fileExtension)) {
            fetch(filePath)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok.');
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
        } else {
            filePreviewContent.innerHTML = `<p class="text-gray-700">Tidak ada pratinjau yang tersedia untuk tipe file ini.</p>`;
        }
        filePreviewModal.style.display = 'flex';
    }

    function closeFilePreviewModal() {
        filePreviewModal.style.display = 'none';
        filePreviewContent.innerHTML = '';
    }

    window.onclick = function(event) {
        if (event.target == taskDetailModal) {
            closeTaskDetailModal();
        }
        if (event.target == filePreviewModal) {
            closeFilePreviewModal();
        }
    }
</script>
</body>
</html>