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
    $discussion_id = intval($_POST['discussion_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if the discussion belongs to the user or if user is admin
    $check_stmt = $mysqli->prepare("SELECT user_id FROM discussions WHERE id = ?");
    $check_stmt->bind_param("i", $discussion_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $_SESSION['error'] = "Diskusi tidak ditemukan.";
        header("Location: forum_diskusi.php");
        exit();
    }
    
    $discussion = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Only allow deletion if user is the owner or admin
    if ($discussion['user_id'] == $user_id || $_SESSION['role'] === 'admin') {
        // First delete all replies and their files
        $get_replies_stmt = $mysqli->prepare("SELECT id, file_path FROM replies WHERE discussion_id = ?");
        $get_replies_stmt->bind_param("i", $discussion_id);
        $get_replies_stmt->execute();
        $replies_result = $get_replies_stmt->get_result();
        
        while ($reply = $replies_result->fetch_assoc()) {
            if (!empty($reply['file_path']) && file_exists($reply['file_path'])) {
                unlink($reply['file_path']);
            }
        }
        $get_replies_stmt->close();
        
        // Delete replies
        $delete_replies_stmt = $mysqli->prepare("DELETE FROM replies WHERE discussion_id = ?");
        $delete_replies_stmt->bind_param("i", $discussion_id);
        $delete_replies_stmt->execute();
        $delete_replies_stmt->close();
        
        // Get discussion file path
        $get_discussion_stmt = $mysqli->prepare("SELECT file_path FROM discussions WHERE id = ?");
        $get_discussion_stmt->bind_param("i", $discussion_id);
        $get_discussion_stmt->execute();
        $discussion_result = $get_discussion_stmt->get_result();
        $discussion_data = $discussion_result->fetch_assoc();
        $get_discussion_stmt->close();
        
        // Delete discussion file if exists
        if (!empty($discussion_data['file_path']) && file_exists($discussion_data['file_path'])) {
            unlink($discussion_data['file_path']);
        }
        
        // Finally delete the discussion
        $delete_stmt = $mysqli->prepare("DELETE FROM discussions WHERE id = ?");
        $delete_stmt->bind_param("i", $discussion_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Diskusi berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus diskusi: " . $delete_stmt->error;
        }
        
        $delete_stmt->close();
    } else {
        $_SESSION['error'] = "Anda tidak memiliki izin untuk menghapus diskusi ini.";
    }
    
    $mysqli->close();
    header("Location: forum_diskusi.php");
    exit();
} else {
    header("Location: forum_diskusi.php");
    exit();
}