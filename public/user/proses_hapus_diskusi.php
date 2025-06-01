<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diskusiId = $_POST['diskusi_id'] ?? null;

    if ($diskusiId) {
        // Koneksi database
        $host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
        $dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
        $username = getenv('MYSQLUSER') ?: 'root';
        $password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
        $port = getenv('MYSQLPORT') ?: '3306';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cek apakah user adalah pemilik diskusi atau admin
            $stmt = $pdo->prepare("SELECT user_id FROM discussions WHERE id = ?");
            $stmt->execute([$diskusiId]);
            $diskusi = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($diskusi && ($diskusi['user_id'] == $userId || $userRole === 'admin')) {
                // Hapus diskusi
                $deleteStmt = $pdo->prepare("DELETE FROM discussions WHERE id = ?");
                $deleteStmt->execute([$diskusiId]);

                // (Opsional) Hapus juga balasan terkait diskusi ini
                $deleteReplies = $pdo->prepare("DELETE FROM replies WHERE discussion_id = ?");
                $deleteReplies->execute([$diskusiId]);

                header('Location: forum_diskusi.php?msg=Diskusi berhasil dihapus');
                exit;
            } else {
                header('Location: forum_diskusi.php?error=Anda tidak berhak menghapus diskusi ini');
                exit;
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}

header('Location: forum_diskusi.php');
exit;
?>
