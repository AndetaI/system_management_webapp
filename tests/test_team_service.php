<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../util/Validator.php';

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/TeamRepository.php';
require_once __DIR__ . '/../repositories/TeamMemberRepository.php';
require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';
require_once __DIR__ . '/../repositories/WorkAssignmentRepository.php';

require_once __DIR__ . '/../services/TeamService.php';

try {
    $pdo = Database::getConnection();

    $userRepository = new UserRepository($pdo);
    $teamRepository = new TeamRepository($pdo);
    $teamMemberRepository = new TeamMemberRepository($pdo);
    $groupRepository = new VillaGroupRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $villaRepository = new VillaRepository($pdo);
    $workRepository = new WorkRepository($pdo);
    $workAssignmentRepository = new WorkAssignmentRepository($pdo);
    $teamService = new TeamService(
        $teamRepository,
        $userRepository,
        $teamMemberRepository,
        $workAssignmentRepository
    );

    $suffix = (string) time();

    // --------------------------------------------------
    // TEST 1: VALID TEAM CREATION
    // --------------------------------------------------

    $result = $teamService->create([
        'name' => 'Test Team ' . $suffix,
        'description' => 'Temporary test team',
    ]);

    if (
        $result['success'] !== true ||
        !isset($result['id'])
    ) {
        throw new RuntimeException(
            'Valid team was rejected.'
        );
    }

    $teamId = (int) $result['id'];

    echo "VALID TEAM test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 2: MISSING TEAM NAME
    // --------------------------------------------------

    $result = $teamService->create([
        'name' => '',
        'description' => 'Invalid team',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['name'])
    ) {
        throw new RuntimeException(
            'Missing team name was accepted.'
        );
    }

    echo "MISSING TEAM NAME test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 3: DUPLICATE TEAM NAME
    // --------------------------------------------------

    $result = $teamService->create([
        'name' => 'Test Team ' . $suffix,
        'description' => 'Duplicate team',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['name'])
    ) {
        throw new RuntimeException(
            'Duplicate team name was accepted.'
        );
    }

    echo "DUPLICATE TEAM NAME test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 4: VALID TEAM UPDATE
    // --------------------------------------------------

    $updatedName = 'Updated Team ' . $suffix;

    $result = $teamService->update(
        $teamId,
        [
            'name' => $updatedName,
            'description' => 'Updated description',
        ]
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid team update was rejected.'
        );
    }

    $updatedTeam = $teamRepository->findById($teamId);

    if (
        $updatedTeam === null ||
        $updatedTeam['name'] !== $updatedName
    ) {
        throw new RuntimeException(
            'Team was not correctly updated.'
        );
    }

    echo "VALID TEAM UPDATE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 5: CREATE SECOND TEAM
    // --------------------------------------------------

    $secondResult = $teamService->create([
        'name' => 'Second Team ' . $suffix,
        'description' => 'Second temporary team',
    ]);

    if (
        $secondResult['success'] !== true ||
        !isset($secondResult['id'])
    ) {
        throw new RuntimeException(
            'Second team could not be created.'
        );
    }

    $secondTeamId = (int) $secondResult['id'];


    // --------------------------------------------------
    // TEST 6: DUPLICATE NAME DURING UPDATE
    // --------------------------------------------------

    $result = $teamService->update(
        $teamId,
        [
            'name' => 'Second Team ' . $suffix,
            'description' => 'Should fail',
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['name'])
    ) {
        throw new RuntimeException(
            'Duplicate team name update was accepted.'
        );
    }

    echo "DUPLICATE TEAM UPDATE test passed." . PHP_EOL;


    // --------------------------------------------------
    // TEST 7: CREATE USER FOR MEMBERSHIP TEST
    // --------------------------------------------------

    $userId = $userRepository->create([
        'username' => 'teamtest_' . $suffix,
        'name' => 'Team Test User',
        'email' => 'teamtest_' . $suffix . '@example.test',
        'password_hash' => password_hash(
            'SecureTest123!',
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'active',
    ]);


    // --------------------------------------------------
    // TEST 8: ADD VALID TEAM MEMBER
    // --------------------------------------------------

    $result = $teamService->addMember(
        $teamId,
        $userId
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid team member was rejected.'
        );
    }

    echo "ADD TEAM MEMBER test passed." . PHP_EOL;

    // --------------------------------------------------
// TEST: DUPLICATE TEAM MEMBER
// --------------------------------------------------

$result = $teamService->addMember(
    $teamId,
    $userId
);

if (
    $result['success'] !== false ||
    !isset($result['errors']['user_id'])
) {
    throw new RuntimeException(
        'Duplicate team member was accepted.'
    );
}

if (
    $result['errors']['user_id'][0]
    !== 'User is already a member of this team.'
) {
    throw new RuntimeException(
        'Duplicate team member error message is incorrect.'
    );
}

echo "DUPLICATE TEAM MEMBER rejection test passed."
    . PHP_EOL;


    // --------------------------------------------------
    // TEST 9: NON-EXISTING USER
    // --------------------------------------------------

    $result = $teamService->addMember(
        $teamId,
        999999999
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['user_id'])
    ) {
        throw new RuntimeException(
            'Non-existing user was accepted as team member.'
        );
    }

    echo "INVALID TEAM MEMBER test passed." . PHP_EOL;
// --------------------------------------------------
// TEAM DELETE TEST
// --------------------------------------------------

