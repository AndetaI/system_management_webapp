<?php

declare(strict_types=1);


error_reporting(E_ALL);

/*
 * Technical PHP errors must not be displayed
 * directly to website visitors.
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');


require_once __DIR__ . '/../util/Logger.php';
require_once __DIR__ . '/../security/ErrorHandler.php';


$logger = new Logger();

$errorHandler = new ErrorHandler($logger);
$errorHandler->register();

require_once __DIR__ . '/../config/env.php';

$page = $_GET['page'] ?? 'dashboard';

$routes = [
    'dashboard' => __DIR__ . '/../views/pages/dashboard/index.php',
    'villas' => __DIR__ . '/../views/pages/villas/index.php',
    'works' => __DIR__ . '/../views/pages/works/index.php',
    'materials' => __DIR__ . '/../views/pages/materials/index.php',
    'stock' => __DIR__ . '/../views/pages/stock/index.php',
    'suppliers' => __DIR__ . '/../views/pages/suppliers/index.php',
    'teams' => __DIR__ . '/../views/pages/teams/index.php',
    'users' => __DIR__ . '/../views/pages/users/index.php',
    'reports' => __DIR__ . '/../views/pages/reports/index.php'
];

if (!array_key_exists($page, $routes)) {
    http_response_code(404);
    exit('Page not found.');
}

require $routes[$page];