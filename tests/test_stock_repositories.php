<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../repositories/MaterialRepository.php';
require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';
require_once __DIR__ . '/../repositories/MaterialUsageRepository.php';
require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../repositories/StockMovementRepository.php';

try {
    $pdo = Database::getConnection();

    $supplierRepository = new SupplierRepository($pdo);
    $materialRepository = new MaterialRepository($pdo);

    $groupRepository = new VillaGroupRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $villaRepository = new VillaRepository($pdo);
    $workRepository = new WorkRepository($pdo);

    $usageRepository = new MaterialUsageRepository($pdo);
    $stockRepository = new StockRepository($pdo);
    $movementRepository = new StockMovementRepository($pdo);

    $suffix = (string) time();


    // --------------------------------------------------
    // 1. CREATE SUPPLIER
    // --------------------------------------------------

    $supplierId = $supplierRepository->create([
        'name' => 'Stock Test Supplier ' . $suffix,
        'email' => 'stock_' . $suffix . '@example.test',
        'phone' => '+355690000000',
        'address' => 'Test Address',
        'contact_person' => 'Test Contact',
    ]);

    echo "SUPPLIER CREATE successful. ID: {$supplierId}" . PHP_EOL;


    // --------------------------------------------------
    // 2. CREATE MATERIAL
    // --------------------------------------------------

    $materialId = $materialRepository->create([
        'supplier_id' => $supplierId,
        'name' => 'Test Cement ' . $suffix,
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 0.30,
        'description' => 'Material for stock repository test',
    ]);

    echo "MATERIAL CREATE successful. ID: {$materialId}" . PHP_EOL;


    // --------------------------------------------------
    // 3. CREATE VILLA GROUP
    // --------------------------------------------------

    $groupId = $groupRepository->create([
        'name' => 'Stock Test Group ' . $suffix,
        'description' => 'Temporary group',
    ]);

    echo "GROUP CREATE successful. ID: {$groupId}" . PHP_EOL;


    // --------------------------------------------------
    // 4. CREATE TYPOLOGY
    // --------------------------------------------------

    $typologyId = $typologyRepository->create([
        'group_id' => $groupId,
        'name' => 'Stock Test Typology ' . $suffix,
        'description' => 'Temporary typology',
        'floors' => 2,
        'area' => 150.00,
    ]);

    echo "TYPOLOGY CREATE successful. ID: {$typologyId}" . PHP_EOL;


    // --------------------------------------------------
    // 5. CREATE VILLA
    // --------------------------------------------------

    $villaId = $villaRepository->create([
        'typology_id' => $typologyId,
        'name' => 'STOCK-TEST-' . $suffix,
        'status' => 'planned',
        'description' => 'Temporary villa',
        'planned_date' => '2026-10-02',
        'actual_date' => null,
    ]);

    echo "VILLA CREATE successful. ID: {$villaId}" . PHP_EOL;


    // --------------------------------------------------
    // 6. CREATE WORK
    // --------------------------------------------------

    $workId = $workRepository->create([
        'villa_id' => $villaId,
        'title' => 'Test Construction Work',
        'category' => 'Construction',
        'status' => 'in_progress',
        'description' => 'Temporary work',
        'planned_date' => '2026-10-02',
        'actual_date' => null,
    ]);

    echo "WORK CREATE successful. ID: {$workId}" . PHP_EOL;


    // --------------------------------------------------
    // 7. CREATE STOCK
    // Start with 100 kg
    // --------------------------------------------------

    $stockId = $stockRepository->create(
        $materialId,
        100.00
    );

    echo "STOCK CREATE successful. ID: {$stockId}" . PHP_EOL;


    // --------------------------------------------------
    // 8. READ STOCK
    // --------------------------------------------------

    $stock = $stockRepository->findByMaterial($materialId);

    if (
        $stock === null ||
        (float) $stock['quantity'] !== 100.00
    ) {
        throw new RuntimeException(
            'Stock was not created correctly.'
        );
    }

    echo "STOCK READ successful. Quantity: "
        . $stock['quantity']
        . PHP_EOL;


    // --------------------------------------------------
    // 9. CREATE IN STOCK MOVEMENT
    // --------------------------------------------------

    $inMovementId = $movementRepository->create([
        'material_id' => $materialId,
        'type' => 'IN',
        'quantity' => 100.00,
        'date' => '2026-10-02 10:00:00',
        'description' => 'Initial stock',
    ]);

    echo "STOCK MOVEMENT IN successful. ID: {$inMovementId}"
        . PHP_EOL;


    // --------------------------------------------------
    // 10. CREATE MATERIAL USAGE
    // Use 20 kg for the work
    // --------------------------------------------------

    $usageId = $usageRepository->create([
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => 20.00,
        'date' => '2026-10-02',
    ]);

    echo "MATERIAL USAGE CREATE successful. ID: {$usageId}"
        . PHP_EOL;


    // --------------------------------------------------
    // 11. READ MATERIAL USAGE
    // --------------------------------------------------

    $usage = $usageRepository->findById($usageId);

    if (
        $usage === null ||
        (int) $usage['work_id'] !== $workId ||
        (int) $usage['material_id'] !== $materialId ||
        (float) $usage['quantity'] !== 20.00
    ) {
        throw new RuntimeException(
            'Material usage was not created correctly.'
        );
    }

    echo "MATERIAL USAGE READ successful." . PHP_EOL;


    // --------------------------------------------------
    // 12. UPDATE MATERIAL USAGE
    // Change 20 kg -> 25 kg
    // --------------------------------------------------

    $usageRepository->update($usageId, [
        'work_id' => $workId,
        'material_id' => $materialId,
        'quantity' => 25.00,
        'date' => '2026-10-02',
    ]);

    $updatedUsage = $usageRepository->findById($usageId);

    if (
        $updatedUsage === null ||
        (float) $updatedUsage['quantity'] !== 25.00
    ) {
        throw new RuntimeException(
            'Material usage was not updated correctly.'
        );
    }

    echo "MATERIAL USAGE UPDATE successful." . PHP_EOL;


    // --------------------------------------------------
    // 13. UPDATE CURRENT STOCK
    // 100 - 25 = 75 kg
    // --------------------------------------------------

    $stockRepository->updateQuantity(
        $materialId,
        75.00
    );

    $updatedStock =
        $stockRepository->findByMaterial($materialId);

    if (
        $updatedStock === null ||
        (float) $updatedStock['quantity'] !== 75.00
    ) {
        throw new RuntimeException(
            'Stock quantity was not updated correctly.'
        );
    }

    echo "STOCK UPDATE successful. Quantity: "
        . $updatedStock['quantity']
        . PHP_EOL;


    // --------------------------------------------------
    // 14. CREATE OUT MOVEMENT
    // --------------------------------------------------

    $outMovementId = $movementRepository->create([
        'material_id' => $materialId,
        'type' => 'OUT',
        'quantity' => 25.00,
        'date' => '2026-10-02 11:00:00',
        'description' => 'Material used for work',
    ]);

    echo "STOCK MOVEMENT OUT successful. ID: {$outMovementId}"
        . PHP_EOL;


    // --------------------------------------------------
    // 15. READ MOVEMENT BY ID
    // --------------------------------------------------

    $movement =
        $movementRepository->findById($outMovementId);

    if (
        $movement === null ||
        $movement['type'] !== 'OUT' ||
        (float) $movement['quantity'] !== 25.00
    ) {
        throw new RuntimeException(
            'Stock movement could not be read correctly.'
        );
    }

    echo "STOCK MOVEMENT READ successful." . PHP_EOL;


    // --------------------------------------------------
    // 16. FIND MOVEMENTS FOR MATERIAL
    // We expect IN + OUT = 2
    // --------------------------------------------------

    $movements =
        $movementRepository->findByMaterial($materialId);

    if (count($movements) !== 2) {
        throw new RuntimeException(
            'Expected two stock movements.'
        );
    }

    echo "STOCK MOVEMENT HISTORY successful. Movements: "
        . count($movements)
        . PHP_EOL;


    // --------------------------------------------------
    // 17. TEST findAll()
    // --------------------------------------------------

    $allUsage = $usageRepository->findAll();
    $allStock = $stockRepository->findAll();
    $allMovements = $movementRepository->findAll();

    echo "MATERIAL USAGE READ ALL successful. Rows: "
        . count($allUsage)
        . PHP_EOL;

    echo "STOCK READ ALL successful. Rows: "
        . count($allStock)
        . PHP_EOL;

    echo "STOCK MOVEMENT READ ALL successful. Rows: "
        . count($allMovements)
        . PHP_EOL;


    // --------------------------------------------------
    // 18. TEST STOCK MOVEMENT UPDATE
    // --------------------------------------------------

    $movementRepository->update($outMovementId, [
        'material_id' => $materialId,
        'type' => 'OUT',
        'quantity' => 25.00,
        'date' => '2026-10-02 11:00:00',
        'description' => 'Updated movement description',
    ]);

    $updatedMovement =
        $movementRepository->findById($outMovementId);

    if (
        $updatedMovement === null ||
        $updatedMovement['description']
            !== 'Updated movement description'
    ) {
        throw new RuntimeException(
            'Stock movement was not updated correctly.'
        );
    }

    echo "STOCK MOVEMENT UPDATE successful." . PHP_EOL;


    // --------------------------------------------------
    // 19. DELETE MATERIAL USAGE
    // --------------------------------------------------

    $usageRepository->delete($usageId);

    if ($usageRepository->findById($usageId) !== null) {
        throw new RuntimeException(
            'Material usage was not deleted.'
        );
    }

    echo "MATERIAL USAGE DELETE successful." . PHP_EOL;


    // --------------------------------------------------
    // 20. DELETE STOCK MOVEMENTS
    // --------------------------------------------------

    $movementRepository->delete($outMovementId);
    $movementRepository->delete($inMovementId);

    if (
        $movementRepository->findById($outMovementId) !== null ||
        $movementRepository->findById($inMovementId) !== null
    ) {
        throw new RuntimeException(
            'Stock movements were not deleted.'
        );
    }

    echo "STOCK MOVEMENT DELETE successful." . PHP_EOL;


    // --------------------------------------------------
    // 21. DELETE STOCK
    // --------------------------------------------------

    $stockRepository->deleteByMaterial($materialId);

    if ($stockRepository->findByMaterial($materialId) !== null) {
        throw new RuntimeException(
            'Stock was not deleted.'
        );
    }

    echo "STOCK DELETE successful." . PHP_EOL;


    // --------------------------------------------------
    // 22. CLEAN UP DEPENDENCIES
    // --------------------------------------------------

    $workRepository->delete($workId);

    $villaRepository->delete($villaId);
    $typologyRepository->delete($typologyId);
    $groupRepository->delete($groupId);

    $materialRepository->delete($materialId);
    $supplierRepository->delete($supplierId);

    echo "TEST DATA cleanup successful." . PHP_EOL;

    echo "All stock/material usage repository tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "Stock repository test failed." . PHP_EOL;

    echo "Error: "
        . $exception->getMessage()
        . PHP_EOL;
}
