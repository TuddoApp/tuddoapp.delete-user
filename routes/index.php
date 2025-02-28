<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

$app->get('/', function (Request $request, Response $response, $args) {
    $html = file_get_contents(__DIR__ . "/../views/index.html");
    $response->getBody()->write($html);
    return $response;
})->setName('/');
