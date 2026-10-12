<?php

declare(strict_types=1);

class StockController
{
    public function __construct(
        private StockService $stockService,
        private MaterialRepository $materialRepository,
        private SupplierRepository $supplierRepository,
        private StockRepository $stockRepository,
        private StockMovementRepository $stockMovementRepository,
        private WorkRepository $workRepository,
        private WorkAssignmentRepository $workAssignmentRepository
    ) {
    }

    private function requireAdmin(): void
    {
        if (SessionManager::role() !== 'admin') {
            http_response_code(403);
            
            require __DIR__ . '/../views/pages/errors/403.php';
            exit;
        }
    }
    
    private function requireMaterialUsagePermission(): void
    {
        Authorization::requirePermission('material_usage.create');
    }

    private function canRecordUsageForWork(int $workId): bool
    {
        if ($workId <= 0) {
            return false;
        }

        if (SessionManager::role() === 'admin') {
            return true;
        }
        /*
        if (in_array(
            SessionManager::role(),
            ['admin', 'manager'],
            true
        )) {
            return true;
        }
        */

        $userId = SessionManager::userId();

        if (!$userId) {
            return false;
        }

        return $this->workAssignmentRepository
            ->isWorkAssignedToUser($workId, (int) $userId);
    }


    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('405 Method Not Allowed');
        }
    }

    private function verifyCsrf(): void
    {
        Csrf::requireValidToken();
    }

    public function index(): void
    {
        $stocks = $this->stockRepository->findAll();
        $movements = $this->stockMovementRepository->findAll();

        $materialsInStock = 0;
        $lowStock = 0;
        $todayMovements = 0;

        $lowStockThreshold = 10;

        foreach ($stocks as $stock) {
            $quantity = (float) $stock['quantity'];

            if ($quantity > 0) {
                $materialsInStock++;
            }

            if ($quantity > 0 && $quantity < $lowStockThreshold) {
                $lowStock++;
            }
        }

        $today = date('Y-m-d');

        foreach ($movements as $movement) {
            if (substr((string) $movement['date'], 0, 10) === $today) {
                $todayMovements++;
            }
        }

        $recentMovements = array_slice($movements, 0, 10);

        require __DIR__ . '/../views/pages/stock/index.php';
    }

    public function stockInForm(): void
    {
        $this->requireAdmin();

        $materials = $this->materialRepository->findAll();
        $suppliers = $this->supplierRepository->findAll();

        $errors = [];
        $old = [];

        require __DIR__ . '/../views/pages/stock/stock-in.php';
    }

    public function movements(): void
    {
        $movements = $this->stockMovementRepository->findAll();

        require __DIR__ . '/../views/pages/stock/movements.php';
    }
    public function stockInStore(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $result = $this->stockService->receiveMaterial($_POST);

        if (!$result['success']) {
            $errors = $result['errors'];
            $old = $_POST;

            $materials = $this->materialRepository->findAll();
            $suppliers = $this->supplierRepository->findAll();

            require __DIR__ . '/../views/pages/stock/stock-in.php';
            return;
        }

        header('Location: /?page=stock');
        exit;
    }

    public function stockOutForm(): void
    {
        $this->requireMaterialUsagePermission();

        $works = $this->workRepository->findAll();
        
        if (SessionManager::role() === 'staff') {
            $works = array_values(array_filter(
                $works,
                fn (array $work): bool =>
                    $this->canRecordUsageForWork((int) $work['id'])
            ));
        }
        $materials = $this->materialRepository->findAll();

        $errors = [];
        $old = [];

        $selectedWorkId = filter_input(
            INPUT_GET,
            'work_id',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($selectedWorkId !== null) {
            if (
                $selectedWorkId === false ||
                !$this->canRecordUsageForWork($selectedWorkId)
            ) {
                http_response_code(403);
                require __DIR__ . '/../views/pages/errors/403.php';
                exit;
            }

            $selectedWork = $this->workRepository
                ->findById($selectedWorkId);

            if ($selectedWork === null) {
                http_response_code(404);
                exit('Work not found.');
            }

            $old['work_id'] = $selectedWorkId;
            $old['villa_id'] = (int) $selectedWork['villa_id'];
        }

        require __DIR__ . '/../views/pages/stock/remove.php';
    }

 
    public function stockOutStore(): void
    {
        $this->requireMaterialUsagePermission();
        $this->requirePost();
        $this->verifyCsrf();

        $workId = filter_var(
            $_POST['work_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $workId === false ||
            $workId === null ||
            !$this->canRecordUsageForWork($workId)
        ) {
            http_response_code(403);
            require __DIR__ . '/../views/pages/errors/403.php';
            exit;
        }

        $result = $this->stockService->useMaterial($_POST);

        if (!$result['success']) {
            $errors = $result['errors'] ?? [];
            $old = $_POST;

            $works = $this->workRepository->findAll();

            if (SessionManager::role() === 'staff') {
                $works = array_values(array_filter(
                    $works,
                    fn (array $work): bool =>
                        $this->canRecordUsageForWork((int) $work['id'])
                ));
            }

            $materials = $this->materialRepository->findAll();

            require __DIR__ . '/../views/pages/stock/remove.php';
            return;
        }

        header(
            'Location: /?page=works&action=show&id=' . $workId
        );
        exit;
    }

}