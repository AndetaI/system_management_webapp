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
require_once __DIR__ . '/../services/MaterialService.php';

try {
    $pdo = Database::getConnection();

    $materialRepository = new MaterialRepository($pdo);
    $supplierRepository = new SupplierRepository($pdo);
    $stockRepository = new StockRepository($pdo);
    $stockMovementRepository = new StockMovementRepository($pdo);
    $materialUsageRepository = new MaterialUsageRepository($pdo);
    $materialService = new MaterialService(
        $materialRepository,
        $supplierRepository,
        $stockRepository,
        $stockMovementRepository,
        $materialUsageRepository
    );

    $suffix = (string) time();


    // --------------------------------------------------
    // 1. CREATE TEMPORARY SUPPLIER
    // --------------------------------------------------

    $supplierId = $supplierRepository->create([
        'name' => 'Validation Test Supplier ' . $suffix,
        'email' => 'validation_' . $suffix . '@example.test',
        'phone' => '+355690000000',
        'address' => 'Test Address',
        'contact_person' => 'Test Contact',
    ]);

    echo "TEST SUPPLIER created. ID: {$supplierId}" . PHP_EOL;


    // --------------------------------------------------
    // 2. VALID MATERIAL
    // --------------------------------------------------

    $result = $materialService->create([
        'supplier_id' => $supplierId,
        'name' => 'Test Cement ' . $suffix,
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 10.50,
        'description' => 'Valid test material',
    ]);

    if (
        $result['success'] !== true ||
        !isset($result['id'])
    ) {
        throw new RuntimeException(
            'Valid material was rejected.'
        );
    }

    $materialId = (int) $result['id'];

    echo "VALID MATERIAL test passed. ID: {$materialId}"
        . PHP_EOL;


    // --------------------------------------------------
    // 3. MISSING NAME
    // --------------------------------------------------

    $result = $materialService->create([
        'supplier_id' => $supplierId,
        'name' => '',
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 10.50,
        'description' => null,
    ]);

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Material with missing name was accepted.'
        );
    }

    if (!isset($result['errors']['name'])) {
        throw new RuntimeException(
            'Missing name error was not returned.'
        );
    }

    echo "MISSING NAME validation test passed." . PHP_EOL;


    // --------------------------------------------------
    // 4. NEGATIVE PRICE
    // --------------------------------------------------

    $result = $materialService->create([
        'supplier_id' => $supplierId,
        'name' => 'Invalid Price Material',
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => -5,
        'description' => null,
    ]);

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Material with negative price was accepted.'
        );
    }

    if (!isset($result['errors']['price'])) {
        throw new RuntimeException(
            'Negative price error was not returned.'
        );
    }

    echo "NEGATIVE PRICE validation test passed." . PHP_EOL;


    // --------------------------------------------------
    // 5. NON-EXISTING SUPPLIER
    // --------------------------------------------------

    $result = $materialService->create([
        'supplier_id' => 999999999,
        'name' => 'Invalid Supplier Material',
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 10,
        'description' => null,
    ]);

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Material with invalid supplier was accepted.'
        );
    }

    if (!isset($result['errors']['supplier_id'])) {
        throw new RuntimeException(
            'Invalid supplier error was not returned.'
        );
    }

    echo "INVALID SUPPLIER business-rule test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // 6. VERIFY VALID MATERIAL EXISTS
    // --------------------------------------------------

    $material = $materialRepository->findById($materialId);

    if ($material === null) {
        throw new RuntimeException(
            'Valid material was not stored.'
        );
    }

    echo "VALID MATERIAL database test passed." . PHP_EOL;

// --------------------------------------------------
// MATERIAL UPDATE TEST
// --------------------------------------------------

$result = $materialService->update(
    $materialId,
    [
        'supplier_id' => $supplierId,
        'name' => 'Updated Test Material',
        'category' => 'Updated Category',
        'unit' => 'kg',
        'price' => 15.50,
        'description' => 'Updated description',
    ]
);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Valid material update was rejected.'
    );
}

