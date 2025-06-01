<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if submission ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Submission ID is required.";
    header("Location: buat_tugas.php");
    exit();
}

$submission_id = (int)$_GET['id'];

// Fetch submission details
try {
    $stmt = $conn->prepare("SELECT s.*, t.title AS task_title FROM task_submissions s JOIN tasks t ON s.task_id = t.id WHERE s.id = :submission_id");
    $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT);
    $stmt->execute();
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        $_SESSION['error_message'] = "Submission not found.";
        header("Location: buat_tugas.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: buat_tugas.php");
    exit();
}

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade'])) {
    try {
        $grade = (int)$_POST['grade'];

        // Update grade in database
        $stmt = $conn->prepare("UPDATE task_submissions SET grade = :grade WHERE id = :submission_id");
        $stmt->bindParam(':grade', $grade, PDO::PARAM_INT);
        $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success_message'] = "Grade updated successfully!";
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
    <title>Beri Nilai - Kelas E-Learning</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body>
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Beri Nilai untuk <?= htmlspecialchars($submission['task_title']) ?></h1>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $_SESSION['error_message'] ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <button type="button" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </span>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $_SESSION['success_message'] ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <button type="button" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form action="beri_nilai.php?id=<?= $submission_id ?>" method="POST">
        <div class="mb-4">
            <label for="grade" class="block text-sm font-semibold mb-2">Nilai</label>
            <input type="number" id="grade" name="grade" min="0" max="100" class="w-24 p-3 border border-gray-300 rounded-lg" required>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg">Simpan Nilai</button>
        </div>
    </form>
</div>
</body>
</html>
