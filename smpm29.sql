-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Jun 2025 pada 07.27
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smpm29`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(3, 'Septian', 'septian@gmail.com', 'Kesulitan Untuk Login', 'Kesulitan untuk melakukan login', '2025-05-12 02:35:46'),
(4, 'Gilang Kusay', 'gilangkusay@gmail.com', 'Kesulitan Untuk Login', 'Kesulitan untuk login', '2025-05-14 23:30:51'),
(5, 'Gilang Kusay', 'gilangkusay@gmail.com', 'Kesulitan Untuk Login', 'Login Field', '2025-05-21 07:57:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `teacher_id`) VALUES
(1, 'Bahasa Indonesia', NULL, 12),
(2, 'Penjas', NULL, 12),
(3, 'Matematika', NULL, NULL),
(4, 'Bahasa Jepang', NULL, NULL),
(5, 'Kemuhammadiyahan', NULL, NULL),
(6, 'Pendidikan Kewarganegaraan', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `discussions`
--

CREATE TABLE `discussions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `discussions`
--

INSERT INTO `discussions` (`id`, `course_id`, `user_id`, `title`, `content`, `created_at`, `file_path`, `file_name`, `file_type`) VALUES
(2, NULL, 1, 'Matimatika', 'Soal Perbandingan Dalam sebuah kelas, perbandingan jumlah siswa laki-laki dan perempuan adalah 3:4. Jika jumlah siswa perempuan ada 36 orang, berapa jumlah siswa laki-laki?', '2025-04-26 12:37:23', NULL, NULL, NULL),
(4, NULL, 1, 'Bahas Indonesia', 'Mari kita diskusikan tentang bahasa Indonesia secara umum, ya! Bisa mulai dari aspek tata bahasa, penggunaan kata yang tepat, atau bahkan perubahan bahasa yang terjadi dalam kehidupan sehari-hari.', '2025-04-26 22:48:09', NULL, NULL, NULL),
(5, NULL, 1, 'Kemuhammadiyahan: Peran dan Tantangan dalam Masyarakat Modern', 'Kemuhammadiyahan merupakan ajaran yang diusung oleh Muhammadiyah, salah satu organisasi Islam terbesar di Indonesia, yang didirikan oleh KH. Ahmad Dahlan pada tahun 1912. Organisasi ini memiliki visi untuk menegakkan ajaran Islam yang murni, memperjuangkan pendidikan, dan meningkatkan kesejahteraan umat. Diskusi ini bertujuan untuk menggali lebih dalam mengenai peran Muhammadiyah dalam masyarakat modern serta tantangan-tantangan yang dihadapinya.', '2025-04-28 06:05:00', NULL, NULL, NULL),
(63, NULL, 12, 'Memahami Pertidaksamaan Linear Satu Variabel dalam Kehidupan Sehari-hari', 'Pada kesempatan kali ini, kita akan memulai diskusi mengenai salah satu topik penting dalam Matematika, yaitu Pertidaksamaan Linear Satu Variabel. Materi ini merupakan bagian dasar dari aljabar dan sangat bermanfaat untuk menyelesaikan masalah sehari-hari yang berkaitan dengan batasan atau kondisi tertentu.', '2025-05-31 06:28:19', 'uploads/683aa183457126.10973552.pdf', 'Matematika.pdf', 'application/pdf');

-- --------------------------------------------------------

--
-- Struktur dari tabel `download_logs`
--

