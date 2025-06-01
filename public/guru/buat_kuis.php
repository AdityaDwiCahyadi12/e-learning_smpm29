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
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Handle delete quiz
if (isset($_GET['delete_id'])) {
    $quizId = $_GET['delete_id'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // First delete all questions related to this quiz
        $stmt = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        
        // Then delete the quiz itself
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([$quizId]);
        
        // Commit transaction
        $pdo->commit();
        
        // Redirect to refresh the page
        header("Location: buat_kuis.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Gagal menghapus kuis: " . $e->getMessage());
    }
}

// Handle form submission for new quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['quiz_title'];
    $duration = $_POST['duration'];
    $startTime = $_POST['starttime'];
    $endTime = $_POST['endtime'];

    // Insert quiz into the database
    $stmt = $pdo->prepare("INSERT INTO quizzes (title, duration_minutes, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $duration, $startTime, $endTime]);
    $quizId = $pdo->lastInsertId(); // Mendapatkan ID kuis yang baru dimasukkan

    // Insert questions into the database
    foreach ($_POST['questions'] as $questionData) {
        $questionText = $questionData['question'];
        $options = $questionData['options'];
        $correctAnswer = $questionData['correct_answer'];

        // Insert each question
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$quizId, $questionText, $options[0], $options[1], $options[2], $options[3], $correctAnswer]);
    }

    // Redirect after successful insertion
    header("Location: buat_kuis.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kuis - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item {
            transition: all 0.2s ease;
        }
        .sidebar-item:hover {
            background-color: #f1f5f9;
            border-left: 3px solid #3b82f6;
        }
        .sidebar-item.active {
            background-color: #e0e7ff;
            color: #3b82f6;
            font-weight: 500;
            border-left: 3px solid #3b82f6;
        }
        .question-container {
            position: relative;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            background-color: white;
        }
        .delete-question {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            color: #94a3b8;
            cursor: pointer;
        }
        .delete-question:hover {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 h-screen bg-white shadow-lg fixed left-0 sidebar">
            <div class="flex items-center justify-center p-4 border-b">
                <span class="text-xl font-bold text-gray-800">E-Learning Class</span>
            </div>
            <div class="p-4 overflow-y-auto" style="height: calc(100vh - 64px);">
                <!-- Menu Utama -->
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
                <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'dashboard.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i><span>Dashboard</span>
                </a>
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">MANAJEMEN</p>
                <a href="profil.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'profil.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-user w-5 mr-3"></i><span>Profil</span>
                </a>
                <a href="tambah_anggota.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_anggota.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-user-plus w-5 mr-3"></i><span>Tambah Anggota</span>
                </a>
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
                </a>
                <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_materi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-book-open w-5 mr-3"></i><span>Tambah Materi</span>
                </a>
                <a href="buat_kuis.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-question-circle w-5 mr-3"></i><span>Buat Kuis</span>
                </a>
                <a href="buat_tugas.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_tugas.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-tasks w-5 mr-3"></i><span>Buat Tugas</span>
                </a>
                <div class="border-t mt-4 pt-2">
                    <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i><span>Keluar</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="ml-64 w-full p-6">
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Buat Kuis Interaktif</h1>
                <p class="text-sm text-gray-600">Buat kuis menarik dengan berbagai jenis pertanyaan</p>
            </div>

            <!-- Quiz Creation Form -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Form Buat Kuis</h2>
                <form id="quiz-form" action="" method="POST">
                    <div class="mb-6">
                        <label for="quiz-title" class="block text-sm font-semibold text-gray-600 mb-2">Judul Kuis</label>
                        <input type="text" id="quiz-title" name="quiz_title" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan judul kuis" required>
                    </div>

                    <!-- Duration and Time -->
                    <div class="mb-6">
                        <label for="duration" class="block text-sm font-semibold text-gray-600 mb-2">Durasi Kuis (menit)</label>
                        <input type="number" id="duration" name="duration" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan durasi dalam menit" required>
                    </div>
                    <div class="mb-6">
                        <label for="starttime" class="block text-sm font-semibold text-gray-600 mb-2">Waktu Mulai</label>
                        <input type="datetime-local" id="starttime" name="starttime" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="mb-6">
                        <label for="endtime" class="block text-sm font-semibold text-gray-600 mb-2">Waktu Selesai</label>
                        <input type="datetime-local" id="endtime" name="endtime" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <!-- Dynamic Questions -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-md font-semibold text-gray-800">Pertanyaan Kuis</h3>
                            <button type="button" id="add-question" class="flex items-center text-sm bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                                <i class="fas fa-plus mr-2"></i> Tambah Pertanyaan
                            </button>
                        </div>
                        <div id="questions-container">
                            <!-- Questions will be dynamically added here -->
                        </div>
                    </div>

                    <div class="flex justify-center mt-4">
                        <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            Simpan Kuis
                        </button>
                    </div>
                </form>
            </div>

            <!-- List of Existing Quizzes -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Daftar Kuis</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <tr>
                                <th class="py-3 px-6 text-left">Judul Kuis</th>
                                <th class="py-3 px-6 text-left">Durasi (menit)</th>
                                <th class="py-3 px-6 text-left">Waktu Mulai</th>
                                <th class="py-3 px-6 text-left">Waktu Selesai</th>
                                <th class="py-3 px-6 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                        <?php
                        // Tampilkan daftar kuis
                        try {
                            $stmt = $pdo->query("SELECT * FROM quizzes ORDER BY id DESC");
                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr class='border-b border-gray-200 hover:bg-gray-50'>";
                                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['duration_minutes']) . "</td>";
                                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars(date('d M Y H:i', strtotime($row['start_time']))) . "</td>";
                                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars(date('d M Y H:i', strtotime($row['end_time']))) . "</td>";
                                    echo "<td class='py-3 px-6 text-left'>";
                                    echo "<div class='flex space-x-2'>";
                                    echo "<a href='edit_kuis.php?id=" . $row['id'] . "' class='text-blue-500 hover:text-blue-700' title='Edit'><i class='fas fa-edit'></i></a>";
                                    echo "<a href='buat_kuis.php?delete_id=" . $row['id'] . "' class='text-red-500 hover:text-red-700' title='Hapus' onclick='return confirm(\"Yakin ingin menghapus kuis ini?\")'><i class='fas fa-trash'></i></a>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-gray-500'>Belum ada kuis yang dibuat</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='5' class='text-center py-3 text-red-500'>Gagal memuat data kuis: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tambah pertanyaan baru
        document.getElementById('add-question').addEventListener('click', function() {
            const questionContainer = document.getElementById('questions-container');
            const questionId = Date.now();
            const questionHTML = `
                <div class="question-container" id="question-${questionId}">
                    <button type="button" class="delete-question" onclick="removeQuestion(${questionId})">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Pertanyaan</label>
                        <input type="text" name="questions[${questionId}][question]" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Masukkan pertanyaan" required>
                    </div>

                    <div class="mb-2">
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Pilihan Jawaban</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" name="questions[${questionId}][correct_answer]" value="0" class="mr-2" required>
                                <input type="text" name="questions[${questionId}][options][]" 
                                       class="w-full p-3 border border-gray-300 rounded-lg" 
                                       placeholder="Pilihan A" required>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="questions[${questionId}][correct_answer]" value="1" class="mr-2">
                                <input type="text" name="questions[${questionId}][options][]" 
                                       class="w-full p-3 border border-gray-300 rounded-lg" 
                                       placeholder="Pilihan B" required>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="questions[${questionId}][correct_answer]" value="2" class="mr-2">
                                <input type="text" name="questions[${questionId}][options][]" 
                                       class="w-full p-3 border border-gray-300 rounded-lg" 
                                       placeholder="Pilihan C" required>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="questions[${questionId}][correct_answer]" value="3" class="mr-2">
                                <input type="text" name="questions[${questionId}][options][]" 
                                       class="w-full p-3 border border-gray-300 rounded-lg" 
                                       placeholder="Pilihan D" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            questionContainer.insertAdjacentHTML('beforeend', questionHTML);
        });

        // Hapus pertanyaan
        function removeQuestion(id) {
            const questionElement = document.getElementById(`question-${id}`);
            if (questionElement) {
                questionElement.remove();
            }
        }

        // Validasi waktu
        document.getElementById('quiz-form').addEventListener('submit', function(e) {
            const startTime = new Date(document.getElementById('starttime').value);
            const endTime = new Date(document.getElementById('endtime').value);
            
            if (startTime >= endTime) {
                alert('Waktu selesai harus setelah waktu mulai');
                e.preventDefault();
            }
            
            const questionCount = document.querySelectorAll('.question-container').length;
            if (questionCount === 0) {
                alert('Harus ada minimal 1 pertanyaan');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>