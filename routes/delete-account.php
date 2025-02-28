<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\FirebaseException;

$app->get('/delete-account/{token}', function ($request, $response, $args) use ($auth) {
    $token = $args['token'] ?? '';

    // Here, you'd verify the token (store & check in DB)
    if (!$token) {
        $response->getBody()->write(json_encode(['error' => "Invalid request."]));
        return $response->withStatus(400);
    }

    try {
        // Create a custom token
        // 🔹 Decode JWT and verify signature
        $decoded = JWT::decode($token, new Key($_ENV['JWT_TOKEN'], 'HS256'));

        // Convert to array for easier access
        $decodedArray = (array) $decoded;
        $uid = $decodedArray['uid'];

        // Exclua o usuário do Firebase Authentication
        $auth->deleteUser($uid);
    } catch (UserNotFound $e) {
        $response->getBody()->write(json_encode(['message' => 'Usuário não encontrado']));
        return $response->withStatus(400);
    } catch (FirebaseException $e) {
        // $response->getBody()->write(json_encode(['message' => "Firebase Error", 'error' => $e->getMessage()]));
        return $response->withStatus(400);
    } catch (InvalidArgumentException $e) {
        // $response->getBody()->write(json_encode(['message' => "Error", 'error' => $e->getMessage()]));
        return $response->withStatus(400);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['message' => "Usuário não  pode ser excluido"]));
        return $response->withStatus(400);
    }

    // Simulate account deletion (delete user from DB in real case)
    $html = file_get_contents(__DIR__ . "/../views/delete-account.html");
    $response->getBody()->write($html);
    return $response;
})->setName('/delete-account');
