<?php
session_start();
require_once 'app/config/database.php'; // Pastikan $pdo sudah terdefinisi di sini

$successMessage = "";
$errorMessage = "";

// Inisialisasi variabel form agar tidak undefined
$name = $email = $subject = $message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan trim input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validasi input
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $errorMessage = "Semua field harus diisi!";
    } else {
        try {
            // Persiapkan statement SQL untuk insert data
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $executed = $stmt->execute([$name, $email, $subject, $message]);

            if ($executed) {
                // Buat pesan WhatsApp dengan encoding URL
                $waNumber = "6287740532337"; // Ganti dengan nomor WhatsApp tujuan Anda

                $waMessage = "Pesan dari form kontak:%0A";
                $waMessage .= "Nama: " . rawurlencode($name) . "%0A";
                $waMessage .= "Email: " . rawurlencode($email) . "%0A";
                $waMessage .= "Subjek: " . rawurlencode($subject) . "%0A";
                $waMessage .= "Pesan: " . rawurlencode($message);

                // Redirect ke WhatsApp dengan pesan terisi
                header("Location: https://wa.me/$waNumber?text=$waMessage");
                exit;
            } else {
                $errorMessage = "Terjadi kesalahan saat menyimpan pesan.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Kesalahan database: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - E-Learning School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .custom-input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .custom-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .alert {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
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
                            <a href="tentang-kami.php" class="text-gray-600 hover:text-gray-900 px-3 py-2">Tentang Kami</a>
                            <a href="kontak.php" class="text-indigo-600 font-medium hover:text-indigo-700 px-3 py-2">Kontak</a>
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

    <!-- Header -->
    <div class="bg-indigo-600 text-white pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Hubungi Kami</h1>
                <p class="text-lg max-w-2xl mx-auto text-indigo-100">
                    Punya pertanyaan, saran, atau masukan? Kami siap mendengarkan dan membantu Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white p-8 rounded-xl shadow-md">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Kirim Pesan</h2>
                
                <?php if($successMessage): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded alert">
                    <p><?php echo $successMessage; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if($errorMessage): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded alert">
                    <p><?php echo $errorMessage; ?></p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo isset($name) ? $name : ''; ?>"
                               class="custom-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none"
                               placeholder="Masukkan nama lengkap Anda">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo isset($email) ? $email : ''; ?>"
                               class="custom-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none"
                               placeholder="Masukkan alamat email Anda">
                    </div>
                    <div class="mb-4">
                        <label for="subject" class="block text-gray-700 font-medium mb-2">Subjek</label>
                        <input type="text" id="subject" name="subject"
                               value="<?php echo isset($subject) ? $subject : ''; ?>"
                               class="custom-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none"
                               placeholder="Subjek pesan Anda">
                    </div>
                    <div class="mb-6">
                        <label for="message" class="block text-gray-700 font-medium mb-2">Pesan</label>
                        <textarea id="message" name="message" rows="5"
                                 class="custom-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none resize-none"
                                 placeholder="Tuliskan pesan Anda di sini"><?php echo isset($message) ? $message : ''; ?></textarea>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-6 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Kirim Pesan
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Informasi Kontak</h2>
                
                <div class="space-y-8">
                    <div class="flex items-start">
                        <div class="bg-indigo-100 rounded-full p-3 mr-4">
                            <i class="fas fa-map-marker-alt text-indigo-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Alamat</h3>
                            <p class="text-gray-600 mt-1">
                                Jalan Abdul Wahab Raya No.29<br>
                                RT.1/RW.8, Cinangka, Sawangan, Jl. Smpm 29 No.18<br>
                                Cinangka, Kec. Sawangan, Kota Depok, Jawa Barat 16516
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-indigo-100 rounded-full p-3 mr-4">
                            <i class="fas fa-phone-alt text-indigo-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Telepon</h3>
                            <p class="text-gray-600 mt-1">(021) 74708650</p>
                            <p class="text-gray-600">+62 812 9197 2102 (WhatsApp)</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-indigo-100 rounded-full p-3 mr-4">
                            <i class="fas fa-envelope text-indigo-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Email</h3>
                            <p class="text-gray-600 mt-1">smpmuhammadiyah2959@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-indigo-100 rounded-full p-3 mr-4">
                            <i class="fas fa-clock text-indigo-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Jam Operasional</h3>
                            <p class="text-gray-600 mt-1">Senin - Jumat: 07:00 - 17:00</p>
                            <p class="text-gray-600">Sabtu, Minggu & Hari Libur Nasional: Tutup</p>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="mt-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="https://www.instagram.com/smpmuh_29?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="bg-pink-600 text-white p-3 rounded-full hover:bg-pink-700 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/@smpmuh_29" class="bg-red-600 text-white p-3 rounded-full hover:bg-red-700 transition-colors">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-8 text-center">Pertanyaan Umum</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Bagaimana cara menggunakan E-Learning?</h3>
                    <p class="text-gray-600">Untuk mengikuti E-Learning, Anda perlu mendaftar terlebih dahulu, kemudian login ke akun Anda, dan pilih kursus yang ingin diikuti. Ikuti petunjuk selanjutnya untuk menyelesaikan proses pendaftaran.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Apakah layanan E-Learning berbayar?</h3>
                    <p class="text-gray-600">E-Learning menawarkan layanan gratis dan berbayar. Anda dapat mengakses sejumlah materi secara gratis, namun untuk akses penuh ke semua kursus, Anda perlu berlangganan paket premium kami.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Apakah E-Learning dapat diakses di perangkat mobile seperti smartphone atau tablet?</h3>
                    <p class="text-gray-600">Ya, E-Learning dapat diakses melalui perangkat mobile seperti smartphone atau tablet. Sehingga memungkinkan pengguna untuk belajar kapan saja dan di mana saja.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Bagaimana jika saya lupa mengerjakan E-Learning tepat waktu?</h3>
                    <p class="text-gray-600">Jika Anda terlambat menyelesaikan kursus, akan ada sanksi atau Anda tidak akan mendapatkan nilai sesuai dengan ketentuan yang berlaku. </p>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <p class="text-gray-600 mb-4">Masih punya pertanyaan lain?</p>
                <a href="#" class="inline-flex items-center bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    <i class="fas fa-question-circle mr-2"></i>
                    Lihat FAQ Lengkap
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