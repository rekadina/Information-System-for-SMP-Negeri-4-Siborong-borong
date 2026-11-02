<?php
session_start();
require_once 'db_config.php';

// Pastikan koneksi berhasil
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek apakah tabel students dan classes ada
$result = $conn->query("SHOW TABLES LIKE 'students'");
if ($result->num_rows == 0) {
    die("Tabel students tidak ditemukan");
}

$result = $conn->query("SHOW TABLES LIKE 'classes'");
if ($result->num_rows == 0) {
    die("Tabel classes tidak ditemukan");
}

// Query yang aman
try {
    $students_query = $conn->query("SELECT s.*, c.homeroom_teacher 
                                  FROM students s
                                  LEFT JOIN classes c ON s.class = c.class_name
                                  ORDER BY s.class ASC, s.name ASC");
    
    $classes_query = $conn->query("SELECT DISTINCT class_name as class FROM classes ORDER BY class_name ASC");
    
    if ($students_query === false || $classes_query === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    die("Error dalam query: " . $e->getMessage());
}
echo "</pre>";

// Add new student
if (isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $class = trim($_POST['class']);
    $homeroom_teacher = trim($_POST['homeroom_teacher']);
    $phone = trim($_POST['phone']);

    // Validate input
    if (empty($name) || empty($address) || empty($class)) {
        $_SESSION['error'] = "Nama, alamat, dan kelas harus diisi";
        header("Location: tambah_siswa.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, address, class, homeroom_teacher, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $student_id, $name, $address, $class, $homeroom_teacher, $phone);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Siswa berhasil ditambahkan";
            header("Location: siswa.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menambahkan siswa: " . $conn->error;
            header("Location: tambah_siswa.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
        header("Location: tambah_siswa.php");
        exit();
    }
}

// Delete student
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Siswa berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus siswa";
    }
    
    header("Location: siswa.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Siswa</title>
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

        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>    
</head>
<body>
    <div class="header">
        <h1>Kelola Data Siswa</h1>
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
        <a href="siswa.php" class="active">
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
            <h2>Daftar Siswa</h2>
            <a href="tambah_siswa.php" class="btn btn-add">+ Data Siswa</a>
        </div>
        
        <div class="filter-section">
            <form method="get" action="">
                <label for="filter_class">Filter Kelas:</label>
                <select name="filter_class" id="filter_class" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php while ($class = $classes_query->fetch_assoc()): ?>
                        <option value="<?= $class['class'] ?>" <?= isset($_GET['filter_class']) && $_GET['filter_class'] == $class['class'] ? 'selected' : '' ?>>
                            <?= $class['class'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <div class="container mt-5">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>ID Siswa</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Kelas</th>
                        <th>Wali Kelas</th>
                        <th>No. HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_query && $students_query->num_rows > 0): ?>
                        <?php 
                        $no = 1; 
                        while($row = $students_query->fetch_assoc()): 
                            // Apply filter if set
                            if (isset($_GET['filter_class']) && $_GET['filter_class'] != '' && $row['class'] != $_GET['filter_class']) {
                                continue;
                            }
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['student_id']); ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['address']); ?></td>
                            <td><?= htmlspecialchars($row['class']); ?></td>
                            <td><?= htmlspecialchars($row['homeroom_teacher']); ?></td>
                            <td><?= htmlspecialchars($row['phone']); ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="edit_siswa.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="siswa.php?delete=<?= $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data siswa</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>