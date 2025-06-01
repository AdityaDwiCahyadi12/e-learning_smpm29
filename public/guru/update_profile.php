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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullName = $_POST['full_name'];
        $username = $_POST['username'];
        $email = $_POST['email'];

        $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ? WHERE id = ?");
        $updateStmt->execute([$fullName, $username, $email, $userId]);

        // Simpan pesan ke session
        $_SESSION['message'] = "Profil berhasil diubah.";
        header("Location: profil.php"); // Redirect ke halaman profil
        exit;
    }
} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}