$updatedMaterial = $materialRepository->findById(
    $materialId
);

if ($updatedMaterial === null) {
    throw new RuntimeException(
        'Updated material could not be found.'
    );
}

if ($updatedMaterial['name'] !== 'Updated Test Material') {
    throw new RuntimeException(
        'Material name was not updated.'
    );
}

if ($updatedMaterial['category'] !== 'Updated Category') {
    throw new RuntimeException(
        'Material category was not updated.'
    );
}

if ((float) $updatedMaterial['price'] !== 15.50) {
    throw new RuntimeException(
        'Material price was not updated.'
    );
}


echo "MATERIAL UPDATE test passed." . PHP_EOL;

$result = $materialService->update(
    $materialId,
    [
        'supplier_id' => $supplierId,
        'name' => 'Test Material',
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => -10,
        'description' => null,
    ]
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['price'])
) {
    throw new RuntimeException(
        'Negative price update was accepted.'
    );
}

echo "INVALID UPDATE PRICE rejection test passed."
    . PHP_EOL;

    $result = $materialService->update(
    $materialId,
    [
        'supplier_id' => 999999999,
        'name' => 'Test Material',
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 10,
        'description' => null,
    ]
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['supplier_id'])
) {
    throw new RuntimeException(
        'Invalid supplier update was accepted.'
    );
}

echo "INVALID UPDATE SUPPLIER rejection test passed."
    . PHP_EOL;

    // --------------------------------------------------
// DELETE MATERIAL TEST
// --------------------------------------------------

$deleteMaterialId = $materialRepository->create([
    'supplier_id' => $supplierId,
    'name' => 'Delete Test Material',
    'category' => 'Test',
    'unit' => 'kg',
    'price' => 10.00,
    'description' => 'Temporary material for delete test',
]);

$result = $materialService->delete($deleteMaterialId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Valid material deletion was rejected.'
    );
}

if (
    $materialRepository->findById($deleteMaterialId)
    !== null
) {
    throw new RuntimeException(
        'Material still exists after deletion.'
    );
}

echo "MATERIAL DELETE test passed." . PHP_EOL;

// --------------------------------------------------
// NON-EXISTING MATERIAL DELETE TEST
// --------------------------------------------------

$result = $materialService->delete(999999999);

if (
    $result['success'] !== false ||
    !isset($result['errors']['material_id'])
) {
    throw new RuntimeException(
        'Non-existing material deletion was accepted.'
    );
}

echo "NON-EXISTING MATERIAL DELETE test passed."
    . PHP_EOL;

// --------------------------------------------------
// MATERIAL WITH STOCK DELETE REJECTION TEST
// --------------------------------------------------

$stockTestMaterialId = $materialRepository->create([
    'supplier_id' => $supplierId,
    'name' => 'Material With Stock',
    'category' => 'Test',
    'unit' => 'kg',
    'price' => 15.00,
    'description' => 'Material used to test protected deletion',
]);

$stockRepository->create(
    $stockTestMaterialId,
    50.00
);

$result = $materialService->delete(
    $stockTestMaterialId
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['material_id'])
) {
    throw new RuntimeException(
        'Material with stock was incorrectly deleted.'
    );
}

echo "MATERIAL WITH STOCK DELETE rejection test passed."
    . PHP_EOL;

/*
 * Cleanup test data manually because the service
 * correctly refused to delete this material.
 */
$stockRepository->deleteByMaterial(
    $stockTestMaterialId
);

$materialRepository->delete(
    $stockTestMaterialId
);
    // --------------------------------------------------
    // 7. CLEAN UP
    // --------------------------------------------------

    $materialRepository->delete($materialId);
    $supplierRepository->delete($supplierId);

    echo "TEST DATA cleanup successful." . PHP_EOL;

    echo "All MaterialService validation tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "MaterialService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