CREATE TABLE `download_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `downloaded_at` datetime NOT NULL,
  `ip_address` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `materials`
--

INSERT INTO `materials` (`id`, `title`, `category`, `content`, `file_path`, `created_at`) VALUES
(40, ' Introducing Oneself and Others', 'Bahasa Inggris', 'Materi ini membahas cara memperkenalkan diri dan orang lain dalam bahasa Inggris. Siswa akan mempelajari ungkapan sapaan, perkenalan formal dan informal, serta kosakata dasar seperti nama, asal, pekerjaan, dan hobi. Dilengkapi dengan contoh percakapan sederhana agar siswa mampu berlatih berbicara secara percaya diri dalam situasi sehari-hari.', '68392faf95ad26.90544246.pdf', '2025-05-30 04:10:24'),
(41, 'Teks Eksposisi: Pengertian, Struktur, dan Contohnya', 'Bahasa Indonesia', 'Materi ini membahas tentang teks eksposisi, yaitu jenis teks yang bertujuan menyampaikan pendapat atau gagasan disertai argumen yang logis. Siswa akan mempelajari pengertian teks eksposisi, struktur teks (tesis, argumentasi, dan penegasan ulang), serta kaidah kebahasaan yang digunakan. Dilengkapi dengan contoh teks eksposisi agar siswa dapat memahami dan menulisnya secara mandiri.', '68392fcfdbc7e1.86708742.pdf', '2025-05-30 04:10:55'),
(42, 'Bilangan Bulat', 'Matematika', 'Jadi, bilangan bulat adalah konsep dasar yang sangat berguna dalam kehidupan sehari-hari dan pelajaran matematika. Dengan memahami bilangan bulat, kita bisa lebih mudah mengerjakan soal dan menghadapi berbagai masalah yang berkaitan dengan angka.', '683a5d2d010774.63927911.pdf', '2025-05-31 01:36:45'),
(43, 'Kemuhammadiyahan', 'Pendidikan Agama Islam / PAI', 'Kemuhammadiyahan adalah pemahaman dan pengamalan ajaran serta nilai-nilai organisasi Muhammadiyah. Muhammadiyah sendiri adalah salah satu organisasi Islam terbesar di Indonesia yang fokus pada pendidikan, dakwah, dan sosial kemasyarakatan berdasarkan Al-Qur’an dan Sunnah.', '683a5d5a665c15.24233715.pdf', '2025-05-31 01:37:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `option_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(1, 31, 'Dalam permainan sepak bola, jumlah pemain dalam satu tim adalah...', '7 orang', '9 orang', '11 orang', '16 orang', 'C'),
(4, 33, 'Choose the right question: ____ do you like to read books?', 'What', 'When', 'Where', 'Why', 'A'),
(5, 34, 'Tentukan hasil dari 7 x (6 + 4) ÷ 2!', '35', '40', '45', '60', 'B'),
(7, 36, 'Berapakah hasil dari 12 ÷ 3?', '1', '7', '4', '8', 'C'),
(8, 39, 'Siapa Presiden Indonesia saat ini?', ' Joko Widodo', 'Megawati', 'H Miuun', 'Prabowo Subianto', 'D');

-- --------------------------------------------------------

--
-- Struktur dari tabel `question_options`
--

CREATE TABLE `question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `title`, `duration_minutes`, `start_time`, `end_time`) VALUES
(31, 2, 'Penjas', 30, '2025-04-27 19:34:00', '2025-05-01 07:34:00'),
(33, 3, 'Kuis Bahasa Inggris: Uji Kemampuan Dasar', 30, '2025-05-09 13:10:00', '2025-06-12 01:10:00'),
(34, 4, 'Kuis Matematika: Pengetahuan Dasar Matematika', 30, '2025-04-29 14:33:00', '2025-04-30 12:33:00'),
(36, 1, 'Matimatika', 30, '2025-05-29 09:43:00', '2025-06-07 09:43:00'),
(39, 0, 'PKN (Pendidikan Kewarganegaraan)', 30, '2025-05-29 10:00:00', '2025-06-07 10:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `correct_answer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `correct_answers` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `replies`
--

