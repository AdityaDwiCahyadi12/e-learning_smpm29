<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Modal styles (kept as per original) */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.4s;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Custom animation for Hero Image: makes it gently float up and down */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); } /* Moves up 10px */
            100% { transform: translateY(0px); } /* Returns to original position */
        }
        .animate-float {
            animation: float 3s ease-in-out infinite; /* Apply the float animation */
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center gap-0">
                            <span class="text-xl font-bold text-gray-800">E-Learning School</span>
                        </div>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="tentang-kami.php" class="text-gray-600 hover:text-gray-900 px-3 py-2">Tentang Kami</a>
                            <a href="kontak.php" class="text-gray-600 hover:text-gray-900 px-3 py-2">Kontak</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-md">Masuk</a>
                    <a href="public/user/register.php" 
                    class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition-colors">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="text-white py-12" style="background: url('public/assets/images/bghomepage.png') center/cover no-repeat;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
                        Belajar Kapan Aja, Dimana Aja!
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Terbentuknya Karakter Peserta Didik Yang Berakhlak Mulia, Berprestasi Dan Soleh Berdasarkan Karakter Profil Pelajar Pancasila.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="login.php" class="bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 transition-colors" id="mulaiMembaca">
                            Mulai E-Learning
                        </a>
                        <a href="tentang-kami.php" class="bg-white text-indigo-600 border-2 border-indigo-600 px-8 py-3 rounded-lg hover:bg-indigo-50 transition-colors" id="pelajariLebih">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="relative flex justify-center items-center"> <img src="public/assets/images/vactor.png" 
                            alt="Hero Image" 
                            class="w-full h-96 object-contain rounded-lg shadow-2xl animate-float"> <div class="absolute -top-4 -right-4 w-20 h-20 bg-indigo-100 rounded-full opacity-50"></div>
                    <div class="absolute -bottom-4 -left-4 w-30 h-30 bg-indigo-100 rounded-full opacity-50"></div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-gray-800 text-white py-12">
        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
            <p class="text-gray-400">&copy; 2025 SMP Muhammadiyah 29 Sawangan. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>