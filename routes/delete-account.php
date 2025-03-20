<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\ResponseInterface;

$app->map(['GET', 'POST'], '/delete-account/{token}', function ($request, $response, $args) use ($auth) {
    $encodedToken = $args['token'] ?? '';

    // Here, you'd verify the token (store & check in DB)
    if (!$encodedToken) {
        return $response
            ->withHeader('Location', '/error?message=' . urlencode('Token inválido'))
            ->withStatus(302);
    }

    try {
        // Decode base64url token
        $token = base64_decode(str_pad(strtr($encodedToken, '-_', '+/'), strlen($encodedToken) % 4, '=', STR_PAD_RIGHT));
        
        if (!$token) {
            return $response
                ->withHeader('Location', '/error?message=' . urlencode('Token inválido'))
                ->withStatus(302);
        }

        // Create a custom token
        // 🔹 Decode JWT and verify signature
        $decoded = JWT::decode($token, new Key($_ENV['JWT_TOKEN'], 'HS256'));
        
        // Verificar se o token expirou
        if ($decoded->exp < time()) {
            return $response
                ->withHeader('Location', '/error?message=' . urlencode('Token expirado'))
                ->withStatus(302);
        }

        // Convert to array for easier access
        $decodedArray = (array) $decoded;
        $uid = $decodedArray['uid'];

        // Exclua o usuário do Firebase Authentication
        $auth->deleteUser($uid);

        // Carregar a página de confirmação
        $html = file_get_contents(__DIR__ . "/../views/delete-account.html");
        $response->getBody()->write($html);
        
        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);

    } catch (UserNotFound $e) {
        return $response
            ->withHeader('Location', '/error?message=' . urlencode('Usuário não encontrado'))
            ->withStatus(302);
    } catch (FirebaseException $e) {
        return $response
            ->withHeader('Location', '/error?message=' . urlencode('Erro ao excluir usuário'))
            ->withStatus(302);
    } catch (Exception $e) {
        return $response
            ->withHeader('Location', '/error?message=' . urlencode('Erro ao processar solicitação'))
            ->withStatus(302);
    }
})->setName('/delete-account');
