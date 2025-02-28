<?php

date_default_timezone_set('UTC');
header('Content-type: application/json');

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\FirebaseException;

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
$token = $_REQUEST['token'] ?? null;

if ($token) {
    try {
        // Create a custom token
        // 🔹 Decode JWT and verify signature
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

        // Convert to array for easier access
        $decodedArray = (array) $decoded;
        $uid = $decodedArray['uid'];

        // Exclua o usuário do Firebase Authentication
        $auth->deleteUser($uid);

        echo json_encode(['message' => 'Usuário excluído com sucesso']);
        exit();
    } catch (UserNotFound $e) {
        echo json_encode(['message' => 'Usuário não encontrado']);
        exit();
    } catch (FirebaseException $e) {
        echo json_encode(['message' => "Firebase Error", 'error' => $e->getMessage()]);
        exit();
    } catch (InvalidArgumentException $e) {
        echo json_encode(['message' => "Error", 'error' => $e->getMessage()]);
        exit();
    } catch (Exception $e) {
        echo json_encode(['message' => "Error", 'error' => $e]);
        exit();
    }
} else {
    echo json_encode(['message' => 'No token provided']);
    exit();
}
