<?php
session_start();
include ('includes/koneksi.php');
require_once 'db_config.php';

// Cek login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Hapus berita
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT image, additional_images, video FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $news = $result->fetch_assoc();

    // Hapus file-file terkait
    if ($news['image'] && file_exists($news['image'])) {
        unlink($news['image']);
    }
    
    $additional_images = json_decode($news['additional_images'], true);
    if (is_array($additional_images)) {
        foreach ($additional_images as $img) {
            if (file_exists($img)) {
                unlink($img);
            }
        }
    }
    
    if ($news['video'] && file_exists($news['video'])) {
        unlink($news['video']);
    }

    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: berita.php?success=2");
    exit();
}

// Ambil semua berita
$news = $conn->query("SELECT id, title, excerpt, image, publish_date, author FROM news ORDER BY publish_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Informasi & Berita Sekolah</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .news-image { max-width: 100px; max-height: 100px; }
        
        /* Gaya untuk tombol aksi */
        .edit-btn {
            background-color: #2196F3;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
            transition: background-color 0.3s;
        }
        .edit-btn:hover {
            background-color: #0b7dda;
            color: white;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .delete-btn:hover {
            background-color: #da190b;
            color: white;
        }
        .view-btn {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
            transition: background-color 0.3s;
        }
        .view-btn:hover {
            background-color: #3e8e41;
            color: white;
        }
        .td-actions {
            display: flex;
            gap: 5px;
            align-items: center;
            height: 100%;
            min-height: 50px;
        }
        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
            float: right;
        }
        .add-btn:hover {
            background-color: #3e8e41;
            color: white;
        }
        .title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Kelola Informasi & Berita Sekolah</h1>
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
     <a href="kelas.php" >
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
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success">Berita berhasil diposting!</div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] == 2): ?>
        <div class="success">Berita berhasil dihapus!</div>
    <?php endif; ?>

    <div class="title-container">
        <h2>Daftar Berita</h2>
        <a href="tambah_berita.php" class="add-btn">+ Tambah Berita</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Thumbnail</th>
                <th>Judul</th>
                <th>Ringkasan</th>
                <th>Tanggal</th>
                <th>Penulis</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if ($news && $news->num_rows > 0) {
                while ($item = $news->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($item['image'] && file_exists($item['image'])): ?>
                                <img src="<?php echo str_replace('../../', '/', $item['image']); ?>" alt="Thumbnail" class="news-image">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars($item['excerpt']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($item['publish_date'])); ?></td>
                        <td><?php echo htmlspecialchars($item['author']); ?></td>
                        <td class="td-actions">
                            <a href="edit_berita.php?id=<?php echo $item['id']; ?>" class="edit-btn">Edit</a>
                            <a href="berita.php?delete=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            <a href="view_berita.php?id=<?php echo $item['id']; ?>" class="view-btn" target="_blank">Lihat</a>
                        </td>
                    </tr>
                <?php endwhile;
            } else {
                echo '<tr><td colspan="7" style="text-align:center;">Tidak ada data berita</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>