INSERT INTO `replies` (`id`, `discussion_id`, `user_id`, `content`, `created_at`, `file_path`, `file_name`, `file_type`) VALUES
(2, 4, 3, 'Tata bahasa Indonesia tidak serumit bahasa lainnya, seperti bahasa Inggris atau Jerman, yang memiliki banyak aturan gramatikal yang perlu dipelajari. Misalnya, dalam bahasa Indonesia kita lebih banyak menggunakan urutan Subjek-Predikat-Objek (S-P-O) dalam kalimat yang sederhana, seperti “Saya (S) makan (P) nasi (O).” Namun, perubahan urutan ini dapat digunakan untuk memberi penekanan atau menunjukkan konteks tertentu.', '2025-05-05 10:45:57', NULL, NULL, NULL),
(3, 5, 6, 'Assalamualaikum Izin Berdiskusi.\r\nKemuhammadiyahan memang memiliki peran yang sangat penting dalam perkembangan umat Islam di Indonesia, terutama dalam konteks pendidikan dan sosial. Muhammadiyah, yang didirikan oleh KH. Ahmad Dahlan, memiliki visi untuk mengusung ajaran Islam yang murni dengan penekanan pada aspek pendidikan, kesehatan, dan sosial. Mereka menekankan pentingnya pemahaman Islam yang moderat dan rasional, yang menyesuaikan dengan kebutuhan masyarakat modern.', '2025-05-06 11:02:47', NULL, NULL, NULL),
(24, 63, 3, 'Assalamu’alaikum Pak Izin Berdiskusi.\r\n\r\nPertidaksamaan linear satu variabel adalah bentuk pernyataan matematika yang menyatakan hubungan ketidaksamaan antara dua ekspresi dengan hanya satu variabel berpangkat satu. Contohnya adalah 2x + 3 < 7.', '2025-05-31 06:32:23', 'uploads/683aa277bd9ae9.88097039.pdf', 'Jawab Matematika.pdf', 'application/pdf');

-- --------------------------------------------------------

