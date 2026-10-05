<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../util/Validator.php';
require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../services/SupplierService.php';
require_once __DIR__ . '/../repositories/MaterialRepository.php';



try {
    $pdo = Database::getConnection();
    $materialRepository = new MaterialRepository($pdo);

    $supplierRepository = new SupplierRepository($pdo);
    $supplierService = new SupplierService(
        $supplierRepository,
        $materialRepository
    );

    $suffix = (string) time();


    // --------------------------------------------------
    // TEST 1: VALID SUPPLIER
    // --------------------------------------------------

    $result = $supplierService->create([
        'name' => 'Test Supplier ' . $suffix,
        'email' => 'supplier_' . $suffix . '@example.test',
        'phone' => '+355691234567',
        'address' => 'Tirana',
        'contact_person' => 'Test Contact',
    ]);

    if (
        $result['success'] !== true ||
        !isset($result['id'])
    ) {
        throw new RuntimeException(
            'Valid supplier was rejected.'
        );
    }

    $supplierId = (int) $result['id'];

    echo "VALID SUPPLIER test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 2: MISSING NAME
    // --------------------------------------------------

    $result = $supplierService->create([
        'name' => '',
        'email' => 'test@example.test',
        'phone' => null,
        'address' => null,
        'contact_person' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['name'])
    ) {
        throw new RuntimeException(
            'Missing supplier name was accepted.'
        );
    }

    echo "MISSING SUPPLIER NAME test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 3: INVALID EMAIL
    // --------------------------------------------------

    $result = $supplierService->create([
        'name' => 'Invalid Email Supplier',
        'email' => 'not-an-email',
        'phone' => null,
        'address' => null,
        'contact_person' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['email'])
    ) {
        throw new RuntimeException(
            'Invalid supplier email was accepted.'
        );
    }

    echo "INVALID SUPPLIER EMAIL test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 4: VALID UPDATE
    // --------------------------------------------------

    $result = $supplierService->update(
        $supplierId,
        [
            'name' => 'Updated Supplier ' . $suffix,
            'email' => 'updated_' . $suffix . '@example.test',
            'phone' => '+355699999999',
            'address' => 'Durres',
            'contact_person' => 'Updated Contact',
        ]
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid supplier update was rejected.'
        );
    }

    $supplier = $supplierRepository->findById(
        $supplierId
    );

    if (
        $supplier === null ||
        $supplier['name'] !==
            'Updated Supplier ' . $suffix ||
        $supplier['email'] !==
            'updated_' . $suffix . '@example.test'
    ) {
        throw new RuntimeException(
            'Supplier was not correctly updated.'
        );
    }

    echo "VALID SUPPLIER UPDATE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 5: INVALID EMAIL DURING UPDATE
    // --------------------------------------------------

    $result = $supplierService->update(
        $supplierId,
        [
            'name' => 'Updated Supplier ' . $suffix,
            'email' => 'invalid-email',
            'phone' => null,
            'address' => null,
            'contact_person' => null,
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['email'])
    ) {
        throw new RuntimeException(
            'Invalid supplier update email was accepted.'
        );
    }

    echo "INVALID SUPPLIER UPDATE EMAIL test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 6: NON-EXISTING SUPPLIER UPDATE
    // --------------------------------------------------

    $result = $supplierService->update(
        999999999,
        [
            'name' => 'Missing Supplier',
            'email' => null,
            'phone' => null,
            'address' => null,
            'contact_person' => null,
        ]
    );

if (
    $result['success'] !== false ||
    !isset($result['errors']['supplier_id'])
) {
    throw new RuntimeException(
        'Non-existing supplier update was accepted.'
    );
}

    echo "NON-EXISTING SUPPLIER UPDATE test passed."
        . PHP_EOL;

// --------------------------------------------------
// TEST: DELETE NON-EXISTING SUPPLIER
// --------------------------------------------------

$result = $supplierService->delete(999999999);

if (
    $result['success'] !== false ||
    !isset($result['errors']['supplier_id'])
) {
    throw new RuntimeException(
        'Non-existing supplier deletion was accepted.'
    );
}

echo "NON-EXISTING SUPPLIER DELETE test passed."
    . PHP_EOL;


// --------------------------------------------------
// TEST: VALID SUPPLIER DELETE
// --------------------------------------------------

$result = $supplierService->delete($supplierId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Valid supplier deletion was rejected.'
    );
}

if (
    $supplierRepository->findById($supplierId)
    !== null
) {
    throw new RuntimeException(
        'Supplier still exists after deletion.'
    );
}

echo "VALID SUPPLIER DELETE test passed."
    . PHP_EOL;
    // --------------------------------------------------
    // CLEAN UP
    // --------------------------------------------------


    echo "TEST DATA cleanup successful." . PHP_EOL;
    echo "All SupplierService validation tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "SupplierService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
