<?php

declare(strict_types=1);

class SupplierController
{
    public function __construct(
        private SupplierRepository $supplierRepository,
        private MaterialRepository $materialRepository,
        private AuditLogger $auditLogger,
        private SupplierService $supplierService
    ) {
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('405 Method Not Allowed');
        }
    }

    public function index(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));

        if ($search !== '') {
            $suppliers = $this->supplierRepository->search($search);
        }else {
            $suppliers = $this->supplierRepository->findAll();
        }

        require __DIR__ . '/../views/pages/suppliers/index.php';
    }

    public function show(): void
    {
        Authorization::requirePermission('suppliers.view');

        $id = (int) ($_GET['id'] ?? 0);
        $supplier = $this->supplierRepository->findById($id);

        if ($supplier === null) {
            http_response_code(404);
            exit('Supplier not found.');
        }

        $errors = [];

        require __DIR__ . '/../views/pages/suppliers/show.php';
    }

    public function create(): void
    {
        Authorization::requirePermission('suppliers.create');

        $errors = [];
        $old = [];

        require __DIR__ . '/../views/pages/suppliers/create.php';
    }

    public function store(): void
    {
        Authorization::requirePermission('suppliers.create');
        $this->requirePost();
        Csrf::requireValidToken();

        $errors = $this->validateSupplier($_POST);

        if ($errors !== []) {
            $old = $_POST;
            require __DIR__ . '/../views/pages/suppliers/create.php';
            return;
        }

        $newSupplierId = $this->supplierRepository->create(
            $this->supplierData($_POST)
        );

        $this->auditLogger->record(
            'SUPPLIER_CREATED',
            'supplier',
            (int) $newSupplierId
        );

        header('Location: /?page=suppliers');
        exit;
    }

    public function edit(): void
    {
        Authorization::requirePermission('suppliers.update');

        $id = (int) ($_GET['id'] ?? 0);
        $supplier = $this->supplierRepository->findById($id);

        if ($supplier === null) {
            http_response_code(404);
            exit('Supplier not found.');
        }

        $errors = [];

        require __DIR__ . '/../views/pages/suppliers/edit.php';
    }

    public function update(): void
    {
        Authorization::requirePermission('suppliers.update');
        $this->requirePost();
        Csrf::requireValidToken();

        $id = (int) ($_GET['id'] ?? 0);
        $supplier = $this->supplierRepository->findById($id);

        if ($supplier === null) {
            http_response_code(404);
            exit('Supplier not found.');
        }

        $errors = $this->validateSupplier($_POST);

        if ($errors !== []) {
            $supplier = array_merge($supplier, $_POST);
            require __DIR__ . '/../views/pages/suppliers/edit.php';
            return;
        }

        $this->supplierRepository->update(
            $id,
            $this->supplierData($_POST)
        );
        $this->auditLogger->record(
            'SUPPLIER_UPDATED',
            'supplier',
            $id
        );

        header('Location: /?page=suppliers');
        exit;
    }

    public function delete(): void
    {
        Authorization::requirePermission('suppliers.delete');
        $this->requirePost();
        Csrf::requireValidToken();

        $id = (int) ($_GET['id'] ?? 0);

        $result = $this->supplierService->delete($id);

        if ($result['success']) {
            $this->auditLogger->record(
                'SUPPLIER_DELETED',
                'supplier',
                $id
            );

            header('Location: /?page=suppliers');
            exit;
        }

        $supplier = $this->supplierRepository->findById($id);

        if ($supplier === null) {
            http_response_code(404);
            exit('Supplier not found.');
        }

        $errors = $result['errors'];

        require __DIR__ . '/../views/pages/suppliers/show.php';
    }
    private function validateSupplier(array $data): array
    {
        $errors = [];

        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        if ($name === '' || mb_strlen($name) > 150) {
            $errors['name'] = 'Supplier name is required (max 150 characters).';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        foreach (['email' => 255, 'phone' => 50,
                  'address' => 255, 'contact_person' => 150] as $field => $max) {
            if (mb_strlen(trim((string) ($data[$field] ?? ''))) > $max) {
                $errors[$field] = "Maximum $max characters allowed.";
            }
        }

        return $errors;
    }

    private function supplierData(array $data): array
    {
        $result = [];

        foreach (['name', 'email', 'phone', 'address', 'contact_person'] as $field) {
            $value = trim((string) ($data[$field] ?? ''));
            $result[$field] = $value === '' ? null : $value;
        }

        return $result;
    }
}