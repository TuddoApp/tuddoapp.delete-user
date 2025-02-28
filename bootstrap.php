<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Kreait\Firebase\Factory;
use Slim\Factory\AppFactory;

// Path to your Firebase service account key file
$serviceAccountPath = __DIR__ . '/firebase-service-account.json';

// Initialize Firebase
$factory = (new Factory)->withServiceAccount($serviceAccountPath);
$auth = $factory->createAuth();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

return $app;