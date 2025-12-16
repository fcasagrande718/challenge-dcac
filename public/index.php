<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ProductRepository.php';
require_once __DIR__ . '/../src/ProductController.php';
require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Router.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$router = new Router();
$repository = new ProductRepository(Database::getConnection());
$controller = new ProductController($repository);

$router->register('GET', '/productos', [$controller, 'list']);
$router->register('GET', '/productos/{id}', [$controller, 'show']);
$router->register('POST', '/productos', [$controller, 'create']);
$router->register('PUT', '/productos/{id}', [$controller, 'update']);
$router->register('DELETE', '/productos/{id}', [$controller, 'delete']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
