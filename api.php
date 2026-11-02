<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// Endpoint untuk mendapatkan data guru
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'teachers') {
    $teachers = $conn->query("SELECT * FROM teachers ORDER BY name ASC");
    $data = [];
    while ($teacher = $teachers->fetch_assoc()) {
        $data[] = $teacher;
    }
    echo json_encode($data);
    exit();
}

// Endpoint untuk mendapatkan jumlah siswa
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'students_count') {
    $result = $conn->query("SELECT total FROM students ORDER BY year DESC LIMIT 1");
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['total'] ?? 0]);
    exit();
}

// Endpoint untuk mendapatkan jumlah ruangan
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'rooms_count') {
    $result = $conn->query("SELECT SUM(total) as total FROM rooms");
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['total'] ?? 0]);
    exit();
}

// Endpoint untuk mendapatkan jumlah mata pelajaran
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'subjects_count') {
    $current_year = date('Y');
    $stmt = $conn->prepare("SELECT total FROM subjects WHERE year = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->bind_param("i", $current_year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['total'] ?? 0]);
    exit();
}

// Endpoint untuk mendapatkan berita
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'news') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $news = $conn->query("SELECT * FROM news ORDER BY publish_date DESC LIMIT $limit");
    $data = [];
    while ($item = $news->fetch_assoc()) {
        $data[] = $item;
    }
    echo json_encode($data);
    exit();
}

// Endpoint untuk mendapatkan fasilitas
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'facilities') {
    $facilities = $conn->query("SELECT * FROM facilities ORDER BY name ASC");
    $data = [];
    while ($item = $facilities->fetch_assoc()) {
        $data[] = $item;
    }
    echo json_encode($data);
    exit();
}

// Endpoint untuk mendapatkan galeri
if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'gallery') {
    // Parameter opsional
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0; // 0 = tanpa limit
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    // Bangun query dasar
    $query = "SELECT * FROM gallery";
    
    // Tambahkan filter kategori jika ada
    if ($category) {
        $query .= " WHERE category = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $category);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    // Tambahkan limit jika ada
    if ($limit > 0) {
        $query .= " LIMIT ?";
        $stmt = $conn->prepare($query);
        if ($category) {
            $stmt->bind_param("si", $category, $limit);
        } else {
            $stmt->bind_param("i", $limit);
        }
    }
    
    // Eksekusi query
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($item = $result->fetch_assoc()) {
        $data[] = $item;
    }
    
    echo json_encode($data);
    exit();
}