<?php
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil data dari form
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validasi sederhana
    if (!$full_name || !$username || !$email || !$password || !$role) {
        die("Semua field harus diisi.");
    }

    if (strlen($password) < 8) {
        die("Password minimal 8 karakter.");
    }

    // Cek email sudah terdaftar atau belum
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        die("Email sudah terdaftar.");
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert data anggota
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (:full_name, :username, :email, :password, :role)");
    $stmt->execute([
        ':full_name' => $full_name,
        ':username' => $username,
        ':email' => $email,
        ':password' => $password_hash,
        ':role' => $role
    ]);

    // Redirect ke halaman tambah anggota dengan pesan sukses
    header("Location: tambah_anggota.php?status=success");
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
