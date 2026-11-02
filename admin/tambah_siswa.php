<?php
session_start();
require_once 'db_config.php';

// Data kelas statis (bisa diganti dengan query database jika diperlukan)
$kelas_structure = [
    '7' => ['7/1', '7/2', '7/3', '7/4', '7/5', '7/6', '7/7'],
    '8' => ['8/1', '8/2', '8/3', '8/4', '8/5', '8/6', '8/7'],
    '9' => ['9/1', '9/2', '9/3', '9/4', '9/5', '9/6']
];

// Get teachers for dropdown
$classes_data_query = $conn->query("SELECT class_name, homeroom_teacher FROM classes ORDER BY class_name ASC");
$classes_data = [];
while ($row = $classes_data_query->fetch_assoc()) {
    $classes_data[$row['class_name']] = $row['homeroom_teacher'];
}

// Get all classes for dropdown
$classes_query = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
$teachers_query = $conn->query("SELECT name FROM teachers ORDER BY name ASC");

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa</title>
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

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            height: 100px;
            resize: vertical;
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

            .class-level {
            margin-bottom: 10px;
        }
        .class-option {
            display: none;
            margin-left: 20px;
        }
    </style>
     <script>
        function showClassOptions(level) {
            // Sembunyikan semua sub-kelas
            document.querySelectorAll('.class-option').forEach(el => {
                el.style.display = 'none';
            });
            
            // Tampilkan sub-kelas yang dipilih
            const subClass = document.getElementById('subclass-' + level);
            if (subClass) {
                subClass.style.display = 'block';
            }
            
            // Reset pilihan subclass ketika level berubah
            document.getElementById('subclass').value = '';
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>Tambah Data Siswa</h1>
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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="tambah_siswa.php" method="post">
                <div class="form-group">
                    <label for="student_id">ID Siswa (NIS/NISN)</label>
                    <input type="text" id="student_id" name="student_id" placeholder="Masukkan ID siswa">
                </div>
                
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" placeholder="Masukkan alamat lengkap" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="class_level">Tingkat Kelas</label>
                    <select id="class_level" name="class_level" onchange="showClassOptions(this.value)" required>
                        <option value="">Pilih Tingkat Kelas</option>
                        <?php foreach ($kelas_structure as $level => $subclasses): ?>
                            <option value="<?= $level ?>">Kelas <?= $level ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subclass">Sub Kelas</label>
                    <select id="subclass" name="class" required>
                        <option value="">Pilih Sub Kelas</option>
                        <?php foreach ($kelas_structure as $level => $subclasses): ?>
                            <div id="subclass-<?= $level ?>" class="class-option">
                                <?php foreach ($subclasses as $subclass): ?>
                                    <option value="<?= $subclass ?>" class="level-<?= $level ?>"><?= $subclass ?></option>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
    <label for="homeroom_teacher">Wali Kelas</label>
    <select id="homeroom_teacher" name="homeroom_teacher" required>
        <option value="">Pilih Wali Kelas</option>
        <?php 
        // Tetap tampilkan semua guru sebagai fallback
        $teachers_query->data_seek(0);
        while ($teacher = $teachers_query->fetch_assoc()): 
        ?>
            <option value="<?= $teacher['name'] ?>"><?= $teacher['name'] ?></option>
        <?php endwhile; ?>
    </select>
</div>
                
                <div class="form-group">
                    <label for="phone">Nomor HP (Opsional)</label>
                    <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor HP">
                </div>
                
                <div class="form-group">
                    <a href="siswa.php" class="btn-back">Kembali</a>
                    <button type="submit" name="add_student" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
        <script>
    // Data kelas dan wali kelas dari PHP ke JavaScript
    const classesData = <?php echo json_encode($classes_data); ?>;
    
    function updateHomeroomTeacher() {
        const selectedClass = document.getElementById('subclass').value;
        const homeroomTeacherSelect = document.getElementById('homeroom_teacher');
        
        if (selectedClass && classesData[selectedClass]) {
            // Set nilai wali kelas sesuai data kelas
            homeroomTeacherSelect.value = classesData[selectedClass];
            
            // Non-aktifkan select jika wali kelas sudah ditentukan
            homeroomTeacherSelect.disabled = true;
        } else {
            // Aktifkan kembali jika kelas tidak punya wali kelas
            homeroomTeacherSelect.disabled = false;
        }
    }
    
    // Panggil fungsi saat kelas berubah
    document.getElementById('subclass').addEventListener('change', updateHomeroomTeacher);
    
    // Jalankan saat halaman dimuat (untuk edit siswa)
    document.addEventListener('DOMContentLoaded', function() {
        updateHomeroomTeacher();
    });
</script>
</body> 
</html>