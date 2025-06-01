<?php

$MYSQL_DATABASE="railway";
$MYSQL_ROOT_PASSWORD="GOmZVqTVJVvIydPTVczPrsfdRmuLlpvy";
$MYSQLPORT="3306";
$MYSQLUSER="root";

$host = 'mysql.railway.internal'; 
$dbname = 'railway';  
$username = 'root';
$password = 'GOmZVqTVJVvIydPTVczPrsfdRmuLlpvy';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>