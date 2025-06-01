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
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'];
    $konten = $_POST['konten'];

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
            $upload_dir = __DIR__ . '/uploads/';
            $file_path = $file_name_new;

            // Pindahkan file ke direktori uploads
            if (!move_uploaded_file($file_tmp, $upload_dir . $file_name_new)) {
                $_SESSION['message'] = "Gagal mengunggah file.";
                $_SESSION['message_type'] = "error";
                header("Location: edit_materi.php?id=" . $id);
                exit();
            }
        } else {
            $_SESSION['message'] = "Format file tidak didukung. Harap unggah file PDF, DOC, PPT, JPG, PNG, atau MP4.";
            $_SESSION['message_type'] = "error";
            header("Location: edit_materi.php?id=" . $id);
            exit();
        }
    }

    // Update data materi di database
    if ($file_path) {
        $stmt = $mysqli->prepare("UPDATE materials SET title = ?, category = ?, content = ?, file_path = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $judul, $kategori, $konten, $file_path, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE materials SET title = ?, category = ?, content = ? WHERE id = ?");
        $stmt->bind_param("sssi", $judul, $kategori, $konten, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Materi berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal memperbarui materi: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
    header("Location: tambah_materi.php");
    exit();
}
?>
