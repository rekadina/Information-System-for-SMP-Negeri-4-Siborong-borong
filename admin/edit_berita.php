<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$news = null;
$error = '';
$success = '';

// Get news ID from URL
if (!isset($_GET['id'])) {
    header('Location: berita.php');
    exit();
}

$id = (int)$_GET['id'];

// Fetch news data
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    $_SESSION['error'] = "Berita tidak ditemukan";
    header('Location: berita.php');
    exit();
}

// Process form submission
if (isset($_POST['update_news'])) {
    $title = $_POST['title'];
    $excerpt = $_POST['excerpt'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    
    // Handle thumbnail update
    if (isset($_FILES['image']['error']) && $_FILES['image']['error'] == 0) {
        // Delete old image if exists
        if ($news['image'] && file_exists($news['image'])) {
            unlink($news['image']);
        }
        
        $target_dir = "../../uploads/news/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid().'.'.$file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
        } else {
            $error = "Gagal mengupload thumbnail";
        }
    } else {
        $image = $news['image'];
    }
    
    // Handle additional images update
    $additional_images = json_decode($news['additional_images'], true) ?: [];
    
    if (!empty($_FILES['additional_images']['name'][0])) {
        $target_dir = "../../uploads/news/";
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
    
    // Handle video update
    if (isset($_FILES['video']['error']) && $_FILES['video']['error'] == 0) {
        // Delete old video if exists
        if ($news['video'] && file_exists($news['video'])) {
            unlink($news['video']);
        }
        
        $target_dir = "../../uploads/news/";
        $file_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $new_filename = 'vid_' . uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
            $video = $target_file;
        }
    } else {
        $video = $news['video'];
    }
    
    // Convert additional images to JSON
    $additional_images_json = json_encode($additional_images);
    
    // Update database
    $stmt = $conn->prepare("UPDATE news SET title = ?, excerpt = ?, content = ?, image = ?, additional_images = ?, video = ?, author = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("sssssssi", $title, $excerpt, $content, $image, $additional_images_json, $video, $author, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Berita berhasil diperbarui";
        header("Location: berita.php");
        exit();
    } else {
        $error = "Gagal memperbarui berita: " . $conn->error;
    }
}

// Decode additional images
$additional_images = json_decode($news['additional_images'], true) ?: [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Berita</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }

        /* Header biru */
       .header {
    background-color: rgb(8, 29, 123);
    color: white;
    padding: 15px;
    position: fixed;
    width: calc(100% - 200px); /* 100% minus lebar sidebar */
    left: 200px; /* sama dengan lebar sidebar */
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* SIDEBAR */
.sidebar {
    width: 200px;
    background: #333;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    overflow-y: auto;
    z-index: 1000;
    padding-top: 80px; /* ruang untuk header */
}

        .sidebar a {
            color: white;
            padding: 15px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #1e90ff;
        }

        .sidebar a.logout {
            background: #f44336;
        }

        .sidebar a.logout:hover {
            background: #d32f2f;
        }

        .content {
    margin-left: 200px; /* sama dengan lebar sidebar */
    padding: 20px;
    margin-top: 100px; /* sama dengan tinggi header */
}
        .success {
            background: #d1ecf1; /* biru muda info */
            color: #0c5460;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #bee5eb;
        }

        .error {
            background: #f2dede;
            color: #a94442;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #ebccd1;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input, textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            min-height: 100px;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
        
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
        
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .current-files {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .current-files img, .current-files video {
            max-width: 200px;
            max-height: 150px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .file-preview {
            display: flex;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .file-item {
            margin-right: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .remove-file {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Berita</h1>
        <div style="text-align: right; padding-right: 20px; color: white;">
            Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </div>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <a href="berita.php" class="btn-back">Kembali ke Daftar Berita</a>
        
        <div class="form-container">
            <h2>Edit Berita</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Judul Berita:</label>
                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($news['title']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="excerpt">Ringkasan Berita (untuk tampilan thumbnail):</label>
                    <textarea id="excerpt" name="excerpt" required><?= htmlspecialchars($news['excerpt']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Isi Berita Lengkap:</label>
                    <textarea id="content" name="content" required><?= htmlspecialchars($news['content']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="author">Penulis:</label>
                    <input type="text" id="author" name="author" required value="<?= htmlspecialchars($news['author']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Thumbnail Saat Ini:</label>
                    <?php if ($news['image'] && file_exists($news['image'])): ?>
                        <div class="current-files">
                            <img src="<?= str_replace('../../', '/', $news['image']) ?>" alt="Current Thumbnail">
                        </div>
                    <?php else: ?>
                        <p>Tidak ada thumbnail</p>
                    <?php endif; ?>
                    <label for="image">Ubah Thumbnail Berita:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Foto Tambahan Saat Ini:</label>
                    <?php if (!empty($additional_images)): ?>
                        <div class="current-files file-preview">
                            <?php foreach ($additional_images as $img): ?>
                                <?php if (file_exists($img)): ?>
                                    <div class="file-item">
                                        <img src="<?= str_replace('../../', '/', $img) ?>" alt="Additional Image">
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Tidak ada foto tambahan</p>
                    <?php endif; ?>
                    <label for="additional_images">Tambah Foto Tambahan:</label>
                    <input type="file" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                </div>
                
                <div class="form-group">
                    <label>Video Saat Ini:</label>
                    <?php if ($news['video'] && file_exists($news['video'])): ?>
                        <div class="current-files">
                            <video controls style="max-width: 300px;">
                                <source src="<?= str_replace('../../', '/', $news['video']) ?>" type="video/mp4">
                                Browser Anda tidak mendukung tag video.
                            </video>
                        </div>
                    <?php else: ?>
                        <p>Tidak ada video</p>
                    <?php endif; ?>
                    <label for="video">Ubah Video:</label>
                    <input type="file" id="video" name="video" accept="video/mp4,video/*">
                </div>
                
                <button type="submit" name="update_news">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</body>
</html>