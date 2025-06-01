<?php
session_start();

// Database configuration
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Task ID is required.";
    header("Location: buat_tugas.php");
    exit();
}

$task_id = (int)$_GET['id'];

// Fetch task details
try {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :task_id");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        $_SESSION['error_message'] = "Task not found.";
        header("Location: buat_tugas.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: buat_tugas.php");
    exit();
}

// Handle form submission for updating task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_title'])) {
    try {
        $title = trim($_POST['task_title']);
        $description = trim($_POST['task_description']);
        $start_date = $_POST['task_start'];
        $deadline = $_POST['task_deadline'];
        $grading_type = $_POST['grading_type'];
        $max_score = (int)$_POST['task_max_score'];

        // Update task in database
        $stmt = $conn->prepare("UPDATE tasks SET title = :title, description = :description, start_date = :start_date, deadline = :deadline, grading_type = :grading_type, max_score = :max_score WHERE id = :task_id");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':grading_type', $grading_type);
        $stmt->bindParam(':max_score', $max_score, PDO::PARAM_INT);
        $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success_message'] = "Task updated successfully!";
        header("Location: buat_tugas.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - Kelas E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Form Edit Tugas</h1>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <?= $_SESSION['error_message'] ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <?= $_SESSION['success_message'] ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <form action="edit_tugas.php?id=<?= $task_id ?>" method="POST">
        <div class="mb-4">
            <label for="task-title" class="block text-sm font-semibold mb-2">Judul Tugas</label>
            <input type="text" id="task-title" name="task_title" placeholder="Masukkan judul tugas" value="<?= htmlspecialchars($task['title']) ?>" class="w-full p-3 border border-gray-300 rounded-lg" required>
        </div>

        <div class="mb-4">
            <label for="task-description" class="block text-sm font-semibold mb-2">Deskripsi Tugas</label>
            <textarea id="task-description" name="task_description" rows="5" placeholder="Tambahkan deskripsi detail tentang tugas ini" class="w-full p-3 border border-gray-300 rounded-lg" required><?= htmlspecialchars($task['description']) ?></textarea>
        </div>

        <div class="mb-4 flex justify-between">
            <div class="w-full mr-2">
                <label for="task-start" class="block text-sm font-semibold mb-2">Waktu Mulai</label>
                <input type="datetime-local" id="task-start" name="task_start" value="<?= date('Y-m-d\TH:i', strtotime($task['start_date'])) ?>" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            <div class="w-full ml-2">
                <label for="task-deadline" class="block text-sm font-semibold mb-2">Deadline</label>
                <input type="datetime-local" id="task-deadline" name="task_deadline" value="<?= date('Y-m-d\TH:i', strtotime($task['deadline'])) ?>" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="task-file" class="block text-sm font-semibold mb-2">Lampiran (Opsional)</label>
            <input type="file" id="task-file" name="task_file" class="w-full p-3 border border-gray-300 rounded-lg mb-2"/>
            <span class="text-gray-600 text-sm">Seret file ke sini atau klik untuk memilih</span>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg">Update Tugas</button>
        </div>
    </form>
</div>
</body>
</html>
