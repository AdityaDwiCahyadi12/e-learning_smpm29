<?php

$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage()); // log ke Railway
    die("Terjadi kesalahan pada database: " . $e->getMessage()); // tampilkan ke browser
}

error_log("ENV DEBUG - MYSQLHOST: " . getenv('MYSQLHOST'));
error_log("ENV DEBUG - MYSQLPORT: " . getenv('MYSQLPORT'));
error_log("ENV DEBUG - MYSQL_DATABASE: " . getenv('MYSQL_DATABASE'));
error_log("ENV DEBUG - MYSQLUSER: " . getenv('MYSQLUSER'));
error_log("ENV DEBUG - MYSQL_ROOT_PASSWORD: " . getenv('MYSQL_ROOT_PASSWORD'));
?>