--
-- Struktur dari tabel `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `feedback` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'submitted',
  `grade` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `submissions`
--

INSERT INTO `submissions` (`id`, `task_id`, `student_id`, `file_path`, `submitted_at`, `feedback`, `status`, `grade`) VALUES
(12, 9, 3, '', '2025-05-31 10:17:50', NULL, 'submitted', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `submission_attachments`
--

CREATE TABLE `submission_attachments` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `submission_files`
--

CREATE TABLE `submission_files` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `submission_files`
--

INSERT INTO `submission_files` (`id`, `submission_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(10, 12, 'submission_1748686670.pdf', 'user/uploads/submissions/9/3/submission_1748686670.pdf', '2025-05-31 10:17:50'),
(11, 1, 'AI95034FU.pdf', 'uploads/submissions/683b02d5d689f_AI95034FU.pdf', '2025-05-31 13:23:33'),
(12, 2, '1631-3231-1-SM.pdf', 'uploads/submissions/683bc23c473a9_1631-3231-1-SM.pdf', '2025-06-01 03:00:12'),
(13, 3, 'AI95034FU.pdf', 'uploads/submissions/683bc4bd43274_AI95034FU.pdf', '2025-06-01 03:10:53'),
(14, 4, '1631-3231-1-SM.pdf', 'uploads/submissions/683bc537d110b_1631-3231-1-SM.pdf', '2025-06-01 03:12:55'),
(15, 5, 'AI95034FU.pdf', 'uploads/submissions/683bcb09ce085_AI95034FU.pdf', '2025-06-01 03:37:45'),
(16, 6, '1631-3231-1-SM.pdf', 'uploads/submissions/683bd2ce287db_1631-3231-1-SM.pdf', '2025-06-01 04:10:54'),
(17, 7, 'AI95034FU.pdf', 'uploads/submissions/683bd63c2dee6_AI95034FU.pdf', '2025-06-01 04:25:32'),
(18, 8, 'AI95034FU.pdf', 'uploads/submissions/683bd683b1b88_AI95034FU.pdf', '2025-06-01 04:26:43'),
(19, 9, '3590-9138-1-PB.pdf', 'uploads/submissions/683bd9fc217ec_3590-9138-1-PB.pdf', '2025-06-01 04:41:32'),
(20, 10, 'Matematika.pdf', 'uploads/submissions/683bddecd111c_Matematika.pdf', '2025-06-01 04:58:20'),
(21, 11, 'B Indonesia.pdf', 'uploads/submissions/683be09c3f633_B Indonesia.pdf', '2025-06-01 05:09:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `max_score` int(11) DEFAULT 100,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deadline` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tasks`
--

INSERT INTO `tasks` (`id`, `course_id`, `teacher_id`, `title`, `description`, `due_date`, `max_score`, `created_at`, `updated_at`, `deadline`) VALUES
(4, 3, 12, 'Operasi Hitung Campuran Bilangan Bulat', 'Selesaikan soal-soal operasi hitung campuran bilangan bulat berikut ini dengan teliti. Tugas ini bertujuan untuk menguji pemahaman kalian terhadap penjumlahan, pengurangan, perkalian, dan pembagian bilangan bulat.\r\nKerjakan dengan rapi di buku tugas dan unggah hasilnya dalam format PDF atau foto yang jelas.', '2025-06-07 11:55:00', 100, '2025-06-01 11:56:07', '2025-06-01 11:56:07', NULL),
(5, 4, 12, 'Mengenal Hiragana dan Kosakata Dasar Bahasa Jepang', 'Pelajari tabel hiragana dan 10 kosakata dasar dalam Bahasa Jepang. Kemudian, salin dan tulis tangan huruf-huruf hiragana tersebut beserta artinya. Setelah itu, buat 5 kalimat sederhana menggunakan kosakata yang telah dipelajari.', '2025-06-07 12:00:00', 100, '2025-06-01 12:01:06', '2025-06-01 12:01:06', NULL),
(6, 5, 12, 'Sejarah dan Peran Muhammadiyah dalam Pendidikan', 'Bacalah materi tentang sejarah Muhammadiyah dan peranannya dalam dunia pendidikan. Setelah itu, kerjakan tuganya', '2025-06-07 12:02:00', 100, '2025-06-01 12:02:35', '2025-06-01 12:02:35', NULL),
(7, 1, 12, 'Menganalisis Teks Deskripsi', 'Bacalah salah satu contoh teks deskripsi yang ada di buku paket atau internet (boleh teks tentang tempat, hewan, atau benda). ', '2025-06-07 12:03:00', 100, '2025-06-01 12:03:51', '2025-06-01 12:03:51', NULL),
(8, 6, 12, 'PKN', 'Teks deskripsi adalah tulisan yang menjelaskan secara rinci tentang suatu objek, seperti tempat, hewan, atau benda, sehingga pembaca dapat membayangkan objek tersebut dengan jelas. Dalam tugas ini, kamu diminta untuk membaca satu contoh teks deskripsi dari buku paket atau internet, lalu menganalisis isi teks tersebut.', '2025-06-07 12:06:00', 100, '2025-06-01 12:06:54', '2025-06-01 12:10:21', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `task_attachments`
--

INSERT INTO `task_attachments` (`id`, `task_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(30, 30, '1631-3231-1-SM.pdf', '/smpm29/public/guru/uploads/tasks/30/683982b4b687c_1631-3231-1-SM.pdf', '2025-05-30 10:04:36'),
(44, 9, 'AI95034FU.pdf', '/smpm29/public/guru/uploads/tasks/9/683acd8c4e1ea_AI95034FU.pdf', '2025-05-31 09:36:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `task_files`
--

CREATE TABLE `task_files` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `task_files`
--

INSERT INTO `task_files` (`id`, `task_id`, `file_path`, `file_name`, `uploaded_at`) VALUES
(8, 11, 'uploads/tasks/683bcd96f010c_AI95034FU.pdf', 'AI95034FU.pdf', '2025-06-01 10:48:39'),
(13, 4, 'uploads/tasks/683bdd67d0aee_Matematika.pdf', 'Matematika.pdf', '2025-06-01 11:56:07'),
(14, 5, 'uploads/tasks/683bde92ec00e_B Jepang.pdf', 'B Jepang.pdf', '2025-06-01 12:01:06'),
(15, 6, 'uploads/tasks/683bdeeb809cf_Kemuhammadiyahan.pdf', 'Kemuhammadiyahan.pdf', '2025-06-01 12:02:35'),
(16, 7, 'uploads/tasks/683bdf375d37d_B Indonesia.pdf', 'B Indonesia.pdf', '2025-06-01 12:03:51'),
(17, 8, 'uploads/tasks/683bdfee6fe0d_PKN.pdf', 'PKN.pdf', '2025-06-01 12:06:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `task_submissions`
--

CREATE TABLE `task_submissions` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `grade` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `task_submissions`
--

INSERT INTO `task_submissions` (`id`, `task_id`, `student_id`, `notes`, `submitted_at`, `grade`, `feedback`, `graded_at`) VALUES
(6, 11, 3, 'atut', '2025-06-01 11:10:54', NULL, NULL, NULL),
(10, 4, 3, 'Assalamualaikum.\r\nBerikut ini saya kumpulkan tugas Matematika sesuai yang diberikan.', '2025-06-01 11:58:20', 85, 'Cukup Baik Jawabanya', '2025-06-01 11:59:07'),
(11, 7, 6, 'Assalamualaikum\r\nSaya ingin mengumpulkan tugas menganalisis teks deskripsi', '2025-06-01 12:09:48', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `uploads`
--

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `uploads`
--

INSERT INTO `uploads` (`id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_at`, `updated_at`) VALUES
(11, 'AI95034FU.pdf', 'uploads/submissions/683bcb09ce085_AI95034FU.pdf', 'application/pdf', 1004860, '2025-06-01 03:37:45', '2025-06-01 03:37:45'),
(12, 'AI95034FU.pdf', 'uploads/submissions/683bd1a4170ce_AI95034FU.pdf', 'application/pdf', 1004860, '2025-06-01 04:05:56', '2025-06-01 04:05:56'),
(13, '1631-3231-1-SM.pdf', 'uploads/submissions/683bd2ce287db_1631-3231-1-SM.pdf', 'application/pdf', 946640, '2025-06-01 04:10:54', '2025-06-01 04:10:54'),
(14, 'AI95034FU.pdf', 'uploads/submissions/683bd63c2dee6_AI95034FU.pdf', 'application/pdf', 1004860, '2025-06-01 04:25:32', '2025-06-01 04:25:32'),
(15, 'AI95034FU.pdf', 'uploads/submissions/683bd683b1b88_AI95034FU.pdf', 'application/pdf', 1004860, '2025-06-01 04:26:43', '2025-06-01 04:26:43'),
(16, '1631-3231-1-SM.pdf', 'uploads/submissions/683bd96981d0b_1631-3231-1-SM.pdf', 'application/pdf', 946640, '2025-06-01 04:39:05', '2025-06-01 04:39:05'),
(17, '3590-9138-1-PB.pdf', 'uploads/submissions/683bd9fc217ec_3590-9138-1-PB.pdf', 'application/pdf', 573487, '2025-06-01 04:41:32', '2025-06-01 04:41:32'),
(18, 'Matematika.pdf', 'uploads/submissions/683bddecd111c_Matematika.pdf', 'application/pdf', 11385458, '2025-06-01 04:58:20', '2025-06-01 04:58:20'),
(19, 'B Indonesia.pdf', 'uploads/submissions/683be09c3f633_B Indonesia.pdf', 'application/pdf', 11415860, '2025-06-01 05:09:48', '2025-06-01 05:09:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL DEFAULT 'siswa',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `created_at`) VALUES
(1, 'adityadwicahyadi', '$2y$10$/xxh5zf4L2s4FUg5G/LMZuUdEeQnSC79RmKbykvZCuZdAz7yOthF2', 'adityadwicahyadi@gmail.com', 'Aditya Dwi Cahyadi', 'admin', '2025-01-18 09:49:37'),
(2, 'ahmadwahyu', '$2y$10$laL7XPHk3y6akKb1KruZ0OouHsDlSGPWZJnuyFJ/584qiSLaiS0d2', 'ahmadwahyu@gmail.com', 'Ahmad Wahyu', 'guru', '2025-01-21 06:58:06'),
(3, 'raritaoktaviani', '$2y$10$qbbm4UMngS748r4TX0Fh9.5/EzlBWBry7Cu8fI6pUndi/Se99kXVi', 'raritaoktaviani@gmail.com', 'Rarita Oktaviani', 'siswa', '2025-02-11 07:50:22'),
(5, 'gilangkusay', '$2y$10$Qb/lOAghBl/n5G5Km1vF9.EeFUQ6BpsnPXGhXzTJVi6T7EHZNwMKK', 'gilangkusay@gmail.com', 'Gilang Kusay', 'siswa', NULL),
(6, 'zaydanalhafis', '$2y$10$mMuB0WZPabyPCyHYt7YjMe0MBjTMsVpO1Xn87BrJQK7Wm5p/eWpT6', 'zaydanalhafis@gmail.com', 'Zaydan Alhafis', 'siswa', '2025-05-05 09:06:38'),
(7, 'dewipratama', '$2y$10$hTFo4QordG639QCi83y3i.vPxYhbTRnGmNwBjMBYzg8CJcZkFyPe6', 'dewipratama@gmail.com', 'Dewi Pratama', '', NULL),
(8, 'panjiramadhan', '$2y$10$0KdXMIt7y.wVOt1EggZ52OhBxhVKYyVbZ9LmF5qpjzg4FJkV/p1p6', 'panjiramadhan@gmail.com', 'Panji Ramaadhan', '', NULL),
(9, 'cakrahusam', '$2y$10$8YMCPRDkmt1N81ZCymAyReI2Ud4ruGTKqGUvXOdCEHjxSox6paGPO', 'cakrahusam@gmail.com', 'Cakra Husam', 'siswa', '2025-01-31 18:47:42'),
(10, 'kevinsanjaya', '$2y$10$KJW4k455bVy23bDSwkMoueLPkaawsvW6oESGTnWCp86A.UVGZ0tx6', 'kevinsanjaya@gmail.com', 'Kevin Sanjaya', 'siswa', '2025-05-12 09:46:48'),
(11, 'divarahma', '$2y$10$o8BrDfvEWSvU7/dSMPMH9OX.IDKmmpCbLt4opPagQDNSZBu92JfMG', 'divarahma@gmail.com', 'Diva Rahma', 'siswa', '2025-05-13 18:51:11'),
(12, 'solehsofiyan', '$2y$10$zc1SQUYc6V.jaJzv98Tk0OvD/BYOninlPAfiA.tjVHutCLvju2cIG', 'solehsofiyan@gmail.com', 'Soleh Sofiyan', 'guru', '2025-03-04 09:19:42'),
(13, 'restiamelia', '$2y$10$NPw2O2TpUIRUMB9kWe2j7eDZilVKo/yX01enIJF9NGEoPa.LlRunO', 'restiamelia@gmail.com', 'Resti Amelia', 'siswa', '2025-05-21 14:59:06'),
(14, 'pegimaulida', '$2y$10$vh0.f4nKs7lr2MQth7ugd.cFOL6CWiODOICc40aPmm7/VEVTwMzxK', 'pegimaulida@gmail.com', 'Pegi Maulida', 'siswa', '2025-05-29 09:26:22');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indeks untuk tabel `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indeks untuk tabel `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `download_logs`
--
ALTER TABLE `download_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indeks untuk tabel `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indeks untuk tabel `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indeks untuk tabel `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indeks untuk tabel `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indeks untuk tabel `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discussion_id` (`discussion_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`task_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indeks untuk tabel `submission_attachments`
--
ALTER TABLE `submission_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indeks untuk tabel `submission_files`
--
ALTER TABLE `submission_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indeks untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indeks untuk tabel `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indeks untuk tabel `task_files`
--
ALTER TABLE `task_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indeks untuk tabel `task_submissions`
--
ALTER TABLE `task_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indeks untuk tabel `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `discussions`
--
ALTER TABLE `discussions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `download_logs`
--
ALTER TABLE `download_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `question_options`
--
ALTER TABLE `question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT untuk tabel `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `submission_attachments`
--
ALTER TABLE `submission_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `submission_files`
--
ALTER TABLE `submission_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `task_files`
--
ALTER TABLE `task_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `task_submissions`
--
ALTER TABLE `task_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Ketidakleluasaan untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `discussions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `download_logs`
--
ALTER TABLE `download_logs`
  ADD CONSTRAINT `download_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `download_logs_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Ketidakleluasaan untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

--
-- Ketidakleluasaan untuk tabel `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`),
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`),
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `fk_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `submission_attachments`
--
ALTER TABLE `submission_attachments`
  ADD CONSTRAINT `submission_attachments_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `task_submissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `task_files`
--
ALTER TABLE `task_files`
  ADD CONSTRAINT `task_files_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `task_submissions`
--
ALTER TABLE `task_submissions`
  ADD CONSTRAINT `task_submissions_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
