<?php
session_start();

$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$userId = $_SESSION['user']['id'] ?? null;

if (!$userId || empty($_POST['judul']) || empty($_POST['konten'])) {
    header("Location: forum_diskusi.php?error=invalid_input");
    exit;
}

$judul = $_POST['judul'];
$konten = $_POST['konten'];
$filePath = null;
$fileName = null;
$fileType = null;

// Perbaikan di sini: Tambahkan tanda kurung tutup pada if isset + pengecekan file error dengan benar
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadDir = 'uploads/forum/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES['attachment'];

    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            header("Location: forum_diskusi.php?error=file_too_large");
            exit;
        }
        header("Location: forum_diskusi.php?error=upload_error");
        exit;
    }

    $fileName = basename($file['name']);
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'ppt', 'pptx'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($fileExt, $allowedTypes)) {
        header("Location: forum_diskusi.php?error=invalid_file_type");
        exit;
    }

    if ($fileSize > $maxSize) {
        header("Location: forum_diskusi.php?error=file_too_large");
        exit;
    }

    $newFileName = 'forum_' . $userId . '_' . time() . '.' . $fileExt;
    $filePath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $filePath)) {
        header("Location: forum_diskusi.php?error=upload_failed");
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO discussions (user_id, title, content, created_at, file_path, file_name, file_type) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
    $stmt->execute([$userId, $judul, $konten, $filePath, $fileName, $fileType]);

    header("Location: forum_diskusi.php?success=diskusi_dibuat");
    exit;
} catch (PDOException $e) {
    if ($filePath && file_exists($filePath)) {
        unlink($filePath);
    }
    error_log("Database error: " . $e->getMessage());
    header("Location: forum_diskusi.php?error=database_error");
    exit;
}
