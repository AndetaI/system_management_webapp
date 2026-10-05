<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../util/Validator.php';

require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../repositories/MaterialRepository.php';
require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../repositories/StockMovementRepository.php';
require_once __DIR__ . '/../repositories/MaterialUsageRepository.php';

require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';

require_once __DIR__ . '/../services/StockService.php';

try {
    $pdo = Database::getConnection();

    $supplierRepository = new SupplierRepository($pdo);
    $materialRepository = new MaterialRepository($pdo);
    $stockRepository = new StockRepository($pdo);
    $movementRepository = new StockMovementRepository($pdo);
    $usageRepository = new MaterialUsageRepository($pdo);

    $groupRepository = new VillaGroupRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $villaRepository = new VillaRepository($pdo);
    $workRepository = new WorkRepository($pdo);

    $stockService = new StockService(
        $pdo,
        $stockRepository,
        $movementRepository,
        $usageRepository,
        $materialRepository,
        $workRepository
    );

    $suffix = (string) time();


    // --------------------------------------------------
    // CREATE TEST DATA
    // --------------------------------------------------

    $supplierId = $supplierRepository->create([
        'name' => 'Stock Test Supplier ' . $suffix,
        'email' => null,
        'phone' => null,
        'address' => null,
        'contact_person' => null,
    ]);

    $materialId = $materialRepository->create([
        'supplier_id' => $supplierId,
        'name' => 'Stock Test Cement ' . $suffix,
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 10.00,
        'description' => 'Temporary material',
    ]);

    $stockRepository->create(
        $materialId,
        100.00
    );

    $groupId = $groupRepository->create([
        'name' => 'Stock Test Group ' . $suffix,
        'description' => null,
    ]);

    $typologyId = $typologyRepository->create([
        'group_id' => $groupId,
        'name' => 'Stock Test Typology ' . $suffix,
        'description' => null,
        'floors' => 2,
        'area' => 100.00,
    ]);

    $villaId = $villaRepository->create([
        'typology_id' => $typologyId,
        'name' => 'STOCK-TEST-VILLA-' . $suffix,
        'status' => 'planned',
        'description' => null,
        'planned_date' => null,
        'actual_date' => null,
    ]);

    $workId = $workRepository->create([
        'villa_id' => $villaId,
        'title' => 'Stock Test Work',
        'category' => 'Construction',
        'status' => 'in_progress',
        'description' => null,
        'planned_date' => null,
        'actual_date' => null,
    ]);

    echo "TEST DATA created." . PHP_EOL;


    // --------------------------------------------------
    // TEST 1: VALID MATERIAL USAGE
    // 100 - 25 = 75
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => 25,
        'date' => '2026-10-02',
    ]);

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid material usage was rejected.'
        );
    }

    if ((float) $result['remaining_stock'] !== 75.0) {
        throw new RuntimeException(
            'Incorrect remaining stock returned.'
        );
    }

    echo "VALID MATERIAL USAGE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 2: VERIFY DATABASE STOCK = 75
    // --------------------------------------------------

    $stock = $stockRepository->findByMaterial(
        $materialId
    );

    if (
        $stock === null ||
        (float) $stock['quantity'] !== 75.0
    ) {
        throw new RuntimeException(
            'Stock was not correctly updated to 75.'
        );
    }

    echo "STOCK UPDATE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 3: EXCESSIVE USAGE
    // Available = 75, request = 200
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => 200,
        'date' => '2026-10-02',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['quantity'])
    ) {
        throw new RuntimeException(
            'Excessive material usage was accepted.'
        );
    }

    echo "INSUFFICIENT STOCK rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 4:
    // FAILED REQUEST MUST NOT CHANGE STOCK
    // --------------------------------------------------

    $stock = $stockRepository->findByMaterial(
        $materialId
    );

    if (
        $stock === null ||
        (float) $stock['quantity'] !== 75.0
    ) {
        throw new RuntimeException(
            'Rejected usage changed the stock quantity.'
        );
    }

    echo "FAILED USAGE ROLLBACK test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 5: NEGATIVE QUANTITY
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => -10,
        'date' => '2026-10-02',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['quantity'])
    ) {
        throw new RuntimeException(
            'Negative quantity was accepted.'
        );
    }

    echo "NEGATIVE QUANTITY rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 6: ZERO QUANTITY
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => 0,
        'date' => '2026-10-02',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['quantity'])
    ) {
        throw new RuntimeException(
            'Zero quantity was accepted.'
        );
    }

    echo "ZERO QUANTITY rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 7: INVALID DATE
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => 5,
        'date' => '2026-02-30',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['date'])
    ) {
        throw new RuntimeException(
            'Invalid usage date was accepted.'
        );
    }

    echo "INVALID DATE rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 8: NON-EXISTING WORK
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => 999999999,
        'material_id' => $materialId,
        'quantity' => 5,
        'date' => '2026-10-02',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['work_id'])
    ) {
        throw new RuntimeException(
            'Non-existing work was accepted.'
        );
    }

    echo "INVALID WORK rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 9: NON-EXISTING MATERIAL
    // --------------------------------------------------

    $result = $stockService->useMaterial([
        'work_id' => $workId,
        'material_id' => 999999999,
        'quantity' => 5,
        'date' => '2026-10-02',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['material_id'])
    ) {
        throw new RuntimeException(
            'Non-existing material was accepted.'
        );
    }

    echo "INVALID MATERIAL rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // CLEAN UP
    // --------------------------------------------------

    /*
     * Delete children before parents because of
     * foreign-key constraints.
     */

    $pdo->prepare(
        'DELETE FROM material_usage
         WHERE work_id = :work_id
         AND material_id = :material_id'
    )->execute([
        'work_id' => $workId,
        'material_id' => $materialId,
    ]);

    $pdo->prepare(
        'DELETE FROM stock_movements
         WHERE material_id = :material_id'
    )->execute([
        'material_id' => $materialId,
    ]);

    $stockRepository->deleteByMaterial($materialId);

    $workRepository->delete($workId);
    $villaRepository->delete($villaId);
    $typologyRepository->delete($typologyId);
    $groupRepository->delete($groupId);

    $materialRepository->delete($materialId);
    $supplierRepository->delete($supplierId);

    echo "TEST DATA cleanup successful." . PHP_EOL;

    echo "All StockService validation tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "StockService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
