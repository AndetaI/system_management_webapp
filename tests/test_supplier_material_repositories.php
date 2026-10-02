<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../repositories/MaterialRepository.php';

try {
    $pdo = Database::getConnection();

    $supplierRepository = new SupplierRepository($pdo);
    $materialRepository = new MaterialRepository($pdo);

    $suffix = (string) time();


    // 1. CREATE SUPPLIER
    $supplierId = $supplierRepository->create([
        'name' => 'Test Supplier ' . $suffix,
        'email' => 'supplier_' . $suffix . '@example.test',
        'phone' => '+355690000000',
        'address' => 'Test Address',
        'contact_person' => 'Test Contact',
    ]);

    echo "SUPPLIER CREATE successful. ID: {$supplierId}" . PHP_EOL;


    // 2. READ SUPPLIER
    $supplier = $supplierRepository->findById($supplierId);

    if ($supplier === null) {
        throw new RuntimeException(
            'Supplier could not be found.'
        );
    }

    echo "SUPPLIER READ successful." . PHP_EOL;


    // 3. UPDATE SUPPLIER
    $supplierRepository->update($supplierId, [
        'name' => 'Updated Test Supplier ' . $suffix,
        'email' => 'supplier_' . $suffix . '@example.test',
        'phone' => '+355690000001',
        'address' => 'Updated Test Address',
        'contact_person' => 'Updated Test Contact',
    ]);

    $updatedSupplier =
        $supplierRepository->findById($supplierId);

    if (
        $updatedSupplier === null ||
        $updatedSupplier['name']
            !== 'Updated Test Supplier ' . $suffix
    ) {
        throw new RuntimeException(
            'Supplier was not updated correctly.'
        );
    }

    echo "SUPPLIER UPDATE successful." . PHP_EOL;


    // 4. CREATE MATERIAL BELONGING TO SUPPLIER
    $materialId = $materialRepository->create([
        'supplier_id' => $supplierId,
        'name' => 'Test Cement ' . $suffix,
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 0.25,
        'description' => 'Temporary material for repository testing',
    ]);

    echo "MATERIAL CREATE successful. ID: {$materialId}" . PHP_EOL;


    // 5. READ MATERIAL
    $material = $materialRepository->findById($materialId);

    if ($material === null) {
        throw new RuntimeException(
            'Material could not be found.'
        );
    }

    if ((int) $material['supplier_id'] !== $supplierId) {
        throw new RuntimeException(
            'Material is linked to the wrong supplier.'
        );
    }

    echo "MATERIAL READ successful." . PHP_EOL;
    echo "SUPPLIER -> MATERIAL relationship successful." . PHP_EOL;


    // 6. UPDATE MATERIAL
    $materialRepository->update($materialId, [
        'supplier_id' => $supplierId,
        'name' => 'Updated Test Cement ' . $suffix,
        'category' => 'Construction',
        'unit' => 'kg',
        'price' => 0.30,
        'description' => 'Updated material description',
    ]);

    $updatedMaterial =
        $materialRepository->findById($materialId);

    if (
        $updatedMaterial === null ||
        $updatedMaterial['name']
            !== 'Updated Test Cement ' . $suffix ||
        (float) $updatedMaterial['price'] !== 0.30
    ) {
        throw new RuntimeException(
            'Material was not updated correctly.'
        );
    }

    echo "MATERIAL UPDATE successful." . PHP_EOL;


    // 7. TEST findAll()
    $suppliers = $supplierRepository->findAll();
    $materials = $materialRepository->findAll();

    echo "SUPPLIER READ ALL successful. Suppliers found: "
        . count($suppliers)
        . PHP_EOL;

    echo "MATERIAL READ ALL successful. Materials found: "
        . count($materials)
        . PHP_EOL;


    // 8. DELETE MATERIAL FIRST
    $materialRepository->delete($materialId);

    if ($materialRepository->findById($materialId) !== null) {
        throw new RuntimeException(
            'Material was not deleted.'
        );
    }

    echo "MATERIAL DELETE successful." . PHP_EOL;


    // 9. DELETE SUPPLIER
    $supplierRepository->delete($supplierId);

    if ($supplierRepository->findById($supplierId) !== null) {
        throw new RuntimeException(
            'Supplier was not deleted.'
        );
    }

    echo "SUPPLIER DELETE successful." . PHP_EOL;

    echo "All supplier/material repository tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "Supplier/material repository test failed."
        . PHP_EOL;
}
