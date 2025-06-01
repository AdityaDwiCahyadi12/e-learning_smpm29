<?php
session_start();

// Konfigurasi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Simpan data tugas ke database
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, instructions, start_time, deadline, max_points, allow_late, category, created_at) 
                               VALUES (:title, :description, :instructions, :start_time, :deadline, :max_points, :allow_late, :category, NOW())");
        
        $stmt->bindParam(':title', $_POST['task_title']);
        $stmt->bindParam(':description', $_POST['task_description']);
        $stmt->bindParam(':instructions', $_POST['task_instruction']);
        $stmt->bindParam(':start_time', $_POST['task_start']);
        $stmt->bindParam(':deadline', $_POST['task_deadline']);
        $stmt->bindParam(':max_points', $_POST['task_points']);
        $stmt->bindParam(':allow_late', $_POST['allow_late_submission']);
        $stmt->bindParam(':category', $_POST['task_category']);

        $stmt->execute();
        $taskId = $pdo->lastInsertId();

        // Proses file lampiran jika ada
        if (isset($_FILES['task_attachment']) && $_FILES['task_attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['task_attachment']['name']);
            $fileName = uniqid() . '_' . $originalName;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['task_attachment']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("INSERT INTO task_attachments (task_id, file_name, file_path, uploaded_at) 
                                       VALUES (:task_id, :file_name, :file_path, NOW())");
                $stmt->bindParam(':task_id', $taskId);
                $stmt->bindParam(':file_name', $originalName);
                $stmt->bindParam(':file_path', $targetPath);
                $stmt->execute();
            }
        }

        $pdo->commit();
        header('Location: buat_tugas.php?success=1');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
