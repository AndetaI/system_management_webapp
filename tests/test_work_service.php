<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../util/Validator.php';

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/TeamRepository.php';
require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';
require_once __DIR__ . '/../repositories/WorkAssignmentRepository.php';
require_once __DIR__ . '/../repositories/MaterialUsageRepository.php';
require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../repositories/MaterialRepository.php';
require_once __DIR__ . '/../services/WorkService.php';

try {
    $pdo = Database::getConnection();

    $userRepository = new UserRepository($pdo);
    $teamRepository = new TeamRepository($pdo);
    $groupRepository = new VillaGroupRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $villaRepository = new VillaRepository($pdo);
    $workRepository = new WorkRepository($pdo);
    $assignmentRepository = new WorkAssignmentRepository($pdo);
    $materialUsageRepository = new MaterialUsageRepository($pdo);
    $supplierRepository = new SupplierRepository($pdo);
    $materialRepository = new MaterialRepository($pdo);
    $workService = new WorkService(
        $pdo,
        $workRepository,
        $villaRepository,
        $assignmentRepository,
        $userRepository,
        $teamRepository,
        $materialUsageRepository
    );

    $suffix = (string) time();


    // --------------------------------------------------
    // CREATE TEST DATA
    // --------------------------------------------------

    $groupId = $groupRepository->create([
        'name' => 'Work Test Group ' . $suffix,
        'description' => 'Temporary group',
    ]);

    $typologyId = $typologyRepository->create([
        'group_id' => $groupId,
        'name' => 'Work Test Typology ' . $suffix,
        'description' => 'Temporary typology',
        'floors' => 2,
        'area' => 120.00,
    ]);

    $villaId = $villaRepository->create([
        'typology_id' => $typologyId,
        'name' => 'WORK-TEST-VILLA-' . $suffix,
        'status' => 'planned',
        'description' => 'Temporary villa',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    $userId = $userRepository->create([
        'username' => 'worktest_' . $suffix,
        'name' => 'Work Test User',
        'email' => 'worktest_' . $suffix . '@example.test',
        'password_hash' => password_hash(
            'SecureTest123!',
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'active',
    ]);

    $teamId = $teamRepository->create([
        'name' => 'Work Test Team ' . $suffix,
        'description' => 'Temporary team',
    ]);

    echo "TEST DATA created." . PHP_EOL;


    // --------------------------------------------------
    // TEST 1: VALID WORK
    // --------------------------------------------------

    $result = $workService->create([
        'villa_id' => $villaId,
        'title' => 'Electrical Installation',
        'category' => 'Electrical',
        'status' => 'planned',
        'description' => 'Temporary work',
        'planned_date' => '2026-10-20',
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== true ||
        !isset($result['id'])
    ) {
        throw new RuntimeException(
            'Valid work was rejected.'
        );
    }

    $workId = (int) $result['id'];

    echo "VALID WORK test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 2: MISSING TITLE
    // --------------------------------------------------

    $result = $workService->create([
        'villa_id' => $villaId,
        'title' => '',
        'category' => 'Electrical',
        'status' => 'planned',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['title'])
    ) {
        throw new RuntimeException(
            'Missing work title was accepted.'
        );
    }

    echo "MISSING TITLE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 3: INVALID STATUS
    // --------------------------------------------------

    $result = $workService->create([
        'villa_id' => $villaId,
        'title' => 'Invalid Status Work',
        'category' => 'Electrical',
        'status' => 'finished',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['status'])
    ) {
        throw new RuntimeException(
            'Invalid work status was accepted.'
        );
    }

    echo "INVALID STATUS test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 4: NON-EXISTING VILLA
    // --------------------------------------------------

    $result = $workService->create([
        'villa_id' => 999999999,
        'title' => 'Invalid Villa Work',
        'category' => 'Electrical',
        'status' => 'planned',
        'planned_date' => null,
        'actual_date' => null,
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['villa_id'])
    ) {
        throw new RuntimeException(
            'Non-existing villa was accepted.'
        );
    }

    echo "INVALID VILLA test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 5: ASSIGN TO USER
    // --------------------------------------------------

    $result = $workService->assign(
        $workId,
        $userId,
        null
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid user assignment was rejected.'
        );
    }

    echo "USER ASSIGNMENT test passed." . PHP_EOL;


    // Remove assignment so we can test other combinations.
    $assignmentRepository->deleteByWork($workId);


    // --------------------------------------------------
    // TEST 6: ASSIGN TO TEAM
    // --------------------------------------------------

    $result = $workService->assign(
        $workId,
        null,
        $teamId
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid team assignment was rejected.'
        );
    }

    echo "TEAM ASSIGNMENT test passed." . PHP_EOL;

    $assignmentRepository->deleteByWork($workId);


    // --------------------------------------------------
    // TEST 7: BOTH USER AND TEAM -> INVALID
    // --------------------------------------------------

    $result = $workService->assign(
        $workId,
        $userId,
        $teamId
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['assignment'])
    ) {
        throw new RuntimeException(
            'User + team assignment was accepted.'
        );
    }

    echo "USER + TEAM rejection test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 8: NEITHER USER NOR TEAM -> INVALID
    // --------------------------------------------------

    $result = $workService->assign(
        $workId,
        null,
        null
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['assignment'])
    ) {
        throw new RuntimeException(
            'Empty assignment was accepted.'
        );
    }

    echo "EMPTY ASSIGNMENT rejection test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 9: NON-EXISTING USER
    // --------------------------------------------------

    $result = $workService->assign(
        $workId,
        999999999,
        null
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['user_id'])
    ) {
        throw new RuntimeException(
            'Non-existing user was accepted.'
        );
    }

    echo "INVALID USER test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 10: NON-EXISTING TEAM
    // --------------------------------------------------

    $result = $workService->assign(
        $workId,
        null,
        999999999
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['team_id'])
    ) {
        throw new RuntimeException(
            'Non-existing team was accepted.'
        );
    }

    echo "INVALID TEAM test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 11: DUPLICATE ASSIGNMENT
    // --------------------------------------------------

    $firstAssignment = $workService->assign(
        $workId,
        $userId,
        null
    );

    if ($firstAssignment['success'] !== true) {
        throw new RuntimeException(
            'Initial assignment failed.'
        );
    }

    $secondAssignment = $workService->assign(
        $workId,
        null,
        $teamId
    );

    if (
        $secondAssignment['success'] !== false ||
        !isset($secondAssignment['errors']['assignment'])
    ) {
        throw new RuntimeException(
            'Second assignment was accepted.'
        );
    }

    echo "DUPLICATE ASSIGNMENT rejection test passed."
        . PHP_EOL;