$deleteTeamId = $teamRepository->create([
    'name' => 'Team Delete Test' . $suffix,
    'description' => 'Temporary team for delete test',
]);

$result = $teamService->delete($deleteTeamId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Team deletion failed.'
    );
}

if ($teamRepository->findById($deleteTeamId) !== null) {
    throw new RuntimeException(
        'Team still exists after deletion.'
    );
}

echo "TEAM DELETE test passed." . PHP_EOL;


// --------------------------------------------------
// TEAM MEMBER CASCADE DELETE TEST
// --------------------------------------------------

$cascadeTeamId = $teamRepository->create([
    'name' => 'Team Cascade Delete Test' . $suffix,
    'description' => 'Temporary team for cascade test',
]);

$cascadeUserId = $userRepository->create([
    'username' => 'team_cascade_user_' . $suffix,
    'name' => 'Team Cascade User',
    'email' => 'team_cascade_user_' . $suffix . '@example.test',
    'password_hash' => password_hash(
        'TestPassword123!',
        PASSWORD_DEFAULT
    ),
    'role' => 'staff',
    'status' => 'active',
]);

$teamMemberRepository->addMember(
    $cascadeTeamId,
    $cascadeUserId
);

if (
    !$teamMemberRepository->exists(
        $cascadeTeamId,
        $cascadeUserId
    )
) {
    throw new RuntimeException(
        'Team membership was not created.'
    );
}

$result = $teamService->delete($cascadeTeamId);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Team cascade deletion failed.'
    );
}

if ($teamRepository->findById($cascadeTeamId) !== null) {
    throw new RuntimeException(
        'Team still exists after deletion.'
    );
}

if (
    $teamMemberRepository->exists(
        $cascadeTeamId,
        $cascadeUserId
    )
) {
    throw new RuntimeException(
        'Team membership was not deleted by cascade.'
    );
}

/*
 * Deleting the team must NOT delete the user.
 */
if ($userRepository->findById($cascadeUserId) === null) {
    throw new RuntimeException(
        'User was incorrectly deleted with team.'
    );
}

echo "TEAM MEMBER CASCADE DELETE test passed."
    . PHP_EOL;


// --------------------------------------------------
// TEAM ASSIGNED TO WORK DELETE REJECTION TEST
// --------------------------------------------------

/*
 * Create temporary villa hierarchy because
 * every work requires a valid villa_id.
 */

$deleteTestGroupId = $groupRepository->create([
    'name' => 'Team Delete Test Group' . $suffix,
    'description' => 'Temporary test group',
]);

$deleteTestTypologyId = $typologyRepository->create([
    'group_id' => $deleteTestGroupId,
    'name' => 'Team Delete Test Typology' . $suffix,
    'description' => 'Temporary test typology',
    'floors' => 1,
    'area' => 100.00,
]);

$deleteTestVillaId = $villaRepository->create([
    'typology_id' => $deleteTestTypologyId,
    'name' => 'TEAM-DELETE-TEST-VILLA' . $suffix,
    'status' => 'planned',
    'description' => 'Temporary test villa',
    'planned_date' => null,
    'actual_date' => null,
]);

$assignedTeamId = $teamRepository->create([
    'name' => 'Assigned Team Delete Test' . $suffix,
    'description' => 'Team assigned to work',
]);

$assignedWorkId = $workRepository->create([
    'villa_id' => $deleteTestVillaId,
    'title' => 'Team Delete Protection Test',
    'category' => 'Test',
    'status' => 'planned',
    'description' => 'Temporary test work',
    'planned_date' => null,
    'actual_date' => null,
]);

$workAssignmentRepository->create(
    $assignedWorkId,
    null,
    $assignedTeamId
);

/*
 * The service must reject deletion because
 * this team is assigned to a work.
 */
$result = $teamService->delete($assignedTeamId);

if (
    $result['success'] !== false ||
    !isset($result['errors']['team_id'])
) {
    throw new RuntimeException(
        'Assigned team was incorrectly allowed to be deleted.'
    );
}

if ($teamRepository->findById($assignedTeamId) === null) {
    throw new RuntimeException(
        'Assigned team was incorrectly deleted.'
    );
}

echo "TEAM ASSIGNED TO WORK DELETE rejection test passed."
    . PHP_EOL;


// --------------------------------------------------
// DELETE TEST CLEANUP
// --------------------------------------------------

/*
 * Remove the work assignment first.
 */
$workAssignmentRepository->deleteByWork(
    $assignedWorkId
);

/*
 * Then work.
 */
$workRepository->delete(
    $assignedWorkId
);

/*
 * Team is no longer assigned and can be deleted.
 */
$teamRepository->delete(
    $assignedTeamId
);

/*
 * Now the temporary villa hierarchy can be
 * removed from child to parent.
 */
$villaRepository->delete(
    $deleteTestVillaId
);

$typologyRepository->delete(
    $deleteTestTypologyId
);

$groupRepository->delete(
    $deleteTestGroupId
);

/*
 * The cascade test deleted only the membership,
 * not the user itself.
 */
$userRepository->delete(
    $cascadeUserId
);

    // --------------------------------------------------
    // CLEAN UP
    // --------------------------------------------------

    $teamMemberRepository->removeMember(
        $teamId,
        $userId
    );

    $userRepository->delete($userId);

    $teamRepository->delete($secondTeamId);
    $teamRepository->delete($teamId);

    echo "TEST DATA cleanup successful." . PHP_EOL;
    echo "All TeamService validation tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "TeamService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
