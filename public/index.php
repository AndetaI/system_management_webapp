<?php

declare(strict_types=1);

error_reporting(E_ALL);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../util/Logger.php';
require_once __DIR__ . '/../security/ErrorHandler.php';

$logger = new Logger();

$errorHandler = new ErrorHandler($logger);
$errorHandler->register();

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../repositories/UserRepository.php';

require_once __DIR__ . '/../services/AuthService.php';

require_once __DIR__ . '/../security/SessionManager.php';
require_once __DIR__ . '/../security/AuthMiddleware.php';

require_once __DIR__ . '/../controllers/AuthController.php';

SessionManager::start();

$pdo = Database::getConnection();

$userRepository = new UserRepository($pdo);
$authService = new AuthService($userRepository);

$authController = new AuthController($authService);

$page = $_GET['page'] ?? 'dashboard';

/*
 * Authentication routes.
 */
if ($page === 'login') {
    $authController->login();
    exit;
}

if ($page === 'logout') {
    $authController->logout();
    exit;
}

/*
 * Everything below this point requires authentication.
 */
AuthMiddleware::requireAuthentication();

$routes = [
    'dashboard' => __DIR__
        . '/../views/pages/dashboard/index.php',

    'villas' => __DIR__
        . '/../views/pages/villas/index.php',

    'works' => __DIR__
        . '/../views/pages/works/index.php',

    'materials' => __DIR__
        . '/../views/pages/materials/index.php',

    'stock' => __DIR__
        . '/../views/pages/stock/index.php',

    'suppliers' => __DIR__
        . '/../views/pages/suppliers/index.php',

    'teams' => __DIR__
        . '/../views/pages/teams/index.php',

    'users' => __DIR__
        . '/../views/pages/users/index.php',

    'reports' => __DIR__
        . '/../views/pages/reports/index.php',
];

if (!array_key_exists($page, $routes)) {
    http_response_code(404);
    exit('Page not found.');
}

require $routes[$page];