<?php
session_start();

$host = 'localhost';
$dbname = 'smpm29';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $fullName = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if ($userId) {
        try {
            // Update user data
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ? WHERE id = ?");
            $stmt->execute([$fullName, $username, $email, $userId]);
            
            // Redirect back to profile page
            header("Location: profil_saya.php");
            exit();
        } catch (PDOException $e) {
            die("Error updating profile: " . $e->getMessage());
        }
    }
}

// If something went wrong, redirect back
header("Location: profil_saya.php");
exit();
?>