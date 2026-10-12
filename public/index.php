<?php

declare(strict_types=1);
ini_set('pcre.jit', '0');

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
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/WorkProgressRepository.php';
require_once __DIR__ . '/../repositories/MaterialRepository.php';
require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../repositories/StockMovementRepository.php';
require_once __DIR__ . '/../repositories/DashboardRepository.php';
require_once __DIR__ . '/../repositories/ReportRepository.php';
/*
 * Services.
 */
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/WorkService.php';
require_once __DIR__ . '/../services/VillaService.php';
require_once __DIR__ . '/../services/MaterialService.php';
require_once __DIR__ . '/../services/StockService.php';
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
require_once __DIR__ . '/../controllers/VillaController.php';
require_once __DIR__ . '/../controllers/MaterialController.php';
require_once __DIR__ . '/../controllers/SupplierController.php';
require_once __DIR__ . '/../controllers/StockController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/ReportController.php';

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
$dashboardRepository = new DashboardRepository($pdo);
$dashboardController = new DashboardController($dashboardRepository);

$reportRepository = new ReportRepository($pdo);
$reportController = new ReportController($reportRepository);

$userRepository = new UserRepository($pdo);
$authService = new AuthService($userRepository);

$authController = new AuthController($authService);

$workRepository = new WorkRepository($pdo);
$villaRepository = new VillaRepository($pdo);
$villaTypologyRepository = new VillaTypologyRepository($pdo);
$workAssignmentRepository = new WorkAssignmentRepository($pdo);

$teamRepository = new TeamRepository($pdo);

$materialUsageRepository = new MaterialUsageRepository($pdo);
$workProgressRepository = new WorkProgressRepository($pdo);

$materialRepository = new MaterialRepository($pdo);
$supplierRepository = new SupplierRepository($pdo);
$stockRepository = new StockRepository($pdo);
$stockMovementRepository = new StockMovementRepository($pdo);

$workService = new WorkService(
    $pdo,
    $workRepository,
    $villaRepository,
    $workAssignmentRepository,
    $userRepository,
    $teamRepository,
    $materialUsageRepository,
    $workProgressRepository
);

$workController = new WorkController(
    $workService,
    $workRepository,
    $workAssignmentRepository,
    $villaRepository,
    $userRepository,
    $teamRepository
);

$villaService = new VillaService(
    $villaRepository,
    $villaTypologyRepository,
    $workRepository
);
 $villaController = new VillaController(
    $villaService,
    $villaRepository,
    $villaTypologyRepository
);

$materialService = new MaterialService(
    $materialRepository,
    $supplierRepository,
    $stockRepository,
    $stockMovementRepository,
    $materialUsageRepository,
    $pdo
);

$materialController = new MaterialController(
    $materialRepository,
    $materialService,
    $supplierRepository
);

$supplierController = new SupplierController(
    $supplierRepository,
    $materialRepository
);
$stockService = new StockService(
    $pdo,
    $stockRepository,
    $stockMovementRepository,
    $materialUsageRepository,
    $materialRepository,
    $workRepository,
    $supplierRepository
);
$stockController = new StockController(
    $stockService,
    $materialRepository,
    $supplierRepository,
    $stockRepository,
    $stockMovementRepository,
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

/*
 * Everything below this point requires authentication.
 */
AuthMiddleware::requireAuthentication();

/*
 * Logout is authenticated + POST + CSRF protected.
 */
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
 * Protected routes with server-side RBAC.
 */
switch ($page) {

    case 'dashboard':
        $dashboardController->index();
        break;

    case 'villas':
        $action = $_GET['action'] ?? 'index';
         switch ($action) {
            case 'index':
                $villaController->index();
                break;

            case 'show':
                $id = filter_input(
                    INPUT_GET,
                    'id',
                    FILTER_VALIDATE_INT
                );

                if ($id === false || $id === null || $id <= 0) {
                    http_response_code(400);
                    echo 'Invalid villa ID.';
                    break;
                }

                $villaController->show($id);
                break;

            case 'create':
                $villaController->create();
                break;

            case 'store':
                $villaController->store();
                break;

            case 'edit':
                $id = filter_input(
                    INPUT_GET,
                    'id',
                    FILTER_VALIDATE_INT
                );

                if ($id === false || $id === null || $id <= 0) {
                    http_response_code(400);
                    echo 'Invalid villa ID.';
                    break;
                }

                $villaController->edit($id);
                break;
            
            case 'update':
                $id = filter_input(
                    INPUT_GET,
                    'id',
                    FILTER_VALIDATE_INT
                );

                if ($id === false || $id === null || $id <= 0) {
                    http_response_code(400);
                    echo 'Invalid villa ID.';
                    break;
                }
                $villaController->update($id);
                break;
            
            case 'delete':
                $id = filter_input(
                    INPUT_GET,
                    'id',
                    FILTER_VALIDATE_INT
                );

                if ($id === false || $id === null || $id <= 0) {
                    http_response_code(400);
                    echo 'Invalid villa ID.';
                    break;
                }

                $villaController->delete($id);
                break;


            default:
                http_response_code(404);
                echo 'Action not found.';
         }

        break;

    case 'works':
        $action = $_GET['action'] ?? 'index';
        
        if ($action === 'index') {
            $workController->index();
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

        if ($action === 'show') {
            $id = filter_input(
                INPUT_GET,
                'id',
                FILTER_VALIDATE_INT
            );

            if ($id === false || $id === null || $id <= 0) {
                http_response_code(400);
                echo 'Invalid work ID.';
                break;
            }

            $workController->show($id);
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
        if ($action === 'updateProgress'){
            $workController->updateProgress();
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
    Authorization::requirePermission('materials.view');

    $action = $_GET['action'] ?? 'index';

    switch ($action) {
        case 'index':
            $materialController->index();
            break;

        case 'show':
            $materialController->show();
            break;

        case 'create':
            $materialController->create();
            break;

        case 'store':
            $materialController->store();
            break;

        case 'edit':
            $materialController->edit();
            break;

        case 'update':
            $materialController->update();
            break;

        case 'delete':
            $materialController->delete();
            break;

        default:
            http_response_code(404);
            echo 'Material action not found.';
    }

    break;

    case 'stock':
        Authorization::requirePermission('materials.view');
        $action = $_GET['action'] ?? 'index';

        switch ($action){
            case 'index':
                $stockController->index();
                break;


            case 'in':
                $stockController->stockInForm();
                break;

            case 'store-in':
                $stockController->stockInStore();
                break;
            
            case'movements':
                $stockController->movements();
                break;

            case 'out':
                $stockController->stockOutForm();
                break;
            case 'store-out':
                $stockController->stockOutStore();
                break;
            default:
                http_response_code(404);
                exit('Stock action not found.');
        }

        break;
    case 'suppliers':
        $action = $_GET['action'] ?? 'index';

        switch ($action){
            case 'index':
                $supplierController->index();
                break;
            case 'create':
                $supplierController->create();
                break;
            case 'store':
                $supplierController->store();
                break;

            case 'edit':
                $supplierController->edit();
                break;

            case 'update':
                $supplierController->update();
                break;

            case 'delete':
                $supplierController->delete();
                break;

            default:
                http_response_code(404);
                exit('Action not found.');
        }
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
        Authorization::requirePermission('reports.view');

        $action = $_GET['action'] ?? 'index';

        switch ($action) {
            case 'index':
                $reportController->index();
                break;

            case 'villa-details':
                $reportController->villaDetails();
                break;

            default:
                http_response_code(404);
                echo 'Report action not found.';
        }

        break;



    default:
        http_response_code(404);

        echo 'Page not found.';

        break;
}