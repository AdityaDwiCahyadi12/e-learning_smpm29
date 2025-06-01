<?php
session_start();
$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$userId = $_SESSION['user']['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diskusiId = $_POST['diskusi_id'];
    $konten = $_POST['konten'];

    $filePath = null;
    $fileName = null;
    $fileType = null;

    // Proses upload file jika ada
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $fileTmpPath = $_FILES['file_lampiran']['tmp_name'];
        $fileName = basename($_FILES['file_lampiran']['name']);
        $fileType = mime_content_type($fileTmpPath);

        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/replies/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $filePath = $destPath;
            }
        }
    }

    // Simpan balasan ke database
    $stmt = $pdo->prepare("INSERT INTO replies (user_id, discussion_id, content, file_path, file_name, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $diskusiId, $konten, $filePath, $fileName, $fileType]);

    header("Location: forum_diskusi.php");
    exit;
}
?>
