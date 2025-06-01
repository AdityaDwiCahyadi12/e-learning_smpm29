<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // --- PENTING: Keamanan untuk mencegah Directory Traversal ---
    // Pastikan hanya file di dalam folder 'uploads/' yang bisa diakses
    $filepath = realpath($file); // Dapatkan path absolut dari file yang diminta
    $base_upload_dir = realpath(__DIR__ . '/uploads/'); // Dapatkan path absolut dari folder 'uploads'

    // Periksa apakah file yang diminta benar-benar ada di dalam folder uploads
    if ($filepath && strpos($filepath, $base_upload_dir) === 0 && file_exists($filepath)) {
        // Amankan nama file untuk ditampilkan ke user
        $filename_display = basename($filepath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream'); // Tipe umum untuk unduhan
        header('Content-Disposition: attachment; filename="' . $filename_display . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath)); // Ukuran file
        readfile($filepath); // Baca dan kirim file
        exit;
    } else {
        die("File tidak ditemukan atau akses ditolak.");
    }
} else {
    die("Parameter file tidak valid.");
}
?>