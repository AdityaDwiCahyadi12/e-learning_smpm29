<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - E-Learning School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .team-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
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
                            <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2">Beranda</a>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 px-3 py-2 font-medium">Tentang Kami</a>
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

    <!-- Hero Section -->
    <div class="pt-24 pb-12 bg-indigo-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Tentang SMP Muhammadiyah 29</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Selamat datang di Sekolah Menengah Pertama Muhammadiyah 29, tempat di mana belajar menjadi pengalaman yang menyenangkan dan penuh inspirasi.
                </p>
            </div>
        </div>
    </div>

    <!-- Visi dan Misi -->
    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="bg-indigo-50 p-8 rounded-xl">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-eye text-2xl text-indigo-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Visi Kami</h2>
                    <p class="text-gray-600">
                    Terbentuknya Karakter Peserta Didik Yang Berakhlak Mulia, Berprestasi Dan Soleh Berdasarkan Karakter Profil Pelajar Pancasila.
                    </p>
                </div>
                <div class="bg-indigo-50 p-8 rounded-xl">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-2xl text-indigo-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Misi Kami</h2>
                    <p class="text-gray-600">
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Menyelenggarakan pembelajaran untuk peningkatan kualitas keislaman dan akhlaqul karimah.</li>
                            <li>Membiasakan warga sekolah untuk menghayati dan mengamalkan nilai nilai Pancasila.</li>
                            <li>Melaksanakan pembelajaran dan bimbingan yang efektif dan kreatif.</li>
                            <li>Mengembangkan pembelajaran siswa dalam keilmuan dan tekhnologi.</li>
                            <li>Mempersiapkan siswa agar mempunyai kesadaran, daya juang tinggi serta istiqomah dalam bersikap dan berkarakter.</li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sejarah Kami -->
    <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">Perjalanan Kami</h2>
                <p class="text-xl text-gray-600 mt-2">Bagaimana Sekolah Menengah Pertama Muhammadiyah 29 dimulai dan berkembang</p>
            </div>
            
            <div class="relative">
                <!-- Timeline -->
                <div class="hidden md:block absolute h-full w-0.5 bg-indigo-200 left-1/2 transform -translate-x-1/2"></div>
                
                <!-- Timeline Items -->
                <div class="space-y-16">
                    <!-- Item 1 -->
                    <div class="relative flex items-center justify-between md:justify-normal">
                        <div class="md:w-1/2 md:pr-8 text-right hidden md:block"></div>
                        <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center justify-center">
                            <div class="h-8 w-8 rounded-full border-4 border-indigo-500 bg-white"></div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md md:w-1/2 md:ml-8">
                            <h3 class="text-xl font-bold text-gray-900">1985</h3>
                            <p class="text-gray-600 mt-2">
                                SMP Muhammadiyah 29 Sawangan didirikan pada tahun 1985, dengan visi dan misi untuk mencetak generasi unggul yang berakhlak mulia, cerdas, dan siap menghadapi tantangan masa depan. Kami berkomitmen untuk memberikan pendidikan berkualitas dengan nilai-nilai Islam yang kuat.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="relative flex items-center justify-between md:justify-normal flex-row-reverse md:flex-row">
                        <div class="md:w-1/2 md:pr-8 text-right">
                            <div class="bg-white p-6 rounded-xl shadow-md">
                                <h3 class="text-xl font-bold text-gray-900">2007</h3>
                                <p class="text-gray-600 mt-2">
                                    Mengalami perkembangan signifikan, dengan berbagai peningkatan baik dalam segi fasilitas maupun kualitas pembelajaran.
                                </p>
                            </div>
                        </div>
                        <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center justify-center">
                            <div class="h-8 w-8 rounded-full border-4 border-indigo-500 bg-white"></div>
                        </div>
                        <div class="md:w-1/2 md:ml-8 hidden md:block"></div>
                    </div>
                    
                    <!-- Item 3 -->
                    <div class="relative flex items-center justify-between md:justify-normal">
                        <div class="md:w-1/2 md:pr-8 text-right hidden md:block"></div>
                        <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center justify-center">
                            <div class="h-8 w-8 rounded-full border-4 border-indigo-500 bg-white"></div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md md:w-1/2 md:ml-8">
                            <h3 class="text-xl font-bold text-gray-900">2015</h3>
                            <p class="text-gray-600 mt-2">
                                Memiliki gedung sekolah berlantai 2 yang modern dan fasilitas lengkap, serta dilengkapi dengan masjid untuk mendukung kegiatan ibadah.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Item 4 -->
                    <div class="relative flex items-center justify-between md:justify-normal flex-row-reverse md:flex-row">
                        <div class="md:w-1/2 md:pr-8 text-right">
                            <div class="bg-white p-6 rounded-xl shadow-md">
                                <h3 class="text-xl font-bold text-gray-900">Sekarang</h3>
                                <p class="text-gray-600 mt-2">
                                    Seiring dengan perkembangan yang pesat, saat ini SMP Muhammadiyah 29 Sawangan telah memiliki 445 siswa yang aktif belajar dan berprestasi, menjadikan sekolah ini semakin dikenal sebagai lembaga pendidikan unggulan di kota depok.
                                </p>
                            </div>
                        </div>
                        <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center justify-center">
                            <div class="h-8 w-8 rounded-full border-4 border-indigo-500 bg-white"></div>
                        </div>
                        <div class="md:w-1/2 md:ml-8 hidden md:block"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="py-16 bg-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Bergabunglah dengan SMP Muhammadiyah 29 Sawangan</h2>
                <p class="text-xl text-indigo-100 mb-8 max-w-3xl mx-auto">
                    Terbentuknya Karakter Peserta Didik Yang Berakhlak Mulia, Berprestasi Dan Soleh Berdasarkan Karakter Profil Pelajar Pancasila.
                </p>
                <a href="public/user/register.php" class="bg-white text-indigo-600 px-8 py-3 rounded-lg hover:bg-indigo-50 transition-colors font-medium">
                    Daftar Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; 2025 SMP Muhammadiyah 29 Sawangan. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>