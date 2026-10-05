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
        private WorkRepository $workRepository
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

    /*
     * Business rule:
     * Work must exist.
     */
    if (
        $this->workRepository->findById($workId)
        === null
    ) {
        $validator->addError(
            'work_id',
            'The selected work does not exist.'
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
            'date' => $data['date'] . ' 00:00:00',
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
}