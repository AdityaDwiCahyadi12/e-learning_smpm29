<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar - E-Learning Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .logout-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .logout-card {
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
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
            <a href="tambah_anggota.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_anggota.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-user-plus w-5 mr-3"></i><span>Tambah Anggota</span>
            </a>

            <!-- Pembelajaran -->
            <p class="text-xs font-semibold text-gray-500 px-2 mb-2 mt-4">PEMBELAJARAN</p>
            <a href="forum_diskusi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-comments w-5 mr-3"></i><span>Forum Diskusi</span>
            </a>
            <a href="tambah_materi.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'tambah_materi.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-book-open w-5 mr-3"></i><span>Tambah Materi</span>
            </a>
            <a href="buat_kuis.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-question-circle w-5 mr-3"></i><span>Buat Kuis</span>
            </a>
            <a href="buat_tugas.php" class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 <?= $currentPage == 'buat_kuis.php' ? 'bg-gray-100 text-blue-600 font-semibold' : 'text-gray-700' ?>">
                <i class="fas fa-tasks w-5 mr-3"></i><span>Buat Tugas</span>
            </a>

            <!-- Logout -->
            <div class="border-t mt-4 pt-2">
                <a href="keluar.php" class="sidebar-item active flex items-center px-3 py-2 text-gray-700 rounded-lg mb-1">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i><span>Keluar</span>
                </a>
            </div>
        </div>
    </div>

        <!-- Main Content -->
        <div class="ml-64 w-full p-6 flex items-center justify-center">
            <div class="logout-container">
                <div class="logout-card bg-white rounded-lg shadow-sm p-8 text-center">
                    <div class="mb-6">
                        <i class="fas fa-sign-out-alt text-5xl text-blue-500 mb-4"></i>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Konfirmasi Keluar</h2>
                        <p class="text-gray-600">Apakah Anda yakin ingin keluar dari sistem?</p>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <a href="dashboard.php" class="bg-gray-200 text-gray-700 py-2 px-6 rounded-lg hover:bg-gray-300 transition duration-200">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                        <a href="../../index.php" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript functionality if needed
            console.log('Logout page loaded');
        });
    </script>
</body>
</html>