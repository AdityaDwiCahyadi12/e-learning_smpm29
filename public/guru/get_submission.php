<?php
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID pengumpulan tidak valid']);
    exit();
}

$submission_id = $_GET['id'];

try {
    // Get submission data
    $query = "SELECT ts.*, t.title as task_title, t.due_date, t.max_score, 
              u.fullname as student_name, u.profile_picture, c.course_name
              FROM task_submissions ts
              JOIN tasks t ON ts.task_id = t.id
              JOIN users u ON ts.student_id = u.id
              JOIN courses c ON t.course_id = c.id
              WHERE ts.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
    
    if (!$submission) {
        echo json_encode(['success' => false, 'message' => 'Pengumpulan tidak ditemukan']);
        exit();
    }
    
    // Get submission files
    $query = "SELECT * FROM submission_files WHERE submission_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'submission' => $submission,
        'files' => $files
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>