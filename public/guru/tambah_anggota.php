<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggota - E-Learning Class</title>
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
        .info-card {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
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

                <!-- Manajemen -->
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">MANAJEMEN</p>
                <a href="profil.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'profil.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-user w-5 mr-3"></i><span>Profil</span>
                </a>
                <a href="tambah_anggota.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-user-plus w-5 mr-3"></i><span>Tambah Anggota</span>
                </a>

                <!-- Pembelajaran -->
                <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
                <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'forum_diskusi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
                </a>
                <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_materi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-book-open w-5 mr-3"></i><span>Tambah Materi</span>
                </a>
                <a href="buat_kuis.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-question-circle w-5 mr-3"></i><span>Buat Kuis</span>
                </a>
                <a href="buat_tugas.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_tugas.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                    <i class="fas fa-tasks w-5 mr-3"></i><span>Buat Tugas</span>
                </a>

                <!-- Logout -->
                <div class="border-t mt-4 pt-2">
                    <a href="keluar.php" class="sidebar-item flex items-center px-3 py-2 text-gray-700 rounded-lg">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i><span>Keluar</span>
                    </a>
                </div>
            </div>
        </div>


        <!-- Main Content -->
        <div class="ml-64 w-full p-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Tambah Anggota</h1>
                <p class="text-sm text-gray-600">Form untuk menambahkan anggota baru</p>
            </div>
            
            <!-- Form for Adding Member -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form action="proses_tambah_anggota.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="full_name" class="block text-sm font-semibold text-gray-600 mb-1">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="mb-4">
                            <label for="username" class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                            <input type="text" id="username" name="username" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Masukkan username" required>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                            <input type="email" id="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Masukkan email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Minimal 8 karakter" required minlength="8">
                                <button type="button" class="absolute right-3 top-3 text-gray-500 hover:text-gray-700" onclick="togglePasswordVisibility()">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-semibold text-gray-600 mb-1">Role</label>
                            <select id="role" name="role" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                                <option value="" disabled selected>Pilih role</option>
                                <option value="user">Siswa</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button" class="bg-gray-200 text-gray-800 py-2 px-6 rounded-lg hover:bg-gray-300 mr-3 transition">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-user-plus mr-2"></i>
                            Tambah Anggota
                        </button>
                    </div>
                </form>
            </div>

            <!-- Information Section at Bottom -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="info-card p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Informasi</h2>
                    <h3 class="font-medium text-gray-800 mb-2">Petunjuk Pendaftaran Anggota</h3>
                    <ul class="list-disc pl-5 space-y-2 text-sm text-gray-600">
                        <li>Pastikan data yang dimasukkan akurat dan lengkap</li>
                        <li>Password harus minimal 8 karakter</li>
                        <li>Email yang digunakan harus unik dan belum terdaftar</li>
                        <li>Setelah terdaftar, anggota akan memiliki akses sebagai "user"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('#password + button i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>