// --------------------------------------------------
// TEST 12: CREATE WORK WITH REQUIRED ASSIGNMENT
// --------------------------------------------------

    $result = $workService->createWithAssignment( 
        [
            'villa_id' => $villaId,
            'title' => 'Plumbing Installation',
            'category' => 'Plumbing',
            'status' => 'planned',
            'description' => 'Transaction test',
            'planned_date' => '2026-10-25',
            'actual_date' => null,
            ],
            $userId,
            null
        );
        
        if (
            $result['success'] !== true ||
            !isset($result['id']) ||
            !isset($result['assignment_id'])
        ) {
            throw new RuntimeException(
                'Work with assignment was not created.'
            );
        }

        $transactionWorkId = (int) $result['id'];
        $assignment= $assignmentRepository->findByWork(
            $transactionWorkId
        );

        if ($assignment === null) {
            throw new RuntimeException(
                'Work was created without its assignment.'
            );
        }
        
        echo "TRANSACTIONAL WORK CREATION test passed."
        . PHP_EOL;
    // --------------------------------------------------
    // TEST 13: VALID WORK UPDATE
    // --------------------------------------------------

    $result = $workService->update(
        $workId,
        [
            'villa_id' => $villaId,
            'title' => 'Updated Electrical Installation',
            'category' => 'Electrical',
            'status' => 'in_progress',
            'description' => 'Updated temporary work',
            'planned_date' => '2026-10-22',
            'actual_date' => null,
        ]
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid work update was rejected.'
        );
    }

    $updatedWork = $workRepository->findById($workId);

    if (
        $updatedWork === null ||
        $updatedWork['title']
            !== 'Updated Electrical Installation' ||
        $updatedWork['status'] !== 'in_progress'
    ) {
        throw new RuntimeException(
            'Work was not correctly updated.'
        );
    }

    echo "VALID WORK UPDATE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 14: INVALID STATUS UPDATE
    // --------------------------------------------------

    $result = $workService->update(
        $workId,
        [
            'villa_id' => $villaId,
            'title' => 'Updated Electrical Installation',
            'category' => 'Electrical',
            'status' => 'finished',
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
            'Invalid work status update was accepted.'
        );
    }

    echo "INVALID WORK STATUS UPDATE test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 15: INVALID VILLA UPDATE
    // --------------------------------------------------

    $result = $workService->update(
        $workId,
        [
            'villa_id' => 999999999,
            'title' => 'Updated Electrical Installation',
            'category' => 'Electrical',
            'status' => 'in_progress',
            'description' => null,
            'planned_date' => null,
            'actual_date' => null,
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['villa_id'])
    ) {
        throw new RuntimeException(
            'Non-existing villa was accepted during update.'
        );
    }

    echo "INVALID VILLA UPDATE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 16: CHANGE ASSIGNMENT USER -> TEAM
    // --------------------------------------------------

    // At this point workId already has a user assignment
    // from TEST 11.

    $result = $workService->changeAssignment(
        $workId,
        null,
        $teamId
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Changing assignment to team failed.'
        );
    }

    $assignment = $assignmentRepository->findByWork(
        $workId
    );

    if (
        $assignment === null ||
        $assignment['user_id'] !== null ||
        (int) $assignment['team_id'] !== $teamId
    ) {
        throw new RuntimeException(
            'Work assignment was not changed to team.'
        );
    }

    echo "CHANGE ASSIGNMENT USER TO TEAM test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 17: CHANGE ASSIGNMENT TEAM -> USER
    // --------------------------------------------------

    $result = $workService->changeAssignment(
        $workId,
        $userId,
        null
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Changing assignment to user failed.'
        );
    }

    $assignment = $assignmentRepository->findByWork(
        $workId
    );

    if (
        $assignment === null ||
        (int) $assignment['user_id'] !== $userId ||
        $assignment['team_id'] !== null
    ) {
        throw new RuntimeException(
            'Work assignment was not changed to user.'
        );
    }

    echo "CHANGE ASSIGNMENT TEAM TO USER test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 18: BOTH USER AND TEAM DURING CHANGE
    // --------------------------------------------------

    $result = $workService->changeAssignment(
        $workId,
        $userId,
        $teamId
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['assignment'])
    ) {
        throw new RuntimeException(
            'Invalid assignment change was accepted.'
        );
    }

    echo "INVALID ASSIGNMENT CHANGE rejection test passed."
        . PHP_EOL;


    // --------------------------------------------------
    // TEST 19: NON-EXISTING USER DURING CHANGE
    // --------------------------------------------------

    $result = $workService->changeAssignment(
        $workId,
        999999999,
        null
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['user_id'])
    ) {
        throw new RuntimeException(
            'Non-existing user assignment change was accepted.'
        );
    }

    echo "INVALID USER ASSIGNMENT CHANGE test passed."
        . PHP_EOL;

    // --------------------------------------------------
