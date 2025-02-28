<?php

use Kreait\Firebase\Auth\UserRecord;
use PHPMailer\PHPMailer\PHPMailer;

date_default_timezone_set('UTC');

require 'vendor/autoload.php';

function sendEmail(UserRecord $user, $subject, $text): void
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    // Server settings
    $mail->isSMTP(); // Use SMTP
    $mail->Host = $_ENV['MAIL_HOST']; // SMTP server (e.g., smtp.gmail.com for Gmail)
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username =  $_ENV['MAIL_USER']; // SMTP username
    $mail->Password =  $_ENV['MAIL_PASS']; // SMTP password
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port to connect to
    $mail->CharSet = 'UTF-8';

    // Recipients
    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']); // Sender
    $mail->addAddress($user->email, $user->displayName); // Recipient

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = $subject; // Email subject
    $mail->Body = $text; // HTML email body
    // $mail->AltBody = 'Hello! This is a test email sent with PHPMailer.'; // Plain text body for non-HTML clients

    // Send the email
    $mail->send();
}
