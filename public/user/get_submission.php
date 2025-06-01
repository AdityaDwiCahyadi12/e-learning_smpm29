<?php
session_start();

// Konfigurasi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

// Membuat koneksi
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Koneksi gagal: ' . $e->getMessage()]));
}

// Pastikan user sudah login dan adalah siswa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'siswa') {
    die(json_encode(['error' => 'Unauthorized']));
}

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Ambil data pengumpulan
    $stmt = $conn->prepare("SELECT ts.*, t.title AS task_title, 
                           GROUP_CONCAT(sf.file_name SEPARATOR '|||') AS file_names,
                           GROUP_CONCAT(sf.file_path SEPARATOR '|||') AS file_paths
                           FROM task_submissions ts
                           JOIN tasks t ON ts.task_id = t.id
                           LEFT JOIN submission_files sf ON ts.id = sf.submission_id
                           WHERE ts.id = ? AND ts.user_id = ?
                           GROUP BY ts.id");
    $stmt->execute([$submission_id, $_SESSION['user_id']]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        die(json_encode(['error' => 'Pengumpulan tidak ditemukan']));
    }
    
    // Format data untuk response
    $response = [
        'task_title' => $submission['task_title'],
        'submitted_at' => date('d M Y H:i', strtotime($submission['submitted_at'])),
        'grade' => $submission['grade'],
        'feedback' => $submission['feedback'],
        'files' => [],
        'status' => $submission['grade'] ? 'Dinilai' : ($submission['is_late'] ? 'Terlambat' : 'Terkumpul')
    ];
    
    // Siapkan data file
    if (!empty($submission['file_names'])) {
        $file_names = explode('|||', $submission['file_names']);
        $file_paths = explode('|||', $submission['file_paths']);
        
        foreach ($file_names as $index => $name) {
            if (isset($file_paths[$index])) {
                $response['files'][] = [
                    'name' => $name,
                    'path' => $file_paths[$index]
                ];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}
?>