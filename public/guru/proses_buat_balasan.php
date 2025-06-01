<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_id = intval($_POST['reply_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if the reply belongs to the user or if user is admin
    $check_stmt = $mysqli->prepare("SELECT user_id, file_path FROM replies WHERE id = ?");
    $check_stmt->bind_param("i", $reply_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $_SESSION['error'] = "Balasan tidak ditemukan.";
        header("Location: forum_diskusi.php");
        exit();
    }
    
    $reply = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Only allow deletion if user is the owner or admin
    if ($reply['user_id'] == $user_id || $_SESSION['role'] === 'admin') {
        // Delete file if exists
        if (!empty($reply['file_path']) && file_exists($reply['file_path'])) {
            unlink($reply['file_path']);
        }
        
        // Delete the reply
        $delete_stmt = $mysqli->prepare("DELETE FROM replies WHERE id = ?");
        $delete_stmt->bind_param("i", $reply_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Balasan berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus balasan: " . $delete_stmt->error;
        }
        
        $delete_stmt->close();
    } else {
        $_SESSION['error'] = "Anda tidak memiliki izin untuk menghapus balasan ini.";
    }
    
    $mysqli->close();
    header("Location: forum_diskusi.php");
    exit();
} else {
    header("Location: forum_diskusi.php");
    exit();
}