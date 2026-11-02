<?php
session_start();
require_once 'db_config.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Process form submission
if (isset($_POST['add_gallery_item'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $upload_date = $_POST['upload_date'];
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../uploads/gallery/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO gallery (title, image, description, category, upload_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $image, $description, $category, $upload_date);
    $stmt->execute();
    
    header("Location: galeri.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Galeri Dokumentasi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header {background-color: rgb(8, 29, 123);color: white;padding: 15px;position: fixed;width: calc(100% - 200px);left: 200px;top: 0;z-index: 1000;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .sidebar {width: 200px;background: #333;position: fixed;top: 0;bottom: 0;left: 0;overflow-y: auto;z-index: 1000;padding-top: 80px;}
        .sidebar a { color: white; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover {background-color: #1e90ff;}
        .sidebar a.active {background-color: #1e90ff;}
        .content {margin-left: 200px; padding: 20px; margin-top: 80px;}
        .form-container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #5cb85c; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background-color: #4cae4c; }
        .btn-cancel { background-color: #6c757d; }
        .btn-cancel:hover { background-color: #5a6268; }
        .sidebar a.logout { background: #f44336; }
        .sidebar a.logout:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tambah Galeri Dokumentasi</h1>
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
        <a href="berita.php">
            <span class="sidebar-icon">📰</span>
            <span class="sidebar-text">Berita</span>
        </a>
        <a href="fasilitas.php">
            <span class="sidebar-icon">🛋️</span>
            <span class="sidebar-text">Fasilitas</span>
        </a>
        <a href="galeri.php" class="active">
            <span class="sidebar-icon">🖼️</span>
            <span class="sidebar-text">Galeri</span>
        </a>
        <a href="logout.php" class="logout">
            <span class="sidebar-icon">🚪</span>
            <span class="sidebar-text">Logout</span>
        </a>
    </div>

    <div class="content">
        <div class="form-container">
            <form action="tambah_galeri.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Judul:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Gambar:</label>
                    <input type="file" id="image" name="image" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori:</label>
                    <input type="text" id="category" name="category">
                </div>
                
                <div class="form-group">
                    <label for="upload_date">Tanggal Upload:</label>
                    <input type="date" id="upload_date" name="upload_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <button type="submit" name="add_gallery_item">Simpan</button>
                <a href="galeri.php" class="btn-cancel">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>