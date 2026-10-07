<?php

declare(strict_types=1);

error_reporting(E_ALL);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

/*
 * Error handling.
 */
require_once __DIR__ . '/../util/Logger.php';
require_once __DIR__ . '/../security/ErrorHandler.php';

$logger = new Logger();

$errorHandler = new ErrorHandler($logger);
$errorHandler->register();

/*
 * Configuration.
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

/*
 * Repositories.
 */
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkAssignmentRepository.php';
require_once __DIR__ . '/../repositories/TeamRepository.php';
require_once __DIR__ . '/../repositories/MaterialUsageRepository.php';
/*
 * Services.
 */
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/WorkService.php';

/*
 * Security.
 */
require_once __DIR__ . '/../security/SessionManager.php';
require_once __DIR__ . '/../security/AuthMiddleware.php';
require_once __DIR__ . '/../security/Authorization.php';
require_once __DIR__ . '/../security/Csrf.php';

/*
 * Controllers.
 */
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/WorkController.php';

/*
 * Utilities.
 */
require_once __DIR__ . '/../util/Validator.php';
/*
 * Start session.
 */
SessionManager::start();

/*
 * Database connection.
 */
$pdo = Database::getConnection();

/*
 * Authentication dependencies.
 */
$userRepository = new UserRepository($pdo);
$authService = new AuthService($userRepository);

$authController = new AuthController($authService);

$workRepository = new WorkRepository($pdo);
$villaRepository = new VillaRepository($pdo);

$workAssignmentRepository = new WorkAssignmentRepository($pdo);

$teamRepository = new TeamRepository($pdo);

$materialUsageRepository = new MaterialUsageRepository($pdo);

$workService = new WorkService(
    $pdo,
    $workRepository,
    $villaRepository,
    $workAssignmentRepository,
    $userRepository,
    $teamRepository,
    $materialUsageRepository
);

$workController = new WorkController(
    $workService,
    $workRepository,
    $workAssignmentRepository
);

/*
 * Determine requested page.
 */
$page = $_GET['page'] ?? 'dashboard';

/*
 * Public authentication routes.
 */
if ($page === 'login') {
    $authController->login();
    exit;
}

if ($page === 'logout') {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Method not allowed.';
        exit;
    }
    
    Csrf::requireValidToken();

    $authController->logout();
    exit;
}

/*
 * Everything below this point requires authentication.
 */
AuthMiddleware::requireAuthentication();

/*
 * Protected routes with server-side RBAC.
 */
switch ($page) {

    case 'dashboard':
        Authorization::requirePermission(
            'dashboard.view'
        );

        require __DIR__
            . '/../views/pages/dashboard/index.php';

        break;

    case 'villas':
        Authorization::requirePermission(
            'villas.view'
        );

        require __DIR__
            . '/../views/pages/villas/index.php';

        break;

    case 'works':

    $action = $_GET['action'] ?? 'index';

    if ($action === 'index') {
        Authorization::requirePermission(
            'works.view'
        );

        require __DIR__
            . '/../views/pages/works/index.php';

        break;
    }

    if ($action === 'create') {
        $workController->create();
        break;
    }

    if ($action === 'edit') {
        $workId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if ($workId === false || $workId === null) {
            http_response_code(400);
            echo 'Invalid work ID.';
            break;
        }

        $workController->edit($workId);
        break;
    }

    if ($action === 'update') {
        $workId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if ($workId === false || $workId === null) {
            http_response_code(400);
            echo 'Invalid work ID.';
            break;
        }

        $workController->update($workId);
        break;
    }

    if ($action === 'delete') {
        $workId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if ($workId === false || $workId === null) {
            http_response_code(400);
            echo 'Invalid work ID.';
            break;
        }

        $workController->delete($workId);
        break;
    }

    http_response_code(404);
    echo 'Action not found.';
    break;

    case 'materials':
        Authorization::requirePermission(
            'materials.view'
        );

        require __DIR__
            . '/../views/pages/materials/index.php';

        break;

    case 'stock':
        Authorization::requirePermission(
            'stock.view'
        );

        require __DIR__
            . '/../views/pages/stock/index.php';

        break;

    case 'suppliers':
        Authorization::requirePermission(
            'suppliers.view'
        );

        require __DIR__
            . '/../views/pages/suppliers/index.php';

        break;

    case 'teams':
        Authorization::requirePermission(
            'teams.view'
        );

        require __DIR__
            . '/../views/pages/teams/index.php';

        break;

    case 'users':
        Authorization::requirePermission(
            'users.view'
        );

        require __DIR__
            . '/../views/pages/users/index.php';

        break;

    case 'reports':
        Authorization::requirePermission(
            'reports.view'
        );

        require __DIR__
            . '/../views/pages/reports/index.php';

        break;

    default:
        http_response_code(404);

        echo 'Page not found.';

        break;
}