// WORK DELETE TEST
// --------------------------------------------------

$deleteWorkId = $workRepository->create([
    'villa_id' => $villaId,
    'title' => 'Delete Test Work',
    'category' => 'Test',
    'status' => 'planned',
    'description' => 'Temporary work for delete test',
    'planned_date' => null,
    'actual_date' => null,
]);

$result = $workService->delete($deleteWorkId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Valid work deletion was rejected.'
    );
}

if ($workRepository->findById($deleteWorkId) !== null) {
    throw new RuntimeException(
        'Work still exists after deletion.'
    );
}

echo "WORK DELETE test passed." . PHP_EOL;


// --------------------------------------------------
// NON-EXISTING WORK DELETE TEST
// --------------------------------------------------

$result = $workService->delete(999999999);

if (
    $result['success'] !== false ||
    !isset($result['errors']['work_id'])
) {
    throw new RuntimeException(
        'Non-existing work deletion was accepted.'
    );
}

echo "NON-EXISTING WORK DELETE test passed."
    . PHP_EOL;

// --------------------------------------------------
// WORK WITH ASSIGNMENT DELETE CASCADE TEST
// --------------------------------------------------

$cascadeWorkId = $workRepository->create([
    'villa_id' => $villaId,
    'title' => 'Assignment Cascade Test',
    'category' => 'Test',
    'status' => 'planned',
    'description' => 'Temporary work for cascade test',
    'planned_date' => null,
    'actual_date' => null,
]);

