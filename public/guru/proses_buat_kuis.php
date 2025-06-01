<?php
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['quiz_title'] ?? '';
        $duration_minutes = $_POST['duration'] ?? 0;
        $start_time = $_POST['starttime'] ?? date('Y-m-d H:i:s');
        $end_time = $_POST['endtime'] ?? date('Y-m-d H:i:s');

        if (empty($title)) {
            die("Judul kuis harus diisi.");
        }

        $course_id = 1; // sesuaikan dengan course yang valid

        // Simpan data kuis
        $sqlQuiz = "INSERT INTO quizzes (course_id, title, duration_minutes, start_time, end_time) 
                    VALUES (:course_id, :title, :duration_minutes, :start_time, :end_time)";
        $stmtQuiz = $pdo->prepare($sqlQuiz);
        $stmtQuiz->execute([
            ':course_id' => $course_id,
            ':title' => $title,
            ':duration_minutes' => $duration_minutes,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ]);
        $quiz_id = $pdo->lastInsertId();

        $questions = $_POST['questions'] ?? [];

        foreach ($questions as $q) {
            $question_text = trim($q['question'] ?? '');
            $correct_answer = $q['correct_answer'] ?? '';
            $options = $q['options'] ?? [];

            if ($question_text === '' || $correct_answer === '' || count($options) < 4) {
                continue; // Lewatkan jika data tidak lengkap
            }

            // Ambil opsi jawaban
            $option_a = trim($options[0]);
            $option_b = trim($options[1]);
            $option_c = trim($options[2]);
            $option_d = trim($options[3]);

            // Simpan pertanyaan ke database
            $sqlQuestion = "INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) 
                            VALUES (:quiz_id, :question_text, :option_a, :option_b, :option_c, :option_d, :correct_answer)";
            $stmtQuestion = $pdo->prepare($sqlQuestion);
            $stmtQuestion->execute([
                ':quiz_id' => $quiz_id,
                ':question_text' => $question_text,
                ':option_a' => $option_a,
                ':option_b' => $option_b,
                ':option_c' => $option_c,
                ':option_d' => $option_d,
                ':correct_answer' => $correct_answer
            ]);
        }

        header("Location: buat_kuis.php?success=1");
        exit;
    } else {
        die("Form belum disubmit dengan metode POST.");
    }
} catch (PDOException $e) {
    die("Gagal menyimpan kuis: " . $e->getMessage());
}
?>
