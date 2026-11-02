<?php
session_start();
require_once 'db_config.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Hapus item galeri
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Hapus gambar jika ada
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if ($item['image'] && file_exists($item['image'])) {
        unlink($item['image']);
    }
    
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: galeri.php?success=2");
    exit();
}

// Ambil semua item galeri
$gallery_items = $conn->query("SELECT * FROM gallery ORDER BY upload_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Galeri Dokumentasi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header {background-color: rgb(8, 29, 123);color: white;padding: 15px;position: fixed;width: calc(100% - 200px);left: 200px;top: 0;z-index: 1000;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .sidebar {width: 200px;background: #333;position: fixed;top: 0;bottom: 0;left: 0;overflow-y: auto;z-index: 1000;padding-top: 80px;}
        .sidebar a { color: white; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover {background-color: #1e90ff;}
        .sidebar a.active {background-color: #1e90ff;}
        .content {margin-left: 200px; padding: 20px; margin-top: 80px;}
        .success { background: #dff0d8; color: #3c763d; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .action-btns a { padding: 5px 10px; text-decoration: none; margin-right: 5px; }
        .edit-btn { background: #5bc0de; color: white; }
        .delete-btn { background: #d9534f; color: white; }
        .gallery-image { max-width: 150px; max-height: 100px; }
        .sidebar a.logout { background: #f44336; }
        .sidebar a.logout:hover { background: #d32f2f; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .add-btn { 
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .add-btn:hover { 
            background-color: #218838;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Galeri Dokumentasi</h1>
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
        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                <?php 
                if ($_GET['success'] == 1) echo "Item galeri berhasil ditambahkan!";
                elseif ($_GET['success'] == 2) echo "Item galeri berhasil dihapus!";
                elseif ($_GET['success'] == 3) echo "Item galeri berhasil diperbarui!";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="header-section">
            <h2>Daftar Galeri</h2>
            <a href="tambah_galeri.php" class="add-btn">
                <span>+</span>
                <span>Tambah Galeri</span>
            </a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($item = $gallery_items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="gallery-image"></td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo $item['category'] ? htmlspecialchars($item['category']) : '-'; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($item['upload_date'])); ?></td>
                    <td class="action-btns">
                        <a href="edit_galeri.php?id=<?php echo $item['id']; ?>" class="edit-btn">Edit</a>
                        <a href="galeri.php?delete=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus item galeri ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>