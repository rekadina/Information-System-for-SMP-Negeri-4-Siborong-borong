<?php
session_start();
require_once 'db_config.php';

// PROSES TAMBAH DATA
if (isset($_POST['add_subject'])) {
    $year = $_POST['year'];
    $name = trim($_POST['name']);
    
    $stmt = $conn->prepare("INSERT INTO subjects (year, name) VALUES (?, ?)");
    $stmt->bind_param("ss", $year, $name);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Mata pelajaran berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan data: " . $conn->error;
    }
    
    header("Location: matapelajaran.php");
    exit();
}

// PROSES HAPUS DATA
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $_SESSION['message'] = "Data berhasil dihapus!";
    header("Location: matapelajaran.php");
    exit();
}

// AMBIL DATA (urutkan berdasarkan update terbaru)
$subjects = $conn->query("SELECT id, year, name, updated_at FROM subjects ORDER BY updated_at DESC");
$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Mata Pelajaran</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
    }

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

    .sidebar a:hover {
      background: #1565C0;
    }

    .sidebar a.active {
      background-color: #1565C0;
    }

    /* Logout styling khusus */
    .sidebar a.logout {
      background-color: #c62828;
    }

    .sidebar a.logout:hover {
      background-color: #e53935;
    }

    .content {
    margin-left: 200px; /* sama dengan lebar sidebar */
    padding: 20px;
    margin-top: 80px; /* sama dengan tinggi header */
}
    .success {
      background: #dff0d8;
      color: #3c763d;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 4px;
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
      background-color: #f2f2f2;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .action-btns a {
      padding: 5px 10px;
      text-decoration: none;
      margin-right: 5px;
    }

    .edit-btn {
      background: #5bc0de;
      color: white;
    }

    .delete-btn {
      background: #d9534f;
      color: white;
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
      background-color: #5cb85c;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #4cae4c;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .add-btn {
        background-color: #5cb85c;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 4px;
    }
    
    .add-btn:hover {
        background-color: #4cae4c;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Data Mata Pelajaran</h1>
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
        <a href="matapelajaran.php" class="active">
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
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="page-header">
            <h2>Data Mata Pelajaran</h2>
            <a href="tambah_matapelajaran.php" class="add-btn">+ Tambah Mata Pelajaran</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahun Ajaran</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Terakhir Update</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($subjects && $subjects->num_rows > 0): ?>
                    <?php $no = 1; while ($row = $subjects->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['year'] ?>/<?= $row['year'] + 1 ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['updated_at'])) ?></td>
                            <td>
                                <a href="edit_matapelajaran.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                                <a href="matapelajaran.php?delete=<?= $row['id'] ?>" 
                                   class="delete-btn"
                                   onclick="return confirm('Hapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Belum ada data mata pelajaran</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
