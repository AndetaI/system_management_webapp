<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../util/Validator.php';

require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';
require_once __DIR__ . '/../services/VillaService.php';

try {
    $pdo = Database::getConnection();

    $groupRepository = new VillaGroupRepository($pdo);
    $villaRepository = new VillaRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $workRepository = new WorkRepository($pdo);
    $villaService = new VillaService(
        $villaRepository,
        $typologyRepository,
        $workRepository
    );

    $suffix = (string) time();

    // Create test group.
    $groupId = $groupRepository->create([
        'name' => 'Test Group ' . $suffix,
        'description' => 'Temporary validation test group',
    ]);

    // Create test typology.
    $typologyId = $typologyRepository->create([
        'group_id' => $groupId,
        'name' => 'Test Typology ' . $suffix,
        'description' => 'Temporary test typology',
        'floors' => 2,
        'area' => 150.00,
    ]);

    echo "TEST TYPOLOGY created." . PHP_EOL;


    // TEST 1: Valid villa.

    $villaName = 'TEST-VILLA-' . $suffix;

    $result = $villaService->create([
        'typology_id' => $typologyId,
        'name' => $villaName,
        'status' => 'planned',
        'description' => 'Temporary test villa',
        'planned_date' => '2026-10-15',
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== true ||
        !isset($result['id'])
    ) {
        throw new RuntimeException(
            'Valid villa was rejected.'
        );
    }

    $villaId = (int) $result['id'];

    echo "VALID VILLA test passed." . PHP_EOL;


    // TEST 2: Missing name.

    $result = $villaService->create([
        'typology_id' => $typologyId,
        'name' => '',
        'status' => 'planned',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['name'])
    ) {
        throw new RuntimeException(
            'Missing villa name validation failed.'
        );
    }

    echo "MISSING NAME test passed." . PHP_EOL;


    // TEST 3: Invalid status.

    $result = $villaService->create([
        'typology_id' => $typologyId,
        'name' => 'STATUS-' . $suffix,
        'status' => 'finished',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['status'])
    ) {
        throw new RuntimeException(
            'Invalid villa status was accepted.'
        );
    }

    echo "INVALID STATUS test passed." . PHP_EOL;


    // TEST 4: Invalid date.

    $result = $villaService->create([
        'typology_id' => $typologyId,
        'name' => 'DATE-' . $suffix,
        'status' => 'planned',
        'planned_date' => '2026-02-30',
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['planned_date'])
    ) {
        throw new RuntimeException(
            'Invalid villa date was accepted.'
        );
    }

    echo "INVALID DATE test passed." . PHP_EOL;


    // TEST 5: Typology does not exist.

    $result = $villaService->create([
        'typology_id' => 999999999,
        'name' => 'TYPOLOGY-' . $suffix,
        'status' => 'planned',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['typology_id'])
    ) {
        throw new RuntimeException(
            'Invalid typology was accepted.'
        );
    }

    echo "INVALID TYPOLOGY test passed." . PHP_EOL;


    // TEST 6: Duplicate villa name.

    $result = $villaService->create([
        'typology_id' => $typologyId,
        'name' => $villaName,
        'status' => 'planned',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['name'])
    ) {
        throw new RuntimeException(
            'Duplicate villa name was accepted.'
        );
    }

    echo "DUPLICATE NAME test passed." . PHP_EOL;
// TEST: VALID VILLA UPDATE

$result = $villaService->update(
    $villaId,
    [
        'typology_id' => $typologyId,
        'name' => 'Updated Villa ' . $suffix,
        'status' => 'in_progress',
        'description' => 'Updated villa description',
        'planned_date' => '2026-11-01',
        'actual_date' => null,
    ]
);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Valid villa update was rejected.'
    );
}

$updatedVilla = $villaRepository->findById($villaId);

if (
    $updatedVilla === null ||
    $updatedVilla['name'] !== 'Updated Villa ' . $suffix ||
    $updatedVilla['status'] !== 'in_progress'
) {
    throw new RuntimeException(
        'Villa was not correctly updated.'
    );
}

