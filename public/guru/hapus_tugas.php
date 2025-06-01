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

// Check if task ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Task ID is required.";
    header("Location: buat_tugas.php");
    exit();
}

$task_id = (int)$_GET['id'];

// Delete task from database
try {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :task_id");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['success_message'] = "Task deleted successfully!";
    header("Location: buat_tugas.php");
    exit();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: buat_tugas.php");
    exit();
}
?>
