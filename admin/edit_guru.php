<?php
session_start();
require_once 'db_config.php';

// Periksa login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Ambil ID guru dari parameter URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data guru yang akan diedit
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    $_SESSION['error'] = "Data guru tidak ditemukan";
    header("Location: guru.php");
    exit();
}

// Proses update data
if (isset($_POST['update_teacher'])) {
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['position'])) {
        $_SESSION['error'] = "Nama dan jabatan harus diisi";
        header("Location: edit_guru.php?id=".$id);
        exit();
    }

    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $gender = isset($_POST['gender']) && in_array($_POST['gender'], ['male','female']) ? $_POST['gender'] : 'male';
    $is_tu = isset($_POST['is_administrative']) ? 1 : 0;
    
    // Handle upload foto baru
    $photo = $teacher['photo'];
    if (isset($_FILES['photo']['error']) && $_FILES['photo']['error'] == 0) {
        // Hapus foto lama jika ada
        if ($photo && file_exists($photo)) {
            unlink($photo);
        }
        
        // Upload foto baru
        $target_dir = "../../uploads/teachers/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        
        if (in_array($ext, $allowed)) {
            $new_name = uniqid().'.'.$ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir.$new_name)) {
                $photo = $target_dir.$new_name;
            }
        }
    }
    
    // Update data di database
    try {
        $stmt = $conn->prepare("UPDATE teachers SET name = ?, position = ?, subject = ?, photo = ?, gender = ?, is_tu = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $name, $position, $subject, $photo, $gender, $is_tu, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data guru berhasil diperbarui";
            header("Location: guru.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal memperbarui data guru: ".$conn->error;
            header("Location: edit_guru.php?id=".$id);
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
        header("Location: edit_guru.php?id=".$id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }

        /* Header biru */
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

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        
        .current-photo {
            max-width: 150px;
            max-height: 150px;
            margin-bottom: 10px;
            display: block;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-check-input {
            width: auto;
            margin: 0;
        }

        .form-check-label {
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Data Guru</h1>
        <div style="text-align: right; padding-right: 20px; color: white;">
            Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </div>
    </div>

    <div class="sidebar" id="sidebar">
        <a href="dashboard.php">
            <span class="sidebar-icon">📊</span>
            <span class="sidebar-text">Dashboard</span>
        </a>
        <a href="guru.php" class="active">
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <a href="guru.php" class="btn-back">Kembali ke Daftar Guru</a>
        
        <div class="form-container">
            <h2>Form Edit Guru</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" required class="form-control" value="<?= htmlspecialchars($teacher['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="position" required class="form-control" value="<?= htmlspecialchars($teacher['position']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($teacher['subject']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="gender" class="form-control">
                        <option value="male" <?= $teacher['gender'] == 'male' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="female" <?= $teacher['gender'] == 'female' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" name="is_administrative" id="is_admin" class="form-check-input" <?= $teacher['is_tu'] ? 'checked' : '' ?>>
                    <label for="is_admin" class="form-check-label">Pegawai Tata Usaha</label>
                </div>
                
                <div class="form-group">
                    <label>Foto Saat Ini:</label>
                    <?php if (!empty($teacher['photo'])): ?>
                        <?php 
                        $clean_path = str_replace('../../', '', $teacher['photo']);
                        ?>
                        <img src="<?= $clean_path ?>" class="current-photo">
                    <?php else: ?>
                        <img src="assets/img/<?= $teacher['gender'] == 'female' ? 'female_user.png' : 'male_user.webp'; ?>" class="current-photo">
                    <?php endif; ?>
                    
                    <label for="photo">Ganti Foto:</label>
                    <input type="file" name="photo" class="form-control-file">
                    <small>Biarkan kosong jika tidak ingin mengubah foto</small>
                </div>
                
                <button type="submit" name="update_teacher" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</body>
</html>