<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = date('Y');
    
    // Update jumlah siswa
    if (isset($_POST['students'])) {
        $students = (int)$_POST['students'];
        $stmt = $conn->prepare("INSERT INTO students (total, year) VALUES (?, ?)");
        $stmt->bind_param("ii", $students, $year);
        $stmt->execute();
    }
    
    // Update data lainnya (ruangan, mata pelajaran) dengan cara serupa
    
    $_SESSION['success'] = "Data berhasil diupdate!";
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Statistik</title>
</head>
<body>
    <h1>Update Statistik</h1>
    
    <form method="post">
        <div>
            <label>Jumlah Siswa:</label>
            <input type="number" name="students" required>
        </div>
        
        <div>
            <label>Jumlah Ruangan:</label>
            <input type="number" name="rooms" required>
        </div>
        
        <div>
            <label>Jumlah Mata Pelajaran:</label>
            <input type="number" name="subjects" required>
        </div>
        
        <button type="submit">Update</button>
    </form>
</body>

<script src="https://cdn.jsdelivr.net/npm/@srexi/purecounterjs/dist/purecounter_vanilla.js"></script>
<script>
    new PureCounter();
</script>
</html>