<?php
session_start();

// Pastikan folder uploads ada
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_profil'])) {
    $userId = $_POST['user_id'] ?? null;

    if ($userId) {
        // Cek error upload
        if ($_FILES['foto_profil']['error'] !== UPLOAD_ERR_OK) {
            die("Terjadi kesalahan saat upload file.");
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileTmp = $_FILES['foto_profil']['tmp_name'];
        $fileType = mime_content_type($fileTmp);
        $fileSize = $_FILES['foto_profil']['size'];

        // Validasi tipe file
        if (!in_array($fileType, $allowedTypes)) {
            die("Format file tidak didukung. Hanya JPG, PNG, dan GIF yang diperbolehkan.");
        }

        // Validasi ukuran file max 10MB
        if ($fileSize > 10 * 1024 * 1024) {
            die("Ukuran file maksimal 10MB.");
        }

        // Cek apakah fungsi GD tersedia
        if (!function_exists('imagecreatefromjpeg')) {
            die("Ekstensi GD belum aktif di server. Silakan aktifkan terlebih dahulu.");
        }

        $targetDir = "uploads/";
        $targetFile = $targetDir . "user_" . $userId . ".jpg";

        // Ambil dimensi gambar asli
        list($width, $height) = getimagesize($fileTmp);

        // Buat resource gambar sesuai tipe
        switch ($fileType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($fileTmp);
                break;
            case 'image/png':
                $image = imagecreatefrompng($fileTmp);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($fileTmp);
                break;
            default:
                die("Format file tidak didukung.");
        }

        // Ukuran baru
        $newWidth = 300;
        $newHeight = 300;

        // Buat canvas kosong
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Untuk PNG dan GIF dengan transparansi, tambahkan ini:
        if ($fileType == 'image/png' || $fileType == 'image/gif') {
            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Resize gambar
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Simpan gambar jadi JPG (kecuali PNG/GIF, tapi kita simpan tetap JPG)
        imagejpeg($newImage, $targetFile, 90);

        // Bersihkan memory
        imagedestroy($image);
        imagedestroy($newImage);

        header("Location: profil_saya.php");
        exit();
    }
}
?>
