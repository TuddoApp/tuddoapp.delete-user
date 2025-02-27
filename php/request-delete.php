<?php

date_default_timezone_set('UTC');

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Kreait\Firebase\Factory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables from .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$secretKey = $_ENV['JWT_TOKEN'];

// Path to your Firebase service account key file
$serviceAccountPath = __DIR__ . '/firebase-service-account.json';

// Initialize Firebase
$factory = (new Factory)->withServiceAccount($serviceAccountPath);
$auth = $factory->createAuth();


// Simulate receiving an ID token from the client (e.g., from a POST request)
$email = $_POST['email'] ?? null;

if ($email) {
    try {
        // Create a custom token
        $user = $auth->getUserByEmail($email);

        $payload = [
            "iss" => "tuddo.org",   // Issuer
            "aud" => "tuddo.org",   // Audience
            "iat" => time(),             // Issued at
            "exp" => time() + 3600,      // Expiry time (1 hour)
            "uid" => $user->uid,          // Custom field: User ID
            "email" => $user->email, // Custom field: User email
            "role" => "admin"            // Custom field: User role
        ];

        // 🔹 Encode (Sign) JWT with HS256 algorithm
        $token = JWT::encode($payload, $secretKey, 'HS256');
        $deletionLink = "https://SEU-SITE.com/confirm-deletion?token=$token";
        $subject = "Confirmação de Exclusão de Conta";
        $text = "Clique no link abaixo para confirmar a exclusão da sua conta:<br />$deletionLink";
        sendEmail($email, $subject, $text);
        
        header('Content-type: application/json');
        echo json_encode([
            'message' => 'Success',
            'token' => $token,
            'link' => "delete-account.php?token=$token",
        ]);
        exit();
    } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
        echo "Error verifying ID token: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$e}";
    }
} else {
    echo "No ID token provided.";
}

function sendEmail($email, $subject, $text): void
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    // Server settings
    $mail->isSMTP(); // Use SMTP
    $mail->Host = 'smtp-relay.brevo.com'; // SMTP server (e.g., smtp.gmail.com for Gmail)
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'morellitecinfo@gmail.com'; // SMTP username
    $mail->Password = 'CfRHpSEKIkmaBc16'; // SMTP password
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port to connect to

    $mail->CharSet = 'UTF-8';

    // Recipients
    $mail->setFrom('tuddo@tuddo.org', 'Tuddo'); // Sender
    $mail->addAddress($email, 'Recipient Name'); // Recipient

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = $subject; // Email subject
    $mail->Body = $text; // HTML email body
    $mail->AltBody = 'Hello! This is a test email sent with PHPMailer.'; // Plain text body for non-HTML clients

    // Send the email
    $mail->send();
}
