<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // Basic security: Prevent directory traversal
    $filepath = realpath($file); // Get absolute path
    $base_upload_dir = realpath('uploads/'); // Ensure this points to your actual uploads directory

    // Check if the requested file is within the allowed uploads directory
    if (strpos($filepath, $base_upload_dir) === 0 && file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        die("File tidak ditemukan atau akses ditolak.");
    }
} else {
    die("Parameter file tidak valid.");
}