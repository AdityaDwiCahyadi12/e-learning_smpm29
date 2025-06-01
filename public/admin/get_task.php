<?php
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tugas tidak valid']);
    exit();
}

$task_id = $_GET['id'];

try {
    // Get task data
    $query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    
    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'Tugas tidak ditemukan']);
        exit();
    }
    
    // Get task attachments
    $query = "SELECT * FROM task_attachments WHERE task_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $attachments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'task' => $task,
        'attachments' => $attachments
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>