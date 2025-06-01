<?php
session_start();

$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user']['id'] ?? null;

    if (!$userId) {
        header("Location: login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_profil'])) {
        $file = $_FILES['foto_profil'];

        // Cek apakah ada kesalahan saat upload
        if ($file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $fileName = "user_$userId.jpg"; // Nama file yang akan disimpan
            $filePath = $uploadDir . $fileName;

            // Cek apakah direktori uploads ada, jika tidak buat
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Pindahkan file yang diupload ke direktori uploads
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Berhasil mengupload
                header("Location: profil.php"); // Redirect ke halaman profil
                exit;
            } else {
                echo "Gagal mengupload foto.";
            }
        } else {
            echo "Terjadi kesalahan saat mengupload foto.";
        }
    }
} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}
