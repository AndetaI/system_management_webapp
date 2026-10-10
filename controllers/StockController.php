<?php

declare(strict_types=1);

class StockController
{
    public function __construct(
        private StockService $stockService,
        private MaterialRepository $materialRepository,
        private SupplierRepository $supplierRepository,
        private StockRepository $stockRepository,
        private StockMovementRepository $stockMovementRepository
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

}