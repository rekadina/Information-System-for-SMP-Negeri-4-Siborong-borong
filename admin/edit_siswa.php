<?php
session_start();
require_once 'db_config.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID siswa tidak valid";
    header("Location: siswa.php");
    exit();
}

$id = (int)$_GET['id'];

// Handle form submission for update
if (isset($_POST['update_student'])) {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $class = trim($_POST['class']);
    $homeroom_teacher = trim($_POST['homeroom_teacher']);
    $phone = trim($_POST['phone']);

    // Validate input
    if (empty($name) || empty($address) || empty($class)) {
        $_SESSION['error'] = "Nama, alamat, dan kelas harus diisi";
        header("Location: edit_siswa.php?id=".$id);
        exit();
    }

    try {
        $stmt = $conn->prepare("UPDATE students SET student_id=?, name=?, address=?, class=?, homeroom_teacher=?, phone=? WHERE id=?");
        $stmt->bind_param("ssssssi", $student_id, $name, $address, $class, $homeroom_teacher, $phone, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data siswa berhasil diperbarui";
            header("Location: siswa.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal memperbarui data siswa: " . $conn->error;
            header("Location: edit_siswa.php?id=".$id);
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: ".$e->getMessage();
        header("Location: edit_siswa.php?id=".$id);
        exit();
    }
}

// Get student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    $_SESSION['error'] = "Siswa tidak ditemukan";
    header("Location: siswa.php");
    exit();
}

// Get classes and their homeroom teachers
$classes_data_query = $conn->query("SELECT class_name, homeroom_teacher FROM classes ORDER BY class_name ASC");
$classes_data = [];
while ($row = $classes_data_query->fetch_assoc()) {
    $classes_data[$row['class_name']] = $row['homeroom_teacher'];
}

// Data kelas statis
$kelas_structure = [
    '7' => ['7/1', '7/2', '7/3', '7/4', '7/5', '7/6', '7/7'],
    '8' => ['8/1', '8/2', '8/3', '8/4', '8/5', '8/6', '8/7'],
    '9' => ['9/1', '9/2', '9/3', '9/4', '9/5', '9/6']
];

// Ekstrak tingkat kelas dari kelas siswa saat ini
$current_class = $student['class'];
$current_level = explode('/', $current_class)[0];

// Get teachers for dropdown
$teachers_query = $conn->query("SELECT name FROM teachers ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Siswa</title>
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

        select:disabled {
            background-color: #f0f0f0;
            color: #555;
        }
    </style>
</head>
<body>
   <div class="header">
        <h1>Edit Data Siswa</h1>
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
            <form action="edit_siswa.php?id=<?= $id ?>" method="post">
                <div class="form-group">
                    <label for="student_id">ID Siswa (NIS/NISN)</label>
                    <input type="text" id="student_id" name="student_id" 
                           value="<?= htmlspecialchars($student['student_id']) ?>" 
                           placeholder="Masukkan ID siswa">
                </div>
                
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($student['name']) ?>" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" 
                              placeholder="Masukkan alamat lengkap" required><?= htmlspecialchars($student['address']) ?></textarea>
                </div>
                
                <div class="form-group">
    <label for="class_level">Tingkat Kelas</label>
    <select id="class_level" name="class_level" onchange="showClassOptions(this.value)" required>
        <option value="">Pilih Tingkat Kelas</option>
        <?php foreach ($kelas_structure as $level => $subclasses): ?>
            <option value="<?= $level ?>" <?= $current_level == $level ? 'selected' : '' ?>>
                Kelas <?= $level ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                
                <div class="form-group">
    <label for="subclass">Sub Kelas</label>
    <select id="subclass" name="class" required onchange="updateHomeroomTeacher()">
        <option value="">Pilih Sub Kelas</option>
        <?php foreach ($kelas_structure as $level => $subclasses): ?>
            <?php foreach ($subclasses as $subclass): ?>
                <option value="<?= $subclass ?>" 
                        class="level-<?= $level ?>"
                        <?= $current_class == $subclass ? 'selected' : '' ?>
                        style="display: <?= ($current_level == $level) ? 'block' : 'none' ?>;">
                    <?= $subclass ?>
                </option>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </select>
</div>
                     
                <div class="form-group">
    <label for="homeroom_teacher">Wali Kelas</label>
    <select id="homeroom_teacher" name="homeroom_teacher" required>
        <option value="">Pilih Wali Kelas</option>
        <?php 
        $teachers_query->data_seek(0); // Reset pointer
        while ($teacher = $teachers_query->fetch_assoc()): 
            $selected = ($student['homeroom_teacher'] == $teacher['name']) ? 'selected' : '';
        ?>
            <option value="<?= $teacher['name'] ?>" <?= $selected ?>>
                <?= $teacher['name'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
                
                <div class="form-group">
                    <label for="phone">Nomor HP (Opsional)</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= htmlspecialchars($student['phone']) ?>" 
                           placeholder="Masukkan nomor HP">
                </div>
                
                <div class="form-group">
                    <a href="siswa.php" class="btn-back">Kembali</a>
                    <button type="submit" name="update_student" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

<script>
    function showClassOptions(level) {
        // Sembunyikan semua opsi sub kelas
        document.querySelectorAll('#subclass option').forEach(option => {
            if (option.value !== "") {
                option.style.display = 'none';
            }
        });
        
        // Tampilkan opsi untuk level yang dipilih
        document.querySelectorAll(`#subclass option.level-${level}`).forEach(option => {
            option.style.display = 'block';
        });
        
        // Reset pilihan subclass
        document.getElementById('subclass').value = '';
        updateHomeroomTeacher();
    }
    
    const classesData = <?php echo json_encode($classes_data); ?>;
    
    function updateHomeroomTeacher() {
        const selectedClass = document.getElementById('subclass').value;
        const homeroomTeacherSelect = document.getElementById('homeroom_teacher');
        
        if (selectedClass && classesData[selectedClass]) {
            homeroomTeacherSelect.value = classesData[selectedClass];
            homeroomTeacherSelect.disabled = true;
        } else {
            homeroomTeacherSelect.disabled = false;
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const currentLevel = '<?= $current_level ?>';
        if (currentLevel) {
            showClassOptions(currentLevel);
        }
        updateHomeroomTeacher();
    });
</script>
</body>
</html>