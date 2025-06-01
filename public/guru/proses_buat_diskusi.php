<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'smpm29';

$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $mysqli->real_escape_string($_POST['judul']);
    $konten = $mysqli->real_escape_string($_POST['konten']);
    $user_id = $_SESSION['user_id'];
    
    // Handle file upload
    $file_path = '';
    $file_name = '';
    $file_type = '';
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/discussions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $fileType = $_FILES['attachment']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Sanitize file name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        
        // Check file size (max 2MB)
        if ($fileSize > 2097152) {
            $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 2MB.";
            header("Location: forum_diskusi.php");
            exit();
        }
        
        // Allow certain file formats
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'pdf');
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['error'] = "Format file tidak diizinkan. Hanya JPG, JPEG, PNG, atau PDF.";
            header("Location: forum_diskusi.php");
            exit();
        }
        
        $destPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $file_path = $destPath;
            $file_name = $fileName;
            $file_type = $fileType;
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat mengunggah file.";
            header("Location: forum_diskusi.php");
            exit();
        }
    }
    
    // Insert discussion into database
    $stmt = $mysqli->prepare("INSERT INTO discussions (user_id, title, content, file_path, file_name, file_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $judul, $konten, $file_path, $file_name, $file_type);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Diskusi berhasil diposting!";
    } else {
        $_SESSION['error'] = "Gagal memposting diskusi: " . $stmt->error;
    }
    
    $stmt->close();
    $mysqli->close();
    
    header("Location: forum_diskusi.php");
    exit();
} else {
    header("Location: forum_diskusi.php");
    exit();
}
?>