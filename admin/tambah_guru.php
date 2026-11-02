<?php
session_start();
require_once 'db_config.php';

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tambah guru baru
if (isset($_POST['add_teacher'])) {
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['position'])) {
        $_SESSION['error'] = "Nama dan jabatan harus diisi";
        header("Location: tambah_guru.php");
        exit();
    }

    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $gender = isset($_POST['gender']) && in_array($_POST['gender'], ['male','female']) ? $_POST['gender'] : 'male';
    $is_tu = isset($_POST['is_administrative']) ? 1 : 0;
    
    // Upload foto
    $photo = '';
    if (isset($_FILES['photo']['error']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../../SMPN 4 SBB/uploads/teachers/";
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
    
    try {
        $stmt = $conn->prepare("INSERT INTO teachers (name, position, subject, photo, gender, is_tu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $position, $subject, $photo, $gender, $is_tu);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Guru berhasil ditambahkan";
            header("Location: guru.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menambahkan guru: ".$conn->error;
            header("Location: tambah_guru.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
        header("Location: tambah_guru.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Guru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }

        /* Header biru */
       .header {
    background-color: rgb(8, 29, 123);
    color: white;
    padding: 15px;
    position: fixed;
    width: calc(100% - 200px); /* 100% minus lebar sidebar */
    left: 200px; /* sama dengan lebar sidebar */
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
    padding-top: 80px; /* ruang untuk header */
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
    margin-left: 200px; /* sama dengan lebar sidebar */
    padding: 20px;
    margin-top: 100px; /* sama dengan tinggi header */
}
        .success {
            background: #d1ecf1; /* biru muda info */
            color: #0c5460;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #bee5eb;
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
        <h1>Tambah Data Guru</h1>
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
            <h2>Form Tambah Guru Baru</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="position" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <input type="text" name="subject" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="gender" class="form-control">
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" name="is_administrative" id="is_admin" class="form-check-input">
                    <label for="is_admin" class="form-check-label">Pegawai Tata Usaha</label>
                </div>
                
                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="photo" class="form-control-file">
                </div>
                
                <button type="submit" name="add_teacher" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</body>
</html>