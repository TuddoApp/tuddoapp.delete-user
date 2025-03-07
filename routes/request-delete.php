<?php

require_once __DIR__ . '/../utils/email.php';  // Ensure this line is at the top

use Firebase\JWT\JWT;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

$app->post('/request-delete', function (Request $request, Response $response, $args) use ($auth, $app) {
    $parsedBody = $request->getParsedBody();
    $email = filter_var($parsedBody['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response->getBody()->write(json_encode(['error' => "Invalid email"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
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
        ];

        // 🔹 Encode (Sign) JWT with HS256 algorithm
        $token = JWT::encode($payload, $_ENV['JWT_TOKEN'], 'HS256');
        $routeParser = $app->getRouteCollector()->getRouteParser();

        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . '://' . $uri->getHost();
        if ($uri->getPort() !== null && $uri->getPort() !== 80 && $uri->getPort() !== 443) {
            $baseUrl .= ':' . $uri->getPort();
        }
        
        // Encode token para URL
        $encodedToken = rtrim(strtr(base64_encode($token), '+/', '-_'), '=');
        $deleteLink = $baseUrl . $routeParser->urlFor('/delete-account', ['token' => $encodedToken]);

        $subject = "Confirmação de Exclusão de Conta";
        $emailVariables = [
            'deleteLink' => $deleteLink
        ];
        $text = loadEmailTemplate('delete-account-email', $emailVariables);

        sendEmail($user, $subject, $text);
    } catch (FailedToVerifyToken $e) {
        $message = "Error verifying ID token: " . $e->getMessage();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $message = "Email could not be sent. Error: {$e}";
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Render confirmation page
    $html = file_get_contents(__DIR__ . "/../views/request-delete.html");
    $response->getBody()->write($html);
    return $response;
})->setName('/request-delete');
