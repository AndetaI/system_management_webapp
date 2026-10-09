<?php

declare(strict_types=1);

class MaterialService
{
    public function __construct(
        private MaterialRepository $materialRepository,
        private SupplierRepository $supplierRepository,
        private StockRepository $stockRepository,
        private StockMovementRepository $stockMovementRepository,
        private MaterialUsageRepository $materialUsageRepository,
        private PDO $pdo
        ) {
    }

    private function validatePrice(Validator $validator, mixed $price): void
    {
        $value = trim((string) ($price ?? ''));

        if (
            !preg_match('/^\d+(?:\.\d{1,2})?$/D', $value)
            || strlen(explode('.', $value)[0]) > 8
            || (float) $value > 99999999.99
        ) {
            $validator->addError(
                'price',
                'Price must be between 0 and 99999999.99, with up to 2 decimal places.'
            );
        }
    }

    public function create(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Material name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                150,
                'Material name cannot exceed 150 characters.'
            )
            ->required(
                'category',
                $data['category'] ?? null,
                'Material category is required.'
            )
            ->maxLength(
                'category',
                $data['category'] ?? null,
                100,
                'Material category cannot exceed 100 characters.'
            )
            ->required(
                'unit',
                $data['unit'] ?? null,
                'Material unit is required.'
            )
            ->maxLength(
                'unit',
                $data['unit'] ?? null,
                50,
                'Material unit cannot exceed 50 characters.'
            )
            ->required(
                'price',
                $data['price'] ?? null,
                'Material price is required.'
            )
            ->required(
                'supplier_id',
                $data['supplier_id'] ?? null,
                'Supplier is required.'
            )
            ->positive(
                'supplier_id',
                $data['supplier_id'] ?? null,
                'Invalid supplier.'
            );
        $this->validatePrice(
            $validator,
            $data['price'] ?? null
        );

        $initialQuantity = trim(
            (string) ($data['initial_quantity'] ?? '0')
        );

        if (
            !preg_match('/^\d+(?:\.\d{1,2})?$/D', $initialQuantity)
            || strlen(explode('.', $initialQuantity)[0]) > 8
            || (float) $initialQuantity > 99999999.99
        ) {
            $validator->addError(
                'initial_quantity',
                'Initial quantity must be between 0 and 99999999.99, with up to 2 decimal places.'
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $supplierId = (int) $data['supplier_id'];

        // Business rule:
        // The selected supplier must actually exist.
        $supplier = $this->supplierRepository->findById(
            $supplierId
        );

        if ($supplier === null) {
            $validator->addError(
                'supplier_id',
                'The selected supplier does not exist.'
            );

            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $pdo = $this->pdo;

        $pdo->beginTransaction();

        try {
            // 1. Create the material.
            $materialId = $this->materialRepository->create([
                'supplier_id' => $supplierId,
                'name' => trim((string) $data['name']),
                'category' => trim((string) $data['category']),
                'unit' => trim((string) $data['unit']),
                'price' => trim((string) $data['price']),
                'description' => isset($data['description'])
                    ? trim((string) $data['description'])
                    : null,
            ]);

            // 2. Initialize stock at initial quantity
            $this->stockRepository->create($materialId, 
            (float) $initialQuantity);

            // 3. Save both operations together.
            $pdo->commit();

            return [
                'success' => true,
                'id' => $materialId,
            ];

        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function update(int $materialId, array $data): array
    {
        $validator = new Validator();

        if ($materialId <= 0) {
            $validator->addError(
                'material_id',
                'Invalid material.'
            );
        }

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Material name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                150,
                'Material name cannot exceed 150 characters.'
            )
            ->required(
                'category',
                $data['category'] ?? null,
                'Material category is required.'
            )
            ->maxLength(
                'category',
                $data['category'] ?? null,
                100,
                'Material category cannot exceed 100 characters.'
            )
            ->required(
                'unit',
                $data['unit'] ?? null,
                'Material unit is required.'
            )
            ->maxLength(
                'unit',
                $data['unit'] ?? null,
                50,
                'Material unit cannot exceed 50 characters.'
            )
            ->required(
                'price',
                $data['price'] ?? null,
                'Material price is required.'
            )
            ->required(
                'supplier_id',
                $data['supplier_id'] ?? null,
                'Supplier is required.'
            )
            ->positive(
                'supplier_id',
                $data['supplier_id'] ?? null,
                'Invalid supplier.'
            );

        $this->validatePrice(
            $validator,
            $data['price'] ?? null
        );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $material = $this->materialRepository->findById($materialId);

        if ($material === null) {
            $validator->addError(
                'material_id',
                'The selected material does not exist.'
            );
        }

        $supplierId = (int) $data['supplier_id'];
        $supplier = $this->supplierRepository->findById($supplierId);
        if ($supplier === null) {
            $validator->addError(
                'supplier_id',
                'The selected supplier does not exist.'
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $this->materialRepository->update($materialId, [
            'supplier_id' => $supplierId,
            'name' => trim((string) $data['name']),
            'category' => trim((string) $data['category']),
            'unit' => trim((string) $data['unit']),
            'price' => trim((string) $data['price']),
            'description' => isset($data['description'])
                ? trim((string) $data['description'])
                : null,
        ]);

        return [
            'success' => true,
        ];
    }


public function delete(int $materialId): array
{
    $validator = new Validator();

    if ($materialId <= 0) {
        $validator->addError(
            'material_id',
            'Invalid material.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Material must exist.
     */
    if (
        $this->materialRepository->findById(
            $materialId
        ) === null
    ) {
        $validator->addError(
            'material_id',
            'Material not found.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }


    /*
     * Preserve stock movement history.
     */
    if (
        $this->stockMovementRepository
            ->existsByMaterialId($materialId)
    ) {
        $validator->addError(
            'material_id',
            'Cannot delete this material because stock movement history exists.'
        );
    }

    /*
     * Preserve material usage history.
     */
    if (
        $this->materialUsageRepository
            ->existsByMaterialId($materialId)
    ) {
        $validator->addError(
            'material_id',
            'Cannot delete this material because material usage history exists.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    $this->pdo->beginTransaction();

    try {
        // Delete the material's stock record first.
        $this->stockRepository->deleteByMaterial($materialId);

        // Then delete the material itself.
        $this->materialRepository->delete($materialId);

        $this->pdo->commit();

        return [
            'success' => true,
        ];

    } catch (Throwable $exception) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        throw $exception;
    }
}
}