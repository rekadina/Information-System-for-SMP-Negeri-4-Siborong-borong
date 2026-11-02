<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

$mail = new PHPMailer(true);

try {
    // Pengaturan SMTP (atau bisa pakai mail biasa)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';         // Ganti dengan SMTP host kamu
    $mail->SMTPAuth = true;
    $mail->Username = 'smpnegeri4@gmail.com.com';        // Email pengirim
    $mail->Password = 'iixz ohea gzwi mjkd';          // Password email
    $mail->SMTPSecure = 'tls';                 
    $mail->Port = 587;

    // Pengirim & Penerima
    $mail->setFrom($_POST['email'], $_POST['name']);
    $mail->addAddress('smpnegeri4sbb@gmail.com', 'smpnegeri4'); // Email tujuan

    // Konten email
    $mail->isHTML(true);
    $mail->Subject = 'Contact Form Message';
    $mail->Body    = "
        <strong>Name:</strong> {$_POST['name']}<br>
        <strong>Email:</strong> {$_POST['email']}<br>
        <strong>Subject:</strong> {$_POST['subject']}<br>
        <strong>Message:</strong> {$_POST['message']}
    ";

    $mail->send();
    echo 'OK';
} catch (Exception $e) {
    echo 'Error: ' . $mail->ErrorInfo;
}
?>
