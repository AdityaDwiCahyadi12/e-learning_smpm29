<?php
session_start();

// Konfigurasi path
$baseUploadDir = __DIR__ . '/../uploads/materials/'; // Sesuaikan dengan struktur folder Anda

if (isset($_GET['file'])) {
    // Bersihkan nama file
    $requestedFile = basename($_GET['file']);
    $filePath = realpath($baseUploadDir . $requestedFile);

    // Verifikasi keamanan
    if ($filePath === false || !file_exists($filePath)) {
        http_response_code(404);
        die("File tidak ditemukan.");
    }

    // Pastikan file berada di dalam direktori yang diizinkan
    if (strpos($filePath, realpath($baseUploadDir)) !== 0) {
        http_response_code(403);
        die("Akses ditolak.");
    }

    // Verifikasi ekstensi file yang diizinkan
    $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        http_response_code(403);
        die("Tipe file tidak diizinkan.");
    }

    // Tentukan Content-Type berdasarkan ekstensi file
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo'
    ];

    $contentType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';

    // Set header untuk download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));

    // Bersihkan output buffer dan kirim file
    ob_clean();
    flush();
    readfile($filePath);
    exit;
} else {
    http_response_code(400);
    die("Parameter file tidak valid.");
}
?>