$assignmentRepository->create(
    $cascadeWorkId,
    $userId,
    null
);

if (
    $assignmentRepository->findByWork($cascadeWorkId)
    === null
) {
    throw new RuntimeException(
        'Test assignment was not created.'
    );
}

$result = $workService->delete($cascadeWorkId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Work with assignment deletion was rejected.'
    );
}

if ($workRepository->findById($cascadeWorkId) !== null) {
    throw new RuntimeException(
        'Work still exists after deletion.'
    );
}

if (
    $assignmentRepository->findByWork($cascadeWorkId)
    !== null
) {
    throw new RuntimeException(
        'Work assignment was not deleted by cascade.'
    );
}

echo "WORK ASSIGNMENT CASCADE DELETE test passed."
    . PHP_EOL;
// --------------------------------------------------
// WORK WITH MATERIAL USAGE DELETE REJECTION TEST
// --------------------------------------------------

$usageTestSupplierId = $supplierRepository->create([
    'name' => 'Work Delete Test Supplier',
    'email' => null,
    'phone' => null,
    'address' => null,
    'contact_person' => null,
]);

$usageTestMaterialId = $materialRepository->create([
    'supplier_id' => $usageTestSupplierId,
    'name' => 'Work Delete Test Material',
    'category' => 'Test',
    'unit' => 'kg',
    'price' => 10.00,
    'description' => 'Temporary test material',
]);

$usageTestWorkId = $workRepository->create([
    'villa_id' => $villaId,
    'title' => 'Material Usage Delete Test',
    'category' => 'Test',
    'status' => 'planned',
    'description' => 'Work with material usage',
    'planned_date' => null,
    'actual_date' => null,
]);

$usageId = $materialUsageRepository->create([
    'work_id' => $usageTestWorkId,
    'material_id' => $usageTestMaterialId,
    'quantity' => 5.00,
    'date' => date('Y-m-d'),
]);

$result = $workService->delete(
    $usageTestWorkId
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['work_id'])
) {
    throw new RuntimeException(
        'Work with material usage was incorrectly deleted.'
    );
}

if (
    $workRepository->findById($usageTestWorkId)
    === null
) {
    throw new RuntimeException(
        'Protected work was deleted.'
    );
}

echo "WORK WITH MATERIAL USAGE DELETE rejection test passed."
    . PHP_EOL;
// --------------------------------------------------
// CLEAN UP
// --------------------------------------------------

/*
 * Clean up the material-usage deletion test.
 *
 * Order matters because of foreign keys:
 * material_usage -> work/material -> supplier
 */
$materialUsageRepository->delete($usageId);

$workRepository->delete(
    $usageTestWorkId
);

$materialRepository->delete(
    $usageTestMaterialId
);

$supplierRepository->delete(
    $usageTestSupplierId
);

/*
 * Clean up original work test data.
 */
$assignmentRepository->deleteByWork($workId);

$workRepository->delete(
    $workId
);

$assignmentRepository->deleteByWork(
    $transactionWorkId
);

$workRepository->delete(
    $transactionWorkId
);

/*
 * Now the villa has no remaining works,
 * so it can safely be deleted.
 */
$villaRepository->delete(
    $villaId
);

$typologyRepository->delete(
    $typologyId
);

$groupRepository->delete(
    $groupId
);

$teamRepository->delete(
    $teamId
);

$userRepository->delete(
    $userId
);

echo "TEST DATA cleanup successful." . PHP_EOL;
echo "All WorkService validation tests passed." . PHP_EOL;
}catch (Throwable $exception) {
    echo "WorkService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
