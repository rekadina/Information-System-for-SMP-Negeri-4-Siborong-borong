<?php
session_start();
require_once 'db_config.php';

// Pastikan user sudah login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Cek parameter id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: galeri.php');
    exit();
}

$id = (int)$_GET['id'];

// Ambil data galeri berdasarkan id
$stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$gallery_item = $result->fetch_assoc();

if (!$gallery_item) {
    // Data tidak ditemukan
    header('Location: galeri.php');
    exit();
}

$error = '';
$success = '';

// Proses update data jika form disubmit
if (isset($_POST['update_gallery_item'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $upload_date = $_POST['upload_date'];

    $image = $gallery_item['image']; // Gambar lama

    // Cek apakah ada upload gambar baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../uploads/gallery/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Hapus gambar lama jika ada
            if ($image && file_exists($image)) {
                unlink($image);
            }
            $image = $target_file;
        } else {
            $error = 'Gagal mengunggah gambar baru.';
        }
    }

    if (!$error) {
        // Update database
        $stmt = $conn->prepare("UPDATE gallery SET title=?, image=?, description=?, category=?, upload_date=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $image, $description, $category, $upload_date, $id);
        if ($stmt->execute()) {
            header("Location: galeri.php?success=3");
            exit();
        } else {
            $error = 'Gagal memperbarui data galeri.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Galeri Dokumentasi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header {background-color: rgb(8, 29, 123);color: white;padding: 15px;position: fixed;width: calc(100% - 200px);left: 200px;top: 0;z-index: 1000;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .sidebar {width: 200px;background: #333;position: fixed;top: 0;bottom: 0;left: 0;overflow-y: auto;z-index: 1000;padding-top: 80px;}
        .sidebar a { color: white; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover {background-color: #1e90ff;}
        .sidebar a.active {background-color: #1e90ff;}
        .content {margin-left: 200px; padding: 20px; margin-top: 80px;}
        .form-container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; font-size: 16px; }
        button:hover { background-color: #218838; }
        .btn-cancel { background-color: #6c757d; }
        .btn-cancel:hover { background-color: #5a6268; }
        .sidebar a.logout { background: #f44336; }
        .sidebar a.logout:hover { background: #d32f2f; }
        .gallery-image-preview { max-width: 200px; max-height: 150px; margin-bottom: 15px; display: block; }
        .error-msg { background-color: #f8d7da; color: #842029; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Galeri Dokumentasi</h1>
    </div>
    <div class="sidebar" id="sidebar">
        <a href="dashboard.php"><span class="sidebar-icon">📊</span><span class="sidebar-text">Dashboard</span></a>
        <a href="guru.php"><span class="sidebar-icon">👨‍🏫</span><span class="sidebar-text">Guru</span></a>
        <a href="siswa.php"><span class="sidebar-icon">👨‍🎓</span><span class="sidebar-text">Siswa</span></a>
        <a href="kelas.php"><span class="sidebar-icon">🏫</span><span class="sidebar-text">Kelas</span></a>
        <a href="ruangan.php"><span class="sidebar-icon">🚪</span><span class="sidebar-text">Ruangan</span></a>
        <a href="matapelajaran.php"><span class="sidebar-icon">📚</span><span class="sidebar-text">Mata Pelajaran</span></a>
        <a href="berita.php"><span class="sidebar-icon">📰</span><span class="sidebar-text">Berita</span></a>
        <a href="fasilitas.php"><span class="sidebar-icon">🛋️</span><span class="sidebar-text">Fasilitas</span></a>
        <a href="galeri.php" class="active"><span class="sidebar-icon">🖼️</span><span class="sidebar-text">Galeri</span></a>
        <a href="logout.php" class="logout"><span class="sidebar-icon">🚪</span><span class="sidebar-text">Logout</span></a>
    </div>

    <div class="content">
        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="edit_galeri.php?id=<?= $id ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Judul:</label>
                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($gallery_item['title']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Gambar Saat Ini:</label><br>
                    <?php if ($gallery_item['image'] && file_exists($gallery_item['image'])): ?>
                    <img src="<?= htmlspecialchars($gallery_item['image']) ?>" alt="Current Image" class="gallery-image-preview" />
                    <?php else: ?>
                    <p>- Tidak ada gambar -</p>
                    <?php endif; ?>
                    <label for="image">Ganti Gambar (kosongkan jika tidak ingin mengubah):</label>
                    <input type="file" id="image" name="image" accept="image/*" >
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($gallery_item['description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori:</label>
                    <input type="text" id="category" name="category" value="<?= htmlspecialchars($gallery_item['category']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="upload_date">Tanggal Upload:</label>
                    <input type="date" id="upload_date" name="upload_date" required value="<?= htmlspecialchars(date('Y-m-d', strtotime($gallery_item['upload_date']))) ?>">
                </div>
                
                <button type="submit" name="update_gallery_item">Update</button>
                <a href="galeri.php" class="btn-cancel">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>

