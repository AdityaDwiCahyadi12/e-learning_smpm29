<?php
session_start();

// Pastikan siswa sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: login.php");
    exit;
}

// Koneksi database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=smpm29', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Ambil data dari form
$quiz_id = $_POST['quiz_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$answers = $_POST['answers'] ?? [];

// Validasi
if ($quiz_id == 0 || empty($answers)) {
    die("Data tidak valid.");
}

try {
    // Mulai transaksi
    $pdo->beginTransaction();
    
    // Hitung skor
    $score = 0;
    $total_questions = 0;
    
    foreach ($answers as $question_id => $student_answer) {
        // Ambil jawaban yang benar dari database
        $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = :question_id");
        $stmt->execute([':question_id' => $question_id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($question) {
            $total_questions++;
            if ($student_answer == $question['correct_answer']) {
                $score++;
            }
            
            // Simpan jawaban siswa
            $stmt_answer = $pdo->prepare("INSERT INTO student_answers (user_id, question_id, answer) 
                                         VALUES (:user_id, :question_id, :answer)");
            $stmt_answer->execute([
                ':user_id' => $user_id,
                ':question_id' => $question_id,
                ':answer' => $student_answer
            ]);
        }
    }
    
    // Hitung nilai dalam persentase
    $percentage = ($total_questions > 0) ? round(($score / $total_questions) * 100, 2) : 0;
    
    // Simpan hasil kuis
    $stmt_result = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, total_questions, percentage, completed_at) 
                                 VALUES (:user_id, :quiz_id, :score, :total_questions, :percentage, NOW())");
    $stmt_result->execute([
        ':user_id' => $user_id,
        ':quiz_id' => $quiz_id,
        ':score' => $score,
        ':total_questions' => $total_questions,
        ':percentage' => $percentage
    ]);
    
    // Commit transaksi
    $pdo->commit();
    
    // Redirect ke halaman hasil
    header("Location: hasil_kuis.php?quiz_id=" . $quiz_id);
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Gagal menyimpan jawaban: " . $e->getMessage());
}
?>