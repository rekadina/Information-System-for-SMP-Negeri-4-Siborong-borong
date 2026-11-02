<?php
session_start();
require_once 'db_config.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID kelas tidak valid";
    header("Location: kelas.php");
    exit();
}

$id = (int)$_GET['id'];

// Get class data
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();

if (!$class) {
    $_SESSION['error'] = "Kelas tidak ditemukan";
    header("Location: kelas.php");
    exit();
}

// Get all teachers for dropdown
$teachers_query = $conn->query("SELECT name FROM teachers ORDER BY name ASC");

// Update class
if (isset($_POST['update_class'])) {
    $class_name = trim($_POST['class_name']);
    $homeroom_teacher = trim($_POST['homeroom_teacher']);

    // Validate input
    if (empty($class_name)) {
        $_SESSION['error'] = "Nama kelas harus diisi";
        header("Location: edit_kelas.php?id=".$id);
        exit();
    }

    try {
        $stmt = $conn->prepare("UPDATE classes SET class_name = ?, homeroom_teacher = ? WHERE id = ?");
        $stmt->bind_param("ssi", $class_name, $homeroom_teacher, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data kelas berhasil diperbarui";
            header("Location: kelas.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal memperbarui data kelas: " . $conn->error;
            header("Location: edit_kelas.php?id=".$id);
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
        header("Location: edit_kelas.php?id=".$id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Kelas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }

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

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Data Kelas</h1>
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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="edit_kelas.php?id=<?= $id ?>" method="post">
                <div class="form-group">
                    <label for="class_name">Nama Kelas</label>
                    <input type="text" id="class_name" name="class_name" 
                           value="<?= htmlspecialchars($class['class_name']) ?>" 
                           placeholder="Contoh: VII-A, VIII-B" required>
                </div>
                
                <div class="form-group">
                    <label for="homeroom_teacher">Wali Kelas</label>
                    <select id="homeroom_teacher" name="homeroom_teacher">
                        <option value="">Pilih Wali Kelas</option>
                        <?php 
                        $teachers_query->data_seek(0); // Reset pointer
                        while ($teacher = $teachers_query->fetch_assoc()): 
                        ?>
                            <option value="<?= $teacher['name'] ?>" 
                                <?= $class['homeroom_teacher'] == $teacher['name'] ? 'selected' : '' ?>>
                                <?= $teacher['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <a href="kelas.php" class="btn-back">Kembali</a>
                    <button type="submit" name="update_class" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>