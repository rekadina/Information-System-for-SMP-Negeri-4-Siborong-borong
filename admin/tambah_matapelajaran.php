<?php
session_start();
include ('includes/koneksi.php');
require_once 'db_config.php';

// PROSES TAMBAH DATA
if (isset($_POST['add_subject'])) {
    $year = $_POST['year'];
    $name = trim($_POST['name']);
    
    $stmt = $conn->prepare("INSERT INTO subjects (year, name) VALUES (?, ?)");
    $stmt->bind_param("ss", $year, $name);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Mata pelajaran berhasil ditambahkan!";
        header("Location: matapelajaran.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan data: " . $conn->error;
    }
}

$current_year = date('Y');
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tambah Mata Pelajaran</title>
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

    .sidebar a:hover {
      background: #1565C0;
    }

    .sidebar a.active {
      background-color: #1565C0;
    }

    .sidebar a.logout {
      background-color: #c62828;
    }

    .sidebar a.logout:hover {
      background-color: #e53935;
    }

    .content {
    margin-left: 200px;
    padding: 20px;
    margin-top: 80px;
}
    .success, .error {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    
    .success {
      background: #dff0d8;
      color: #3c763d;
    }
    
    .error {
      background: #f2dede;
      color: #a94442;
    }

    .form-container {
      background: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      max-width: 600px;
      margin: 0 auto;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    input, select, textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    button {
      background-color: #5cb85c;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
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
    
    .btn-back {
        background-color: #6c757d;
        color: white;
        padding: 8px 12px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .btn-back:hover {
        background-color: #5a6268;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Tambah Mata Pelajaran</h1>
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
        <div class="page-header">
            <a href="matapelajaran.php" class="btn-back">← Kembali</a>
            <h2>Tambah Mata Pelajaran</h2>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="form-container">
            <form method="post">
                <div class="form-group">
                    <label for="year">Tahun Ajaran:</label>
                    <select name="year" id="year" required>
                        <?php for ($i = $current_year; $i >= $current_year-5; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?>/<?= $i+1 ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Nama Mata Pelajaran:</label>
                    <input type="text" name="name" id="name" required>
                </div>
                <button type="submit" name="add_subject">Simpan Data</button>
            </form>
        </div>
    </div>
</body>
</html>