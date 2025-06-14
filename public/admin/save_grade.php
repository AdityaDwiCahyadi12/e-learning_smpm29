<?php
session_start();
header('Content-Type: application/json');

// Konfigurasi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

// Connect to database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get submission ID, grade, and feedback from request
$submissionId = $_POST['submission_id'];
$grade = $_POST['grade'];
$feedback = $_POST['feedback'];

// Query to update grade and feedback
$sql = "UPDATE submissions SET grade = '$grade', feedback = '$feedback' WHERE id = '$submissionId'";
$result = $conn->query($sql);

// Check if update was successful
if ($result) {
    echo json_encode(['success' => true, 'message' => 'Grade and feedback updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating grade and feedback: ' . $conn->error]);
}
?>