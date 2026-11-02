<?php
session_start();
include ('includes/koneksi.php');
require_once 'db_config.php';

// Cek jika admin belum login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Periksa apakah ada ID guru yang dikirim
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Ambil data foto guru (kalau ada)
    $stmt = $conn->prepare("SELECT photo FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    // Hapus file foto dari folder kalau ada
    if ($teacher && $teacher['photo'] && file_exists($teacher['photo'])) {
        unlink($teacher['photo']);
    }

    // Hapus data guru dari database
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Redirect balik ke guru.php dengan pesan sukses
    header("Location: guru.php?success=2");
    exit();
} else {
    // Kalau tidak ada ID yang dikirim, balik ke guru.php
    header("Location: guru.php");
    exit();
}
?>
