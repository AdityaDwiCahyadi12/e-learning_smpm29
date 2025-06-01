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

    $quizId = $_GET['id'] ?? null;

    if (!$quizId) {
        header("Location: buat_kuis.php");
        exit;
    }

    // Ambil data kuis
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        header("Location: buat_kuis.php");
        exit;
    }

    // Ambil pertanyaan terkait kuis
    $questionsStmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
    $questionsStmt->execute([$quizId]);
    $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Proses pengeditan kuis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['quiz_title'];
        $duration = $_POST['duration'];
        $startTime = $_POST['starttime'];
        $endTime = $_POST['endtime'];

        // Update kuis
        $updateStmt = $pdo->prepare("UPDATE quizzes SET title = ?, duration_minutes = ?, start_time = ?, end_time = ? WHERE id = ?");
        $updateStmt->execute([$title, $duration, $startTime, $endTime, $quizId]);

        // Update pertanyaan
        foreach ($_POST['questions'] as $questionId => $questionData) {
            $questionText = $questionData['question'];
            $correctAnswer = $questionData['correct_answer'] ?? null; // Use null if not set

            // Ambil pilihan jawaban dari input
            $optionA = $questionData['options'][0] ?? ''; // Pilihan A
            $optionB = $questionData['options'][1] ?? ''; // Pilihan B
            $optionC = $questionData['options'][2] ?? ''; // Pilihan C
            $optionD = $questionData['options'][3] ?? ''; // Pilihan D

            // Update pertanyaan
            $updateQuestionStmt = $pdo->prepare("UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ? WHERE id = ?");
            $updateQuestionStmt->execute([$questionText, $optionA, $optionB, $optionC, $optionD, $correctAnswer, $questionId]);
        }

        // Redirect setelah berhasil
        header("Location: buat_kuis.php");
        exit;
    }
} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kuis - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <!-- (Sidebar code here) -->
        
        <!-- Main Content -->
        <div class="ml-64 w-full p-6">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Kuis</h2>
                <form action="" method="POST">
                    <div class="mb-6">
                        <label for="quiz-title" class="block text-sm font-semibold text-gray-600 mb-2">Judul Kuis</label>
                        <input type="text" id="quiz-title" name="quiz_title" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($quiz['title']) ?>" required>
                    </div>

                    <div class="mb-6">
                        <label for="duration" class="block text-sm font-semibold text-gray-600 mb-2">Durasi Kuis (menit)</label>
                        <input type="number" id="duration" name="duration" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($quiz['duration_minutes']) ?>" required>
                    </div>

                    <div class="mb-6">
                        <label for="starttime" class="block text-sm font-semibold text-gray-600 mb-2">Waktu Mulai</label>
                        <input type="datetime-local" id="starttime" name="starttime" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= date('Y-m-d\TH:i', strtotime($quiz['start_time'])) ?>" required>
                    </div>

                    <div class="mb-6">
                        <label for="endtime" class="block text-sm font-semibold text-gray-600 mb-2">Waktu Selesai</label>
                        <input type="datetime-local" id="endtime" name="endtime" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= date('Y-m-d\TH:i', strtotime($quiz['end_time'])) ?>" required>
                    </div>

                    <h3 class="text-md font-semibold text-gray-800 mb-4">Pertanyaan Kuis</h3>
                    <div id="questions-container">
                        <?php foreach ($questions as $question): ?>
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-600 mb-2">Pertanyaan</label>
                                <input type="text" name="questions[<?= $question['id'] ?>][question]" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($question['question_text']) ?>" required>
                                
                                <label class="block text-sm font-semibold text-gray-600 mb-2 mt-4">Pilihan Jawaban</label>
                                <div class="flex items-center mb-2">
                                    <input type="text" name="questions[<?= $question['id'] ?>][options][]" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($question['option_a']) ?>" required placeholder="Pilihan A">
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="text" name="questions[<?= $question['id'] ?>][options][]" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($question['option_b']) ?>" required placeholder="Pilihan B">
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="text" name="questions[<?= $question['id'] ?>][options][]" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($question['option_c']) ?>" required placeholder="Pilihan C">
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="text" name="questions[<?= $question['id'] ?>][options][]" class="w-full p-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($question['option_d']) ?>" required placeholder="Pilihan D">
                                </div>
                                <label class="block text-sm font-semibold text-gray-600 mb-2">Jawaban Benar</label>
                                <select name="questions[<?= $question['id'] ?>][correct_answer]" class="w-full p-3 border border-gray-300 rounded-lg" required>
                                    <option value="A" <?= $question['correct_answer'] == 'A' ? 'selected' : '' ?>>A</option>
                                    <option value="B" <?= $question['correct_answer'] == 'B' ? 'selected' : '' ?>>B</option>
                                    <option value="C" <?= $question['correct_answer'] == 'C' ? 'selected' : '' ?>>C</option>
                                    <option value="D" <?= $question['correct_answer'] == 'D' ? 'selected' : '' ?>>D</option>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex justify-center mt-4">
                        <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
