<?php
session_start();
require_once 'db_config.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Proses tambah fasilitas baru
if (isset($_POST['add_facility'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $tab_content = trim($_POST['tab_content']);
    
    // Validasi input
    if (empty($name) || empty($description) || empty($tab_content)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header("Location: tambah_fasilitas.php");
        exit();
    }
    
    // Handle upload gambar
    $image = '';
    $image_thumbnail = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Validasi file upload
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Hanya file gambar (JPEG, PNG, GIF) yang diizinkan";
            header("Location: tambah_fasilitas.php");
            exit();
        }
        
        $target_dir = "../../SMPN 4 SBB/uploads/facilities/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                $_SESSION['error'] = "Gagal membuat direktori upload";
                header("Location: tambah_fasilitas.php");
                exit();
            }
        }
        
        // Upload gambar utama
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
            
            // Buat thumbnail
            $thumbnail_file = $target_dir . 'thumb_' . $new_filename;
            if (createThumbnail($target_file, $thumbnail_file, 500, 500)) {
                $image_thumbnail = $thumbnail_file;
            } else {
                $_SESSION['error'] = "Gagal membuat thumbnail";
                header("Location: tambah_fasilitas.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Gagal mengupload gambar";
            header("Location: tambah_fasilitas.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Gambar fasilitas harus diupload";
        header("Location: tambah_fasilitas.php");
        exit();
    }
    
    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO facilities (name, image, image_thumbnail, description, tab_content) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $_SESSION['error'] = "Prepare statement gagal: " . htmlspecialchars($conn->error);
        header("Location: tambah_fasilitas.php");
        exit();
    }
    
    $stmt->bind_param("sssss", $name, $image, $image_thumbnail, $description, $tab_content);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Fasilitas berhasil ditambahkan!";
        header("Location: fasilitas.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan fasilitas: " . htmlspecialchars($stmt->error);
        header("Location: tambah_fasilitas.php");
        exit();
    }
}

// Fungsi createThumbnail
function createThumbnail($src, $dest, $width, $height) {
    if (!file_exists($src)) {
        error_log("File sumber tidak ditemukan: $src");
        return false;
    }

    $info = getimagesize($src);
    if (!$info) {
        error_log("File bukan gambar yang valid: $src");
        return false;
    }

    $type = $info[2];
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($src);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($src);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($src);
            break;
        default:
            error_log("Tipe gambar tidak didukung: $type");
            return false;
    }
    
    if (!$image) {
        error_log("Gagal membuat gambar dari sumber: $src");
        return false;
    }
    
    $thumb = imagecreatetruecolor($width, $height);
    if (!$thumb) {
        error_log("Gagal membuat gambar thumbnail");
        imagedestroy($image);
        return false;
    }
    
    // Preserve transparency untuk PNG/GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    
    if (!imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image))) {
        error_log("Gagal menyalin gambar yang diresample");
        imagedestroy($image);
        imagedestroy($thumb);
        return false;
    }
    
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($thumb, $dest, 90);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($thumb, $dest, 9);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($thumb, $dest);
            break;
    }
    
    imagedestroy($thumb);
    imagedestroy($image);
    
    if (!$success) {
        error_log("Gagal menyimpan thumbnail: $dest");
        return false;
    }
    
    return true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Fasilitas Baru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header {background-color: rgb(8, 29, 123);color: white;padding: 15px;position: fixed;width: calc(100% - 200px);left: 200px;top: 0;z-index: 1000;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .sidebar {width: 200px;background: #333;position: fixed;top: 0;bottom: 0;left: 0;overflow-y: auto;z-index: 1000;padding-top: 80px;}
        .sidebar a { color: white; padding: 15px; text-decoration: none; display: block; }
        .sidebar a:hover:not(.logout) { background: #1565C0; }
        .sidebar a.active { background-color: #1565C0; }
        .sidebar a.logout { background-color: #d32f2f; }
        .sidebar a.logout:hover { background-color: #f44336; }
        .content {margin-left: 200px;padding: 20px;margin-top: 80px;}
        .success { background: #dff0d8; color:rgb(8, 29, 123); padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .error { background: #f2dede; color: #a94442; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #5cb85c; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #4cae4c; }
        .cancel-btn {
            background-color: #d9534f;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .cancel-btn:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tambah Fasilitas Baru</h1>
    </div>

    <div class="sidebar">
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
        <a href="berita.php">
            <span class="sidebar-icon">📰</span>
            <span class="sidebar-text">Berita</span>
        </a>
        <a href="fasilitas.php" class="active">
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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h2>Form Tambah Fasilitas</h2>
        <form action="tambah_fasilitas.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nama Fasilitas:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi Singkat:</label>
                <textarea id="description" name="description" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="tab_content">Konten Tab (Detail Fasilitas):</label>
                <textarea id="tab_content" name="tab_content" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Gambar Utama:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Gambar akan otomatis dibuat thumbnail-nya</small>
            </div>
            
            <button type="submit" name="add_facility">Simpan Fasilitas</button>
            <a href="fasilitas.php" class="cancel-btn">Batal</a>
        </form>
    </div>
</body>
</html>