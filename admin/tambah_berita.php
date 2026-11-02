<?php
session_start();
include ('includes/koneksi.php');
require_once 'db_config.php';

// Cek login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Simpan berita lengkap (thumbnail + konten sekaligus)
if (isset($_POST['save_news'])) {
    $title = $_POST['title'];
    $excerpt = $_POST['excerpt'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $image = '';
    $additional_images = [];
    $video = '';

    // Handle upload thumbnail
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../SMPN 4 SBB/uploads/news/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
        } else {
            $error = "Gagal mengupload thumbnail";
        }
    }

    // Handle upload multiple additional images
    if (!empty($_FILES['additional_images']['name'][0])) {
         $target_dir = "../../SMPN 4 SBB/uploads/news/";
        foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['additional_images']['error'][$key] == 0) {
                $file_ext = strtolower(pathinfo($_FILES['additional_images']['name'][$key], PATHINFO_EXTENSION));
                $new_filename = 'img_' . uniqid() . '_' . $key . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $additional_images[] = $target_file;
                }
            }
        }
    }

    // Handle upload video
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
         $target_dir = "../../SMP4 N SBB/uploads/news/";
        $file_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $new_filename = 'vid_' . uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
            $video = $target_file;
        }
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO news (title, excerpt, content, image, additional_images, video, publish_date, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $today = date('Y-m-d');
    $additional_images_json = json_encode($additional_images);
    $stmt->bind_param("ssssssss", $title, $excerpt, $content, $image, $additional_images_json, $video, $today, $author);
    $stmt->execute();

    header("Location: berita.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Berita Baru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header { background-color: rgb(8, 29, 123); color: white; padding: 15px; position: fixed; width: calc(100% - 200px); left: 200px; top: 0; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .sidebar { width: 200px; background: #333; position: fixed; top: 0; bottom: 0; left: 0; overflow-y: auto; z-index: 1000; padding-top: 80px; }
        .sidebar a { color: white; padding: 15px; text-decoration: none; display: block; }
        .sidebar a:hover:not(.logout) { background: #1565C0; }
        .sidebar a.active { background-color: #1565C0; }
        .sidebar a.logout { background-color: #d32f2f; }
        .sidebar a.logout:hover { background-color: #f44336; }
        .content { margin-left: 200px; padding: 20px; margin-top: 80px; }
        .success, .error { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #dff0d8; color:rgb(8, 29, 123); }
        .error { background: #f2dede; color: #a94442; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #5cb85c; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #4cae4c; }
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Tambah Berita Baru</h1>
</div>

<div class="sidebar" id="sidebar">
    <a href="dashboard.php">
        <span class="sidebar-icon">📊</span>
        <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="guru.php">
        <span class="sidebar-icon">👨‍🏫</span>
        <span class="sidebar-text">Guru</span>
    </a>
    <a href="siswa.php">
        <span class="sidebar-icon">👨‍🎓</span>
        <span class="sidebar-text">Siswa</span>
    </a>
    <a href="kelas.php">
            <span class="sidebar-icon">🏫</span>
            <span class="sidebar-text">Kelas</span>
        </a>
        <a href="ruangan.php">
            <span class="sidebar-icon">🚪</span>
            <span class="sidebar-text">Ruangan</span>
        </a>
    <a href="matapelajaran.php">
        <span class="sidebar-icon">📚</span>
        <span class="sidebar-text">Mata Pelajaran</span>
    </a>
    <a href="berita.php" class="active">
        <span class="sidebar-icon">📰</span>
        <span class="sidebar-text">Berita</span>
    </a>
    <a href="fasilitas.php">
        <span class="sidebar-icon">🛋️</span>
        <span class="sidebar-text">Fasilitas</span>
    </a>
    <a href="galeri.php">
        <span class="sidebar-icon">🖼️</span>
        <span class="sidebar-text">Galeri</span>
    </a>
    <a href="logout.php" class="logout">
        <span class="sidebar-icon">🚪</span>
        <span class="sidebar-text">Logout</span>
    </a>
</div>

<div class="content">
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <a href="berita.php" class="btn-back">Kembali ke Daftar Berita</a>
    
    <h2>Form Tambah Berita</h2>
    <form action="tambah_berita.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Judul Berita:</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="excerpt">Ringkasan Berita (untuk tampilan thumbnail):</label>
            <textarea id="excerpt" name="excerpt" rows="3" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Isi Berita Lengkap:</label>
            <textarea id="content" name="content" rows="10" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="author">Penulis:</label>
            <input type="text" id="author" name="author" required value="<?php echo $_SESSION['admin_username'] ?? 'Administrator'; ?>">
        </div>
        
        <div class="form-group">
            <label for="image">Thumbnail Berita:</label>
            <input type="file" id="image" name="image" accept="image/*" required>
        </div>
        
        <div class="form-group">
            <label for="additional_images">Foto Tambahan (bisa multiple):</label>
            <input type="file" id="additional_images" name="additional_images[]" accept="image/*" multiple>
        </div>
        
        <div class="form-group">
            <label for="video">Upload Video (mp4):</label>
            <input type="file" id="video" name="video" accept="video/mp4,video/*">
        </div>
        
        <button type="submit" name="save_news">Simpan Berita</button>
    </form>
</div>
</body>
</html>