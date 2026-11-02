<?php
session_start();
require_once 'db_config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add new class
if (isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    $homeroom_teacher = trim($_POST['homeroom_teacher']);

    // Validate input
    if (empty($class_name)) {
        $_SESSION['error'] = "Nama kelas harus diisi";
        header("Location: tambah_kelas.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, homeroom_teacher) VALUES (?, ?)");
        $stmt->bind_param("ss", $class_name, $homeroom_teacher);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Kelas berhasil ditambahkan";
            header("Location: kelas.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menambahkan kelas: " . $conn->error;
            header("Location: tambah_kelas.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
        header("Location: tambah_kelas.php");
        exit();
    }
}

// Delete class
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Kelas berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus kelas";
    }
    
    header("Location: kelas.php");
    exit();
}

// Get all classes
$classes_query = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
if ($classes_query === false) {
    die("Query error: " . $conn->error);
}

// Get all teachers for dropdown
$teachers_query = $conn->query("SELECT name FROM teachers ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Kelas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }

        /* Header */
        .header {
            background-color: rgb(8, 29, 123);
            color: white;
            padding: 15px;
            position: fixed;
            width: calc(100% - 200px);
            left: 200px;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            background: #333;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
            padding-top: 80px;
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
            margin-left: 200px;
            padding: 20px;
            margin-top: 100px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #e3f2fd;
            color: #0d47a1;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Action buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #3498db;
            color: white;
        }

        .btn-edit:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-add {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            margin-left: auto;
        }

        .btn-add:hover {
            background-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
    </style>    
</head>
<body>
    <div class="header">
        <h1>Kelola Data Kelas</h1>
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
         <a href="kelas.php" class='active'>
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
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="header-section">
            <h2>Daftar Kelas</h2>
            <a href="tambah_kelas.php" class="btn btn-add">+ Data Kelas</a>
        </div>

        <div class="container mt-5">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Wali Kelas</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($classes_query && $classes_query->num_rows > 0): ?>
                        <?php $no = 1; while($row = $classes_query->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['class_name']); ?></td>
                            <td><?= htmlspecialchars($row['homeroom_teacher']); ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="edit_kelas.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="kelas.php?delete=<?= $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data kelas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>