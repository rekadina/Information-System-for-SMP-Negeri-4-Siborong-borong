<?php
session_start();
include ('includes/koneksi.php');
require_once 'db_config.php';

// Tambah data ruangan
if (isset($_POST['add_room'])) {
    $year = $_POST['year'];
    $total = (int)$_POST['total'];
    
    // Cek apakah data untuk tahun ini sudah ada
    $check_stmt = $conn->prepare("SELECT id FROM rooms WHERE year = ?");
    $check_stmt->bind_param("s", $year);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update data yang sudah ada
        $stmt = $conn->prepare("UPDATE rooms SET total = ? WHERE year = ?");
    } else {
        // Tambah data baru
        $stmt = $conn->prepare("INSERT INTO rooms (total, year) VALUES (?, ?)");
    }
    
    $stmt->bind_param("is", $total, $year);
    $stmt->execute();
    
    $_SESSION['message'] = "Data ruangan berhasil diperbarui!";
    header("Location: ruangan.php");
    exit();
}

// Hapus data ruangan
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $_SESSION['message'] = "Data ruangan berhasil dihapus!";
    header("Location: ruangan.php");
    exit();
}

// Ambil semua data ruangan
$rooms_data = $conn->query("SELECT id, year, total, updated_at FROM rooms ORDER BY year DESC");
$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Ruangan Sekolah</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 0; 
            padding: 0; 
            background-color: #f4f4f4; 
            transition: margin-left 0.3s; /* Smooth transition for content */
        }

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
            transition: transform 0.3s; /* Smooth transition for sidebar hide/show */
          }

        .sidebar a {
            color: white;
            padding: 15px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

                .sidebar.hidden {
            transform: translateX(-100%); /* Hide sidebar */
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
            transition: margin-left 0.3s; /* Smooth transition for content */
        }
         .content.shifted {
            margin-left: 0; /* Adjust content margin */
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
        .form-check {
    display: flex;
    align-items: center;
    gap: 10px; /* Jarak antara checkbox dan teks */
}

.form-check-input {
    width: auto;
    margin: 0; /* Hilangkan margin default */
}

.form-check-label {
    margin: 0; /* Hilangkan margin default */
    cursor: pointer; /* Ubah kursor saat dihover */
}

    </style>
</head>
<body>
  <div class="header">
    <h1>Data Ruangan Sekolah</h1>
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
        <a href="ruangan.php" class='active'>
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
    <?php 
    if (isset($_SESSION['message'])) {
        $alert_class = strpos($_SESSION['message'], 'berhasil') !== false ? 'alert-success' : 'alert-error';
        echo '<div class="alert '.$alert_class.'">'.$_SESSION['message'].'</div>';
        unset($_SESSION['message']);
    }
    ?>
    
    <h2>Tambah Data Ruangan</h2>
    <form action="ruangan.php" method="post">
      <div class="form-group">
        <label for="year">Tahun Ajaran:</label>
        <select id="year" name="year" required>
          <?php 
          for ($i = $current_year; $i >= $current_year - 5; $i--) {
              echo "<option value='$i'>$i/" . ($i + 1) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="total">Jumlah Ruangan:</label>
        <input type="number" id="total" name="total" min="1" required>
      </div>
      <button type="submit" name="add_room">Simpan</button>
    </form>
    
    <h2>Data Ruangan per Tahun</h2>
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Tahun Ajaran</th>
          <th>Jumlah Ruangan</th>
          <th>Terakhir Diupdate</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php 
    if ($rooms_data && $rooms_data->num_rows > 0) {
        $no = 1;
        while ($row = $rooms_data->fetch_assoc()) {
            // Pastikan kolom year ada
            $year = isset($row['year']) ? $row['year'] : 'N/A';
            $total = isset($row['total']) ? $row['total'] : 0;
            $updated = isset($row['updated_at']) ? $row['updated_at'] : date('Y-m-d H:i:s');
            
            echo "<tr>
                <td>".$no++."</td>
                <td>".htmlspecialchars($year)."/".(htmlspecialchars($year)+1)."</td>
                <td>".number_format($total, 0, ',', '.')."</td>
                <td>".date('d/m/Y H:i', strtotime($updated))."</td>
                <td>
                    <a href='ruangan.php?delete=".$row['id']."' 
                       class='delete-btn'
                       onclick='return confirm(\"Hapus data ini?\")'>
                       Hapus
                    </a>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Belum ada data ruangan</td></tr>";
    }
    ?>
      </tbody>
    </table>
  </div>
    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
        }

        // Cek status sidebar saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        });
    </script>
</body>
</html>