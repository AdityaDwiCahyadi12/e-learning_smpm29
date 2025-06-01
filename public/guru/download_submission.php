<?php
require_once 'config.php';

$file = $_GET['file'];

// Validasi path file untuk mencegah directory traversal
$safePath = realpath('uploads/' . basename($file));
if (strpos($safePath, realpath('uploads/')) !== 0) {
    die('Akses file tidak diizinkan');
}

if (file_exists($safePath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($safePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($safePath));
    readfile($safePath);
    exit;
} else {
    die('File tidak ditemukan');
}
?>