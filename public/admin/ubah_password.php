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
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Ambil password dari database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($oldPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashedPassword, $userId]);

                // Simpan pesan ke session
                $_SESSION['message'] = "Password berhasil diubah.";
                header("Location: profil.php"); // Redirect ke halaman profil
                exit;
            } else {
                $_SESSION['error'] = "Password baru dan konfirmasi tidak cocok.";
                header("Location: profil.php"); // Redirect ke halaman profil
                exit;
            }
        } else {
            $_SESSION['error'] = "Password lama salah.";
            header("Location: profil.php"); // Redirect ke halaman profil
            exit;
        }
    }
} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}
