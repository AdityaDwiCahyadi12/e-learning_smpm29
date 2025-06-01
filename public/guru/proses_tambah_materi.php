<?php
session_start();

// Koneksi database
$host = getenv('MYSQLHOST') ?: 'localhost';  // fallback kalau env gak ada
$dbname = getenv('MYSQL_DATABASE') ?: 'smpm29';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'] ?? null;
    $new_kategori = $_POST['new_kategori'] ?? null;
    $konten = $_POST['konten'];

    // Tentukan kategori
    $kategori = !empty($new_kategori) ? $new_kategori : $kategori;

    // Proses file upload
    $file_path = null;
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_materi']['tmp_name'];
        $file_name = basename($_FILES['file_materi']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];

        // Validasi ekstensi file
        if (in_array($file_ext, $allowed_extensions)) {
            // Buat nama file unik
            $file_name_new = uniqid('', true) . '.' . $file_ext;
            
            // Daftar folder tujuan
            $upload_dirs = [
                'C:/xampp/htdocs/smpm29/public/guru/uploads',
                'C:/xampp/htdocs/smpm29/public/guru/uploads/materials',
                'C:/xampp/htdocs/smpm29/public/uploads/materials',
                'C:/xampp/htdocs/smpm29/public/user/uploads/materials'
            ];
            
            $upload_success = true;
            
            // Unggah ke semua folder
            foreach ($upload_dirs as $upload_dir) {
                // Buat folder jika belum ada
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $destination = $upload_dir . '/' . $file_name_new;
                
                if (!copy($file_tmp, $destination)) {
                    $upload_success = false;
                    break;
                }
            }
            
            if ($upload_success) {
                $file_path = $file_name_new;
            } else {
                $_SESSION['message'] = "Gagal mengunggah file ke semua folder.";
                $_SESSION['message_type'] = "error";
                header("Location: tambah_materi.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Format file tidak didukung. Harap unggah file PDF, DOC, PPT, JPG, PNG, atau MP4.";
            $_SESSION['message_type'] = "error";
            header("Location: tambah_materi.php");
            exit();
        }
    }

    // Simpan data materi ke database
    $stmt = $mysqli->prepare("INSERT INTO materials (title, category, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $judul, $kategori, $konten, $file_path);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Materi berhasil ditambahkan!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menambahkan materi: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
    header("Location: tambah_materi.php");
    exit();
}

$mysqli->close();
?>