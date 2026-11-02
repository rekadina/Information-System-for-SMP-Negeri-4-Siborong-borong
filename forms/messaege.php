<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $tanggal = $_POST['date'];
    $message = $_POST['message'];

    try {
        // Pengaturan SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'smpnegeri4sbb@gmail.com'; // Email admin
        $mail->Password = 'iixz ohea gzwi mjkd'; // Gunakan App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Pengirim dan penerima
        $mail->setFrom($email, $name);
        $mail->addAddress('smpnegeri4sbb@gmail.com');

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = 'Pesan Baru dari Form Website';
        $mail->Body    = "
            <h3>Pesan dari Website SMP Negeri 4 Siborongborong</h3>
            <p><strong>Nama:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Nomor HP:</strong> $phone</p>
            <p><strong>Tanggal:</strong> $tanggal</p>
            <p><strong>Pesan:</strong><br>$message</p>
        ";

        $mail->send();
        echo "Pesan berhasil dikirim!";
    } catch (Exception $e) {
        echo "Gagal mengirim pesan. Error: {$mail->ErrorInfo}";
    }
}
?>