echo "VALID VILLA UPDATE test passed." . PHP_EOL;


// TEST: INVALID STATUS UPDATE

$result = $villaService->update(
    $villaId,
    [
        'typology_id' => $typologyId,
        'name' => 'Updated Villa ' . $suffix,
        'status' => 'invalid_status',
        'description' => null,
        'planned_date' => null,
        'actual_date' => null,
    ]
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['status'])
) {
    throw new RuntimeException(
        'Invalid villa status update was accepted.'
    );
}

echo "INVALID VILLA STATUS UPDATE test passed."
    . PHP_EOL;


// TEST: INVALID TYPOLOGY UPDATE

$result = $villaService->update(
    $villaId,
    [
        'typology_id' => 999999999,
        'name' => 'Updated Villa ' . $suffix,
        'status' => 'in_progress',
        'description' => null,
        'planned_date' => null,
        'actual_date' => null,
    ]
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['typology_id'])
) {
    throw new RuntimeException(
        'Invalid typology update was accepted.'
    );
}

echo "INVALID TYPOLOGY UPDATE test passed."
    . PHP_EOL;


// TEST: INVALID DATE UPDATE

$result = $villaService->update(
    $villaId,
    [
        'typology_id' => $typologyId,
        'name' => 'Updated Villa ' . $suffix,
        'status' => 'in_progress',
        'description' => null,
        'planned_date' => '2026-02-30',
        'actual_date' => null,
    ]
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['planned_date'])
) {
    throw new RuntimeException(
        'Invalid villa date update was accepted.'
    );
}

echo "INVALID VILLA DATE UPDATE test passed."
    . PHP_EOL;

// --------------------------------------------------
// VILLA DELETE TEST
// --------------------------------------------------

$deleteVillaId = $villaRepository->create([
    'typology_id' => $typologyId,
    'name' => 'DELETE-TEST-VILLA',
    'status' => 'planned',
    'description' => 'Temporary villa for delete test',
    'planned_date' => null,
    'actual_date' => null,
]);

$result = $villaService->delete($deleteVillaId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Valid villa deletion was rejected.'
    );
}

if ($villaRepository->findById($deleteVillaId) !== null) {
    throw new RuntimeException(
        'Villa still exists after deletion.'
    );
}

echo "VILLA DELETE test passed." . PHP_EOL;
// --------------------------------------------------
// VILLA WITH WORK DELETE REJECTION TEST
// --------------------------------------------------

$protectedVillaId = $villaRepository->create([
    'typology_id' => $typologyId,
    'name' => 'PROTECTED-TEST-VILLA',
    'status' => 'in_progress',
    'description' => 'Villa with a work',
    'planned_date' => null,
    'actual_date' => null,
]);

$testWorkId = $workRepository->create([
    'villa_id' => $protectedVillaId,
    'title' => 'Delete Protection Test Work',
    'category' => 'Test',
    'status' => 'planned',
    'description' => 'Temporary work',
    'planned_date' => null,
    'actual_date' => null,
]);

$result = $villaService->delete($protectedVillaId);

if (
    $result['success'] !== false ||
    !isset($result['errors']['villa_id'])
) {
    throw new RuntimeException(
        'Villa with works was incorrectly deleted.'
    );
}

echo "VILLA WITH WORK DELETE rejection test passed."
    . PHP_EOL;

/*
 * Manual cleanup because VillaService correctly
 * refused to delete this villa.
 */
$workRepository->delete($testWorkId);
$villaRepository->delete($protectedVillaId);

    // Clean up in FK-safe order.

    $villaRepository->delete($villaId);
    $typologyRepository->delete($typologyId);
    $groupRepository->delete($groupId);

    echo "TEST DATA cleanup successful." . PHP_EOL;
    echo "All VillaService validation tests passed." . PHP_EOL;

} catch (Throwable $exception) {
    echo "VillaService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
