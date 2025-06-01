<?php
// Mulai session
session_start();

// Konfigurasi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

// Membuat koneksi PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Ambil data user
$userId = $_SESSION['user']['id'] ?? null;
$userData = [];

if ($userId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error mengambil data user: " . $e->getMessage());
    }
}

// Check if user photo exists
$photoPath = file_exists("uploads/user_$userId.jpg") ? "uploads/user_$userId.jpg" : "https://ui-avatars.com/api/?name=" . urlencode($userData['full_name'] ?? 'User') . "&background=3b82f6&color=fff";

// Ambil data kuis (pertanyaan dan pilihan jawaban)
$questions = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM questions");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error mengambil data pertanyaan: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kerjakan Kuis - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
        .quiz-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .quiz-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .option-item {
            transition: all 0.2s ease;
        }
        .option-item:hover {
            background-color: #f1f5f9;
        }
        .option-item.correct {
            background-color: #d1fae5;
            border-color: #10b981;
        }
        .option-item.incorrect {
            background-color: #fee2e2;
            border-color: #ef4444;
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
        .timer-warning {
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { color: #ef4444; }
            50% { color: #f87171; }
            100% { color: #ef4444; }
        }
        .start-quiz-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60vh;
        }
        .start-quiz-btn {
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .start-quiz-btn:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }
        .quiz-instructions {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            max-width: 600px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .hidden {
            display: none;
        }
        .timer {
            font-size: 18px;
            font-weight: 600;
        }
        .timer-danger {
            color: #ef4444;
            animation: pulse 1s infinite;
        }
        .finish-quiz-btn {
            background-color: #ef4444;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .finish-quiz-btn:hover {
            background-color: #dc2626;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            <div class="p-4">
                <div class="flex items-center mb-6 p-3 bg-gray-100 rounded-lg">
                    <img src="<?= $photoPath ?>" alt="Profil" class="w-10 h-10 rounded-full mr-3 object-cover">
                    <div>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($userData['full_name'] ?? 'User') ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($userData['role'] ?? 'Role') ?></p>
                    </div>
                </div>

                <p class="text-xs font-semibold text-gray-500 px-2 mb-2">MENU UTAMA</p>
                <a href="dashboard.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-home w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PROFIL</p>
                <a href="profil_saya.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-user w-5 mr-3"></i>
                    <span>Profil Saya</span>
                </a>

                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-comments w-5 mr-3"></i>
                    <span>Forum Diskusi</span>
                </a>
                <a href="materi.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-book-open w-5 mr-3"></i>
                    <span>Materi</span>
                </a>
                <a href="kuis.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-question-circle w-5 mr-3"></i>
                    <span>Kuis</span>
                </a>
                <a href="tugas.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-tasks w-5 mr-3"></i>
                    <span>Tugas</span>
                </a>

                <div class="border-t mt-4 pt-2">
                    <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                        <span>Keluar</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="ml-64 w-full p-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">Kuis</h1>
                        <p class="text-sm text-gray-600">Jawablah kuis berdasarkan materi pembelajaran yang telah disediakan oleh guru</p>
                    </div>
                </div>
            </div>

            <!-- Start Quiz Section -->
            <div id="start-quiz-section" class="start-quiz-container fade-in">
                <div class="quiz-instructions">
                    <h2 class="text-lg font-semibold mb-3">Petunjuk Pengerjaan Kuis</h2>
                    <ul class="list-disc pl-5 space-y-2 text-gray-700">
                        <li>Kuis terdiri dari <?php echo count($questions); ?> pertanyaan pilihan ganda</li>
                        <li>Setiap pertanyaan memiliki bobot nilai yang sama</li>
                        <li>Anda tidak bisa kembali ke pertanyaan sebelumnya setelah mengirim jawaban</li>
                        <li>Pastikan semua pertanyaan telah dijawab sebelum mengirim kuis</li>
                        <li>Waktu pengerjaan: 30 menit</li>
                    </ul>
                </div>
                <button id="start-quiz-btn" class="start-quiz-btn">
                    <i class="fas fa-play mr-2"></i> Mulai Kuis
                </button>
            </div>

            <!-- Quiz Content (Initially Hidden) -->
            <div id="quiz-content" class="hidden">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Questions Navigation -->
                    <div class="w-full lg:w-1/4">
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="font-medium text-gray-800 mb-3">Daftar Pertanyaan</h3>
                            <div class="grid grid-cols-5 gap-2">
                                <?php foreach ($questions as $index => $question) { ?>
                                    <button class="question-nav bg-gray-100 text-gray-800 w-10 h-10 rounded-full flex items-center justify-center font-medium"><?= $index + 1 ?></button>
                                <?php } ?>
                            </div>

                            <div class="mt-6">
                                <h3 class="font-medium text-gray-800 mb-3">Progress Kuis</h3>
                                <div class="flex justify-between mb-1">
                                    <span id="progress-text" class="text-sm font-medium text-gray-700">0/<?php echo count($questions); ?> Pertanyaan</span>
                                    <span id="progress-percent" class="text-sm font-medium text-gray-700">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div id="progress-bar" class="progress-bar bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t flex justify-between items-center">
                                <div class="timer flex items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <span id="quiz-timer">30:00</span>
                                </div>
                                <button id="finish-quiz" class="finish-quiz-btn">
                                    <i class="fas fa-flag mr-2"></i> Selesai Kuis
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Question Content -->
                    <div class="w-full lg:w-3/4">
                        <form id="quiz-form" class="quiz-card bg-white rounded-lg shadow-sm overflow-hidden">
                            <!-- Question Header -->
                            <div class="flex items-center justify-between p-4 bg-blue-50 border-b">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-question-circle text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h2 id="question-number" class="font-semibold text-gray-800">Pertanyaan 1</h2>
                                        <p class="text-xs text-gray-600" id="question-type">Pilihan Ganda • Nilai: 10</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Question Body -->
                            <div class="p-6">
                                <p id="question-text" class="font-medium text-gray-800 mb-6">Loading pertanyaan...</p>

                                <!-- Multiple Choice Options -->
                                <div id="options-list" class="options-list space-y-3">
                                    <!-- Opsi akan di-generate oleh JavaScript -->
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="flex justify-between mt-8">
                                    <button type="button" id="prev-question" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hidden">
                                        <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                                    </button>
                                    <button type="button" id="next-question" class="ml-auto bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                                        Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Modal (Hidden) -->
    <div id="result-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="bg-green-100 p-4 rounded-full inline-block mb-4">
                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Kuis Selesai!</h3>
                <p class="text-gray-600 mt-2">Terima kasih telah mengerjakan kuis ini. Kamu telah selesai!</p>
                <button id="close-modal" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questions = <?php echo json_encode($questions); ?>;
            let currentQuestion = 0;
            let userAnswers = Array(questions.length).fill(null);
            let quizStarted = false;
            let quizTimerInterval;
            let timeLeft = 30 * 60; // 30 minutes in seconds

            // DOM Elements
            const startQuizSection = document.getElementById('start-quiz-section');
            const quizContent = document.getElementById('quiz-content');
            const startQuizBtn = document.getElementById('start-quiz-btn');
            const prevQuestionBtn = document.getElementById('prev-question');
            const nextQuestionBtn = document.getElementById('next-question');
            const finishQuizBtn = document.getElementById('finish-quiz');
            const progressText = document.getElementById('progress-text');
            const progressPercent = document.getElementById('progress-percent');
            const progressBar = document.getElementById('progress-bar');
            const quizTimer = document.getElementById('quiz-timer');
            const questionNavButtons = document.querySelectorAll('.question-nav');
            const questionNumber = document.getElementById('question-number');
            const questionType = document.getElementById('question-type');
            const questionText = document.getElementById('question-text');
            const optionsList = document.getElementById('options-list');
            const modal = document.getElementById('result-modal');

            // Function to update timer display
            function updateTimerDisplay() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                quizTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 5 * 60) {
                    quizTimer.classList.add('timer-danger');
                } else {
                    quizTimer.classList.remove('timer-danger');
                }
            }

            // Function to reset quiz to initial state
            function resetQuiz() {
                currentQuestion = 0;
                userAnswers = Array(questions.length).fill(null);
                quizStarted = false;
                timeLeft = 30 * 60;
                clearInterval(quizTimerInterval);
                
                // Reset UI
                quizContent.classList.add('hidden');
                startQuizSection.classList.remove('hidden');
                updateProgress();
                
                // Reset timer display
                quizTimer.textContent = '30:00';
                quizTimer.classList.remove('timer-danger');
            }

            // Function to submit quiz
            function submitQuiz() {
                clearInterval(quizTimerInterval);
                
                let correct = 0;
                let correctAnswersList = '';

                questions.forEach((q, i) => {
                    const userAnswer = userAnswers[i];
                    const correctIndex = { A: 0, B: 1, C: 2, D: 3 }[q.correct_answer.toUpperCase()];
                    
                    if (userAnswer !== null && userAnswer === correctIndex) {
                        correct++;
                    }

                    correctAnswersList += `Pertanyaan ${i + 1}: Jawaban Benar ${q.correct_answer.toUpperCase()}<br>`;
                });

                const score = correct * 10;
                const wrong = questions.length - correct;

                modal.querySelector('.text-center').innerHTML = `
                    <div class="bg-green-100 p-4 rounded-full inline-block mb-4">
                        <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-800">Kuis Selesai!</h3>
                    <p class="text-gray-600 mt-2 mb-2">Skor Akhir: <strong>${score}</strong></p>
                    <p class="text-gray-600 mb-2">Benar: <strong>${correct}</strong> | Salah: <strong>${wrong}</strong></p>
                    <div class="text-left text-sm bg-gray-100 p-3 rounded mb-3">${correctAnswersList}</div>
                    <button id="close-modal" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">Tutup</button>
                `;

                modal.classList.remove('hidden');

                document.getElementById('close-modal').addEventListener('click', () => {
                    modal.classList.add('hidden');
                    resetQuiz();
                });
            }

            // Function to start the quiz
            function startQuiz() {
                quizStarted = true;
                startQuizSection.classList.add('hidden');
                quizContent.classList.remove('hidden');
                showQuestion(currentQuestion);
                updateProgress();
                
                // Start timer
                updateTimerDisplay();
                quizTimerInterval = setInterval(() => {
                    timeLeft--;
                    updateTimerDisplay();
                    
                    if (timeLeft <= 0) {
                        clearInterval(quizTimerInterval);
                        submitQuiz();
                    }
                }, 1000);
            }

            // Function to show question
            function showQuestion(index) {
                if (!quizStarted) return;
                
                const question = questions[index];
                questionNumber.textContent = `Pertanyaan ${index + 1}`;
                questionType.textContent = 'Pilihan Ganda • Nilai: 10';
                questionText.textContent = question.question_text;

                optionsList.innerHTML = '';

                const options = [question.option_a, question.option_b, question.option_c, question.option_d];
                options.forEach((option, i) => {
                    const label = document.createElement('label');
                    label.className = 'option-item flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50';
                    label.innerHTML = `
                        <input type="radio" name="question_${index}" class="h-4 w-4 text-blue-600 mr-3" value="${i}">
                        <span>${option}</span>
                    `;
                    const input = label.querySelector('input');
                    input.addEventListener('change', () => {
                        userAnswers[index] = parseInt(input.value);
                        updateProgress();
                    });
                    if (userAnswers[index] === i) input.checked = true;
                    optionsList.appendChild(label);
                });

                prevQuestionBtn.classList.toggle('hidden', index === 0);
                nextQuestionBtn.classList.toggle('hidden', index === questions.length - 1);

                questionNavButtons.forEach((btn, i) => {
                    if (i === index) {
                        btn.classList.add('bg-blue-100', 'text-blue-800');
                        btn.classList.remove('bg-gray-100', 'text-gray-800');
                    } else {
                        btn.classList.remove('bg-blue-100', 'text-blue-800');
                        btn.classList.add('bg-gray-100', 'text-gray-800');
                    }
                    if (userAnswers[i] !== null) {
                        btn.classList.add('ring-2', 'ring-blue-500');
                    } else {
                        btn.classList.remove('ring-2', 'ring-blue-500');
                    }
                });
            }

            // Function to update progress
            function updateProgress() {
                const answered = userAnswers.filter(a => a !== null).length;
                const percent = (answered / questions.length) * 100;
                progressText.textContent = `${answered}/${questions.length} Pertanyaan`;
                progressPercent.textContent = `${Math.round(percent)}%`;
                progressBar.style.width = `${percent}%`;
            }

            // Event Listeners
            startQuizBtn.addEventListener('click', startQuiz);

            nextQuestionBtn.addEventListener('click', () => {
                if (currentQuestion < questions.length - 1) {
                    currentQuestion++;
                    showQuestion(currentQuestion);
                }
            });

            prevQuestionBtn.addEventListener('click', () => {
                if (currentQuestion > 0) {
                    currentQuestion--;
                    showQuestion(currentQuestion);
                }
            });

            questionNavButtons.forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    currentQuestion = index;
                    showQuestion(currentQuestion);
                });
            });
            
            finishQuizBtn.addEventListener('click', () => {
                if (confirm('Apakah Anda yakin ingin menyelesaikan kuis sekarang? Anda tidak bisa kembali setelah ini.')) {
                    submitQuiz();
                }
            });

            // Initialize with start screen
            resetQuiz();
        });
    </script>
</body>
</html>