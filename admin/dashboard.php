<?php
session_start();
require_once 'db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek jika admin belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Proses update data
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $isi = $_POST['isi'];
    
    $query = "UPDATE konten SET isi='$isi' WHERE id=$id";
    mysqli_query($koneksi, $query);     
    $_SESSION['success'] = "Data berhasil diupdate";
}

// Fungsi untuk menghitung data dengan error handling
function countData($conn, $table) {
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    } catch (Exception $e) {
        error_log("Error counting data from $table: " . $e->getMessage());
        return 0;
    }
}
// Hitung total siswa (menggunakan COUNT langsung dari tabel students)
$student_count = countData($conn, 'students');

// Hitung siswa per kelas
$students_per_class = [];
if ($conn->query("SHOW TABLES LIKE 'classes'")->num_rows > 0) {
    $result = $conn->query("
        SELECT c.class_name, COUNT(s.id) as student_count 
        FROM classes c
        LEFT JOIN students s ON c.class_name = s.class
        GROUP BY c.class_name
        ORDER BY c.class_name
    ");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students_per_class[$row['class_name']] = $row['student_count'];
        }
    }
}

// Hitung total data
$counts = [
    'teachers' => countData($conn, 'teachers'),
    'students' => countData($conn, 'students'),
    'rooms' => countData($conn, 'rooms'),
    'subjects' => countData($conn, 'subjects'),
    'news' => countData($conn, 'news'),
    'facilities' => countData($conn, 'facilities'),
    'gallery' => countData($conn, 'gallery')
];

$tables = ['teachers', 'students', 'rooms', 'subjects', 'news', 'facilities', 'gallery'];

foreach ($tables as $table) {
    $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
    if ($checkTable && $checkTable->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as total FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $counts[$table] = $row['total'];
        } else {
            $counts[$table] = 0;
            error_log("WARNING: Query gagal di tabel '$table'.");
        }
    } else {
        $counts[$table] = 0;
        error_log("WARNING: Tabel '$table' tidak ditemukan di database.");
    }
}

// AMBIL DATA TERAKHIR MATA PELAJARAN
$current_year = date('Y');
$subject_count = 0;
$stmt = $conn->prepare("SELECT COUNT(DISTINCT name) as count FROM subjects WHERE year = ?");
if ($stmt) {
    $stmt->bind_param("i", $current_year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $subject_count = $row['count'];
    }
}

$room_count = 0;
$result = $conn->query("SELECT SUM(total) as total FROM rooms");
if ($result && $row = $result->fetch_assoc()) {
    $room_count = $row['total'] ?? 0;
}


// Debug session (opsional)
// echo '<pre>'; print_r($_SESSION); echo '</pre>';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMPN4SBB</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }

        /* Header jadi biru */
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


        /* Sidebar link */
        .sidebar a {
            color: white;
            padding: 15px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

        /* Hover atau aktif → biru lebih terang */
        .sidebar a:hover, .sidebar a.active {
            background: #1565C0; /* biru lebih terang */
        }

        /* Logout tetap merah */
        .sidebar a.logout {
            background: #f44336;
        }

        .sidebar a.logout:hover {
            background: #d32f2f;
        }

      .content {
    margin-left: 200px; /* sama dengan lebar sidebar */
    padding: 20px;
    margin-top: 80px; /* sama dengan tinggi header */
}

        /* Kartu dengan aksen biru */
        .card {
            background: white;
            padding: 20px;
            margin: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0,123,255,0.3); /* biru glow */
            display: inline-block;
            width: 200px;
            border-top: 3px solid #007BFF;
        }

        .card h3 {
            margin-top: 0;
            color: #007BFF; /* judul biru */
        }

        /* Link Kelola biru */
        .card a {
            color: #007BFF;
            text-decoration: none;
        }

        .card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator'); ?></p>
    </div>

 <div class="sidebar" id="sidebar">
        <a href="dashboard.php" class="active">
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
        <a href="matapelajaran.php" >
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
        <h2>Statistik</h2>
        
        <div class="card">
            <h3>Guru&Tata Usaha</h3>
            <p>Total: <?php echo $counts['teachers']; ?></p>
            <a href="guru.php">Kelola</a>
        </div>
        
        <div class="card">
            <h3>Siswa</h3>
            <p>Total: <?php echo number_format($student_count, 0, ',', '.'); ?></p>
            <a href="siswa.php">Kelola</a>
        </div>
        
        <div class="card">
             <h3>Ruangan</h3>
             <p>Total: <?php echo number_format($room_count, 0, ',', '.'); ?></p>
             <a href="ruangan.php">Kelola</a>
        </div>
        
        <div class="card">
            <h3>Mata Pelajaran</h3>
            <p>Total: <?php echo number_format($subject_count, 0, ',', '.'); ?></p>
            <a href="matapelajaran.php">Kelola</a>
        </div>
        
        <div class="card">
            <h3>Berita</h3>
            <p>Total: <?php echo $counts['news']; ?></p>
            <a href="berita.php">Kelola</a>
        </div>
        
        <div class="card">
            <h3>Fasilitas</h3>
            <p>Total: <?php echo $counts['facilities']; ?></p>
            <a href="fasilitas.php">Kelola</a>
        </div>
        
        <div class="card">
            <h3>Galeri</h3>
            <p>Total: <?php echo $counts['gallery']; ?></p>
            <a href="galeri.php">Kelola</a>
        </div>
    </div>
</body>
</html>