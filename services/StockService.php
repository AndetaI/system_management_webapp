<?php

declare(strict_types=1);

class StockService
{
    public function __construct(
        private PDO $pdo,
        private StockRepository $stockRepository,
        private StockMovementRepository $stockMovementRepository,
        private MaterialUsageRepository $materialUsageRepository,
        private MaterialRepository $materialRepository,
        private WorkRepository $workRepository,
        private SupplierRepository $supplierRepository
    ) {
    }
public function useMaterial(array $data): array
{
    $validator = new Validator();

    $validator
        ->required(
            'work_id',
            $data['work_id'] ?? null,
            'Work is required.'
        )
        ->positive(
            'work_id',
            $data['work_id'] ?? null,
            'Invalid work.'
        )
        ->required(
            'material_id',
            $data['material_id'] ?? null,
            'Material is required.'
        )
        ->positive(
            'material_id',
            $data['material_id'] ?? null,
            'Invalid material.'
        )
        ->required(
            'quantity',
            $data['quantity'] ?? null,
            'Quantity is required.'
        )
        ->positive(
            'quantity',
            $data['quantity'] ?? null,
            'Quantity must be greater than zero.'
        )
        ->required(
            'date',
            $data['date'] ?? null,
            'Usage date is required.'
        )
        ->date(
            'date',
            $data['date'] ?? null,
            'Usage date must be a valid date.'
        );

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    $workId = (int) $data['work_id'];
    $materialId = (int) $data['material_id'];
    $quantity = (float) $data['quantity'];

    $quantityInput = trim((string) $data['quantity']);

    /* quantity validation*/
    if (
        !preg_match('/^\d+(?:\.\d{1,2})?$/D', $quantityInput)
        || $quantity <= 0
        || $quantity > 99999999.99
    ) {
        return [
            'success' => false,
            'errors' => [
                'quantity' => [
                    'Enter a positive quantity with up to 2 decimal places.'
                ]
            ]
        ];
    }

    /*
     * Business rule:
     * Work must exist and belong to the selected villa
     */
    $work = $this->workRepository->findById($workId);
    
    if ($work === null) {
        $validator->addError(
            'work_id',
            'The selected work does not exist.'
        );
    }
  
    $villaId = filter_var(
        $data['villa_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($villaId === false || $villaId === null || $villaId <= 0) {
        $validator->addError(
            'villa_id',
            'Please select a valid villa.'
        );
    } elseif (
        $work !== null &&
        (int) $work['villa_id'] !== $villaId
    ) {
        $validator->addError(
            'villa_id',
            'The selected work does not belong to the selected villa.'
        );
    }

    /*
     * Business rule:
     * Material must exist.
     */
    if (
        $this->materialRepository->findById($materialId)
        === null
    ) {
        $validator->addError(
            'material_id',
            'The selected material does not exist.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }


    try {
        $this->pdo->beginTransaction();

        /*
         * Read AND lock the stock row.
         */
        $stock = $this->stockRepository
            ->findByMaterialForUpdate($materialId);

        if ($stock === null) {
            $this->pdo->rollBack();

            return [
                'success' => false,
                'errors' => [
                    'quantity' => [
                        'No stock is available for this material.',
                    ],
                ],
            ];
        }

        $availableQuantity = (float) $stock['quantity'];

        /*
         * Business rule:
         * Requested usage cannot exceed available stock.
         */
        if ($quantity > $availableQuantity) {
            $this->pdo->rollBack();

            return [
                'success' => false,
                'errors' => [
                    'quantity' => [
                        'Requested quantity exceeds available stock.',
                    ],
                ],
            ];
        }

        $newQuantity = $availableQuantity - $quantity;

        /*
         * Record material usage.
         */
        $usageId = $this->materialUsageRepository->create([
            'work_id' => $workId,
            'material_id' => $materialId,
            'quantity' => $quantity,
            'date' => $data['date'],
        ]);

        /*
         * Record OUT movement.
         */
        $movementId = $this->stockMovementRepository->create([
            'material_id' => $materialId,
            'type' => 'OUT',
            'quantity' => $quantity,
            'date' => date('Y-m-d H:i:s'),
            'description' => 'Material used for work ID '
                . $workId,
        ]);

        /*
         * Update warehouse quantity.
         */
        $this->stockRepository->updateQuantity(
            $materialId,
            $newQuantity
        );

        $this->pdo->commit();

        return [
            'success' => true,
            'usage_id' => $usageId,
            'movement_id' => $movementId,
            'remaining_stock' => $newQuantity,
        ];

    } catch (Throwable $exception) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        throw $exception;
    }
}

public function receiveMaterial(array $data): array
{
    $materialId = filter_var(
        $data['material_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $supplierId = filter_var(
        $data['supplier_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $quantity = trim((string) ($data['quantity'] ?? ''));
    $description = trim((string) ($data['description'] ?? ''));

    $errors = [];

    // Validate material.
    if (!$materialId) {
        $errors['material_id'] = ['Select a valid material.'];
    } elseif (
        $this->materialRepository->findById($materialId) === null
    ) {
        $errors['material_id'] = ['Material not found.'];
    }

    if (!$supplierId) {
        $errors['supplier_id'] = ['Select a valid supplier.'];
    } elseif ($this->supplierRepository->findById($supplierId) === null) {
        $errors['supplier_id'] = ['Supplier not found.'];
    }

    

    // Validate quantity.
    if (
        !preg_match('/^\d+(?:\.\d{1,2})?$/D', $quantity)
        || (float) $quantity <= 0
        || (float) $quantity > 99999999.99
    ) {
        $errors['quantity'] = [
            'Enter a positive quantity with up to 2 decimal places.'
        ];
    }

    // Validate description.
    if (mb_strlen($description) > 255) {
        $errors['description'] = [
            'Description cannot exceed 255 characters.'
        ];
    }
    if ($materialId && $supplierId) {
        $material = $this->materialRepository->findById($materialId);

        if (
            $material !== null &&
            (int) $material['supplier_id'] !== $supplierId
        ) {
            $errors['supplier_id'] = [
                'This supplier is not assigned to the selected material.'
            ];
        }
    }

    if ($errors !== []) {
        return [
            'success' => false,
            'errors' => $errors,
        ];
    }

    try {
        $this->pdo->beginTransaction();

        // Lock the stock row.
        $stock = $this->stockRepository
            ->findByMaterialForUpdate($materialId);

        if ($stock === null) {
            $this->pdo->rollBack();

            return [
                'success' => false,
                'errors' => [
                    'quantity' => ['Stock record not found.']
                ],
            ];
        }

        $newQuantity = round(
            (float) $stock['quantity'] + (float) $quantity,
            2
        );

        if ($newQuantity > 99999999.99) {
            $this->pdo->rollBack();

            return [
                'success' => false,
                'errors' => [
                    'quantity' => ['Stock quantity exceeds the maximum.']
                ],
            ];
        }

        // Increase stock.
        $this->stockRepository->updateQuantity(
            $materialId,
            $newQuantity
        );

        // Record IN movement.
        $movementId = $this->stockMovementRepository->create([
            'material_id' => $materialId,
            'supplier_id' => $supplierId,
            'type' => 'IN',
            'quantity' => (float) $quantity,
            'date' => date('Y-m-d H:i:s'),
            'description' => $description !== '' ? $description : null,
        ]);

        $this->pdo->commit();

        return [
            'success' => true,
            'movement_id' => $movementId,
            'remaining_stock' => $newQuantity,
        ];

    } catch (Throwable $exception) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        throw $exception;
    }
}

}