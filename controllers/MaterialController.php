<?php

declare(strict_types=1);

class MaterialController
{
    public function __construct(
        private MaterialRepository $materialRepository,
        private MaterialService $materialService,
        private SupplierRepository $supplierRepository
    ) {
    }

    private function requireAdmin(): void
    {
        if (SessionManager::role() !== 'admin') {
            http_response_code(403);
            exit('403 Forbidden');
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
        $search = trim((string) ($_GET['search'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));
        $stockStatus = (string) ($_GET['stock'] ?? '');

        // Whitelist accepted stock filters.
        $allowedStockFilters = [
            '',
            'in_stock',
            'low_stock',
            'out_of_stock',
        ];

        if (!in_array($stockStatus, $allowedStockFilters, true)) {
            $stockStatus = '';
        }

        $categories = $this->materialRepository->findCategories();

        // Only accept existing categories.
        if ($category !== '' && !in_array($category, $categories, true)) {
            $category = '';
        }

        $materials = $this->materialRepository->search(
            $search,
            $category,
            $stockStatus
        );

        require __DIR__ . '/../views/pages/materials/index.php';
    }


    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $material = $this->materialRepository->findById($id);

        if ($material === null) {
            http_response_code(404);
            exit('Material not found.');
        }

        require __DIR__ . '/../views/pages/materials/show.php';
    }

    public function create(): void
    {
        $this->requireAdmin();

        $suppliers = $this->supplierRepository->findAll();
        $errors = [];
        $old = [];

        require __DIR__ . '/../views/pages/materials/create.php';
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $result = $this->materialService->create($_POST);

        if (!$result['success']) {
            $errors = $result['errors'];
            $old = $_POST;
            $suppliers = $this->supplierRepository->findAll();

            require __DIR__ . '/../views/pages/materials/create.php';
            return;
        }

        header('Location: /?page=materials');
        exit;
    }

    public function edit(): void
    {
        $this->requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        $material = $this->materialRepository->findById($id);

        if ($material === null) {
            http_response_code(404);
            exit('Material not found.');
        }

        $suppliers = $this->supplierRepository->findAll();
        $errors = [];

        require __DIR__ . '/../views/pages/materials/edit.php';
    }

    public function update(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        $result = $this->materialService->update($id, $_POST);

        if (!$result['success']) {
            $errors = $result['errors'];
            $material = array_merge(
                $this->materialRepository->findById($id) ?? [],
                $_POST
            );
            $suppliers = $this->supplierRepository->findAll();

            require __DIR__ . '/../views/pages/materials/edit.php';
            return;
        }

        header('Location: /?page=materials');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        $result = $this->materialService->delete($id);

        if (!$result['success']) {
            http_response_code(400);
            exit(implode(' ', array_merge(
                ...array_map(
                    static fn($errors) => (array) $errors,
                    array_values($result['errors'])
                )
            )));
        }

        header('Location: /?page=materials');
        exit;
    }
}
