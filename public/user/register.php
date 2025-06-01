<?php
session_start();
require_once '../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $username = strtolower(str_replace(' ', '', $full_name)) . rand(100, 999); // Generate username automatically

    // Cek apakah email sudah digunakan
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email sudah terdaftar!';
        header("Location: register.php");
        exit();
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $password, $email, $full_name, 'siswa'])) {
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            header("Location: ../../login.php");
            exit();
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan data!';
            header("Location: register.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Learning School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        /* Custom styles here */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .form-container {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        .input-field {
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }

        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(90deg, #0ea5e9, #3b82f6);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #0284c7, #2563eb);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .form-icon {
            color: #0ea5e9;
            transition: all 0.3s;
        }

        .input-group:focus-within .form-icon {
            color: #3b82f6;
            transform: scale(1.1);
        }
    </style>
</head>
<body class="text-white">

    <!-- Navbar -->
    <nav class="bg-white shadow-md w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="../../index.php" class="text-gray-500 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium">Beranda</a>
                    <a href="../../login.php" class="bg-primary-50 text-primary-600 hover:bg-primary-100 px-4 py-2 rounded-md text-sm font-medium">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="py-8 px-4 flex justify-center items-center min-h-[calc(100vh-64px)]">
        <div class="max-w-4xl w-full flex flex-col md:flex-row rounded-3xl shadow-custom overflow-hidden">
            <div class="w-full md:w-7/12 bg-white p-8 text-gray-800">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Buat Akun Baru</h2>
                    <p class="text-gray-500 text-sm">Lengkapi data diri Anda untuk memulai</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <p><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <p><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate class="space-y-5">
                    <div class="input-group">
                        <label class="block mb-2 text-gray-700 font-medium text-sm">
                            <i class="fas fa-user form-icon mr-1"></i> Nama Lengkap
                        </label>
                        <input type="text" name="nama" required class="input-field w-full px-4 py-3 rounded-lg focus:outline-none" placeholder="Masukkan nama lengkap Anda">
                    </div>

                    <div class="input-group">
                        <label class="block mb-2 text-gray-700 font-medium text-sm">
                            <i class="fas fa-envelope form-icon mr-1"></i> Email
                        </label>
                        <input type="email" name="email" required class="input-field w-full px-4 py-3 rounded-lg focus:outline-none" placeholder="email@example.com">
                    </div>

                    <div class="input-group">
                        <label class="block mb-2 text-gray-700 font-medium text-sm">
                            <i class="fas fa-lock form-icon mr-1"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" required id="password" class="input-field w-full px-4 py-3 rounded-lg focus:outline-none" placeholder="Buat password yang kuat">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg">
                            <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600 text-sm">Sudah punya akun? 
                        <a href="../../login.php" class="text-primary-600 hover:text-primary-700 font-semibold">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>