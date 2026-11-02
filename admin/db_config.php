<?php
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "smpn4sbb_admin"; // Pastikan nama database benar

// Buat koneksi dengan error reporting yang tepat
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Set karakter encoding
    $conn->set_charset("utf8mb4");
    
    // Nonaktifkan strict mode jika diperlukan
    $conn->query("SET SESSION sql_mode = ''");
    
    // Untuk testing koneksi (bisa dihapus setelah berhasil)
    if ($conn->ping()) {
    error_log("Koneksi database berhasil"); // Pesan akan masuk ke log server
}
    
} catch (mysqli_sql_exception $e) {
    error_log("Koneksi database gagal: " . $e->getMessage());
    die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
    
}



