<?php
session_start();

// Koneksi database
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'smpm29';

$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Cek apakah ada ID yang diberikan
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data materi dari database
    $stmt = $mysqli->prepare("SELECT * FROM materials WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $material = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "Materi tidak ditemukan.";
        $_SESSION['message_type'] = "error";
        header("Location: tambah_materi.php");
        exit();
    }
} else {
    $_SESSION['message'] = "ID materi tidak diberikan.";
    $_SESSION['message_type'] = "error";
    header("Location: tambah_materi.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Materi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Edit Materi</h1>
      <a href="tambah_materi.php" class="text-blue-600 hover:text-blue-800"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="mb-4 p-4 rounded <?php echo $_SESSION['message_type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <i class="fas <?php echo $_SESSION['message_type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
        <?php echo $_SESSION['message']; ?>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
      <form action="proses_edit_materi.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $material['id']; ?>">

        <div class="mb-4">
          <label for="judul" class="block font-medium text-gray-700 mb-2">Judul Materi</label>
          <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($material['title']); ?>" class="w-full border border-gray-300 rounded px-4 py-2" required>
        </div>

        <div class="mb-4">
          <label for="kategori" class="block font-medium text-gray-700 mb-2">Kategori</label>
          <select name="kategori" id="kategori" class="w-full border border-gray-300 rounded px-4 py-2" required>
            <?php
              $kategori = ['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'Bahasa Inggris', 'Pendidikan Agama', 'Seni Budaya', 'PJOK', 'Prakarya'];
              foreach ($kategori as $k) {
                $selected = $material['category'] === $k ? 'selected' : '';
                echo "<option value=\"$k\" $selected>$k</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-4">
          <label for="konten" class="block font-medium text-gray-700 mb-2">Deskripsi</label>
          <textarea name="konten" id="konten" rows="5" class="w-full border border-gray-300 rounded px-4 py-2" required><?php echo htmlspecialchars($material['content']); ?></textarea>
        </div>

        <div class="mb-4">
          <label class="block font-medium text-gray-700 mb-2">Lampiran</label>
          <?php if (!empty($material['file_path'])): ?>
            <p class="text-sm text-gray-500 mb-2">File saat ini: <strong><?php echo basename($material['file_path']); ?></strong></p>
          <?php endif; ?>
          <input type="file" name="file_materi" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
          <p class="text-xs text-gray-400 mt-1">Biarkan kosong jika tidak ingin mengubah file.</p>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
          <button type="reset" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded"><i class="fas fa-undo mr-1"></i>Reset</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"><i class="fas fa-save mr-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>

<?php $mysqli->close(); ?>
