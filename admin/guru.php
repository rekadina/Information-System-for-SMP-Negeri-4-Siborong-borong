<?php
session_start();
require_once 'db_config.php';

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hapus guru
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Hapus foto jika ada
    $stmt = $conn->prepare("SELECT photo FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
    
    if ($teacher['photo'] && file_exists($teacher['photo'])) {
        unlink($teacher['photo']);
    }
    
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Guru berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus guru";
    }
    
    header("Location: guru.php");
    exit();
}

// Tambah guru baru
if (isset($_POST['add_teacher'])) {
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['position'])) {
        $_SESSION['error'] = "Nama dan jabatan harus diisi";
        header("Location: guru.php");
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
    
    try {
        $stmt = $conn->prepare("INSERT INTO teachers (name, position, subject, photo, gender, is_tu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $position, $subject, $photo, $gender, $is_tu);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Guru berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan guru: ".$conn->error;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
    }
    
    header("Location: guru.php");
    exit();
}

// Query untuk guru biasa (bukan tata usaha)
$query_guru = "SELECT * FROM teachers WHERE is_tu = 0 ORDER BY position DESC, name ASC";
$guru = $conn->query($query_guru);

$query_tata_usaha = "SELECT * FROM teachers WHERE is_tu = 1 ORDER BY name ASC";
$tata_usaha = $conn->query($query_tata_usaha); 	

if ($guru === false || $tata_usaha === false) {
    die("Query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Guru</title>
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
            background-color: #e3f2fd; /* biru sangat muda */
            color: #0d47a1;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Gaya untuk tombol aksi */
.action-btns {
    display: flex;
    gap: 8px; /* Jarak antara tombol */
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
    background-color: #3498db; /* Warna biru */
    color: white;
}

.btn-edit:hover {
    background-color: #2980b9; /* Warna biru lebih gelap saat hover */
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-delete {
    background-color: #e74c3c; /* Warna merah */
    color: white;
}

.btn-delete:hover {
    background-color: #c0392b; /* Warna merah lebih gelap saat hover */
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-add {
    background-color: #28a745; /* Warna hijau */
    color: white;
    padding: 8px 16px;
    margin-left: auto; /* Posisikan ke kanan */
}

.btn-add:hover {
    background-color: #218838;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

        .delete-btn {
            background: #dc3545; /* merah */
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
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
        <h1>Kelola Data Guru</h1>
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
        
        <div class="header-section">
            <h2>Daftar Guru</h2>
            <a href="tambah_guru.php" class="btn btn-add">+ Data Guru</a>
        </div>
        
        <div class="container mt-5">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Mata Pelajaran</th>
                        <th>Jenis Kelamin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($guru && $guru->num_rows > 0): ?>
                        <?php $no = 1; while($row = $guru->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <?php 
                                    $clean_path = str_replace('../../', '', $row['photo']);
                                    ?>
                                    <img src="<?= $clean_path ?>" width="50" height="50" class="rounded-circle">
                                <?php else: ?>
                                    <img src="assets/img/<?= $row['gender'] == 'female' ? 'female_user.png' : 'male_user.webp'; ?>" width="50" height="50" class="rounded-circle">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['position']); ?></td>
                            <td><?= htmlspecialchars($row['subject']); ?></td>
                            <td><?= $row['gender'] == 'male' ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="edit_guru.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="guru.php?delete=<?= $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data guru</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
            <h2 class="mt-5">Pegawai Tata Usaha</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Jenis Kelamin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tata_usaha && $tata_usaha->num_rows > 0): ?>
                        <?php $no = 1; while($row = $tata_usaha->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <?php 
                                    $clean_path = str_replace('../../', '', $row['photo']);
                                    ?>
                                    <img src="<?= $clean_path ?>" width="50" height="50" class="rounded-circle">
                                <?php else: ?>
                                    <img src="assets/img/<?= $row['gender'] == 'female' ? 'female_user.png' : 'male_user.webp'; ?>" width="50" height="50" class="rounded-circle">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['position']); ?></td>
                            <td><?= $row['gender'] == 'male' ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="edit_guru.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="guru.php?delete=<?= $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data pegawai tata usaha</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>