<?php
session_start();
include ('includes/koneksi.php');
require_once 'db_config.php';

// Periksa apakah user sudah login, jika tidak redirect ke login.php
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Hapus fasilitas
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Hapus gambar jika ada
    $stmt = $conn->prepare("SELECT image, image_thumbnail FROM facilities WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $facility = $result->fetch_assoc();
    
    if ($facility['image'] && file_exists($facility['image'])) {
        unlink($facility['image']);
    }
    if ($facility['image_thumbnail'] && file_exists($facility['image_thumbnail'])) {
        unlink($facility['image_thumbnail']);
    }
    
    $stmt = $conn->prepare("DELETE FROM facilities WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = "Fasilitas berhasil dihapus!";
    header("Location: fasilitas.php");
    exit();
}

// Ambil semua fasilitas dengan format yang benar
$facilities = $conn->query("SELECT id, name, image_thumbnail, description FROM facilities ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sarana & Prasarana</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .facility-image { max-width: 100px; max-height: 100px; }
        .title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .add-btn:hover {
            background-color: #3e8e41;
            color: white;
        }
        .action-btns {
            display: flex;
            gap: 5px;
        }
        .edit-btn {
            background-color: #2196F3;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }
        .edit-btn:hover {
            background-color: #0b7dda;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }
        .delete-btn:hover {
            background-color: #da190b;
        }

        .table-image-container {
    width: 80px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f5f5f5;
    border-radius: 4px;
}

.table-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Sarana & Prasarana</h1>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="title-container">
            <h2>Daftar Fasilitas</h2>
            <a href="tambah_fasilitas.php" class="add-btn">+ Tambah Fasilitas</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Thumbnail</th>
                    <th>Nama</th>
                    <th>Deskripsi Singkat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($facilities->num_rows > 0): ?>
                    <?php $no = 1; while ($facility = $facilities->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                           <td>
    <?php if ($facility['image_thumbnail']): ?>
        <div class="table-image-container">
            <img src="<?= $facility['image_thumbnail'] ?>" alt="<?= htmlspecialchars($facility['name']) ?>">
        </div>
    <?php else: ?>
        <span>Tidak ada gambar</span>
    <?php endif; ?>
</td>
                            <td><?= htmlspecialchars($facility['name']) ?></td>
                            <td><?= !empty($facility['description']) ? substr(htmlspecialchars($facility['description']), 0, 50) . '...' : '-' ?></td>
                            <td class="action-btns">
                                <a href="edit_fasilitas.php?id=<?= $facility['id'] ?>" class="edit-btn">Edit</a>
                                <a href="fasilitas.php?delete=<?= $facility['id'] ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus fasilitas ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Belum ada fasilitas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>