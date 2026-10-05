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
require_once __DIR__ . '/../services/UserService.php';

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
    $userService = new UserService(
        $userRepository, 
        $workAssignmentRepository
        );

    $suffix = (string) time();

    $username = 'testuser_' . $suffix;
    $email = 'test_' . $suffix . '@example.test';


    // TEST 1: VALID USER

    $result = $userService->create([
        'username' => $username,
        'name' => 'Test User',
        'email' => $email,
        'password' => 'SecureTest123!',
        'role' => 'staff',
        'status' => 'active',
    ]);

    if (
        $result['success'] !== true ||
        !isset($result['id'])
    ) {
        throw new RuntimeException(
            'Valid user was rejected.'
        );
    }

    $userId = (int) $result['id'];

    echo "VALID USER test passed." . PHP_EOL;


    // TEST 2: INVALID EMAIL

    $result = $userService->create([
        'username' => 'emailtest_' . $suffix,
        'name' => 'Email Test',
        'email' => 'not-an-email',
        'password' => 'SecureTest123!',
        'role' => 'staff',
        'status' => 'active',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['email'])
    ) {
        throw new RuntimeException(
            'Invalid email validation failed.'
        );
    }

    echo "INVALID EMAIL test passed." . PHP_EOL;


    // TEST 3: INVALID ROLE

    $result = $userService->create([
        'username' => 'roletest_' . $suffix,
        'name' => 'Role Test',
        'email' => 'role_' . $suffix . '@example.test',
        'password' => 'SecureTest123!',
        'role' => 'worker',
        'status' => 'active',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['role'])
    ) {
        throw new RuntimeException(
            'Invalid role validation failed.'
        );
    }

    echo "INVALID ROLE test passed." . PHP_EOL;


    // TEST 4: SHORT PASSWORD

    $result = $userService->create([
        'username' => 'passtest_' . $suffix,
        'name' => 'Password Test',
        'email' => 'password_' . $suffix . '@example.test',
        'password' => '123',
        'role' => 'staff',
        'status' => 'active',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['password'])
    ) {
        throw new RuntimeException(
            'Password validation failed.'
        );
    }

    echo "PASSWORD validation test passed." . PHP_EOL;


    // TEST 5: DUPLICATE USERNAME

    $result = $userService->create([
        'username' => $username,
        'name' => 'Duplicate Username',
        'email' => 'different_' . $suffix . '@example.test',
        'password' => 'SecureTest123!',
        'role' => 'manager',
        'status' => 'active',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['username'])
    ) {
        throw new RuntimeException(
            'Duplicate username was not rejected.'
        );
    }

    echo "DUPLICATE USERNAME test passed." . PHP_EOL;


    // TEST 6: DUPLICATE EMAIL

    $result = $userService->create([
        'username' => 'different_' . $suffix,
        'name' => 'Duplicate Email',
        'email' => $email,
        'password' => 'SecureTest123!',
        'role' => 'manager',
        'status' => 'active',
    ]);

    if (
        $result['success'] !== false ||
        !isset($result['errors']['email'])
    ) {
        throw new RuntimeException(
            'Duplicate email was not rejected.'
        );
    }

    echo "DUPLICATE EMAIL test passed." . PHP_EOL;


    // TEST 7: VERIFY PASSWORD IS HASHED

    $statement = $pdo->prepare(
        'SELECT password_hash
         FROM users
         WHERE id = :id'
    );

    $statement->execute([
        'id' => $userId,
    ]);

    $storedUser = $statement->fetch();

    if ($storedUser === false) {
        throw new RuntimeException(
            'Created user could not be found.'
        );
    }

    if (
        $storedUser['password_hash']
        === 'SecureTest123!'
    ) {
        throw new RuntimeException(
            'Password was stored as plain text.'
        );
    }

    if (
        !password_verify(
            'SecureTest123!',
            $storedUser['password_hash']
        )
    ) {
        throw new RuntimeException(
            'Stored password hash is invalid.'
        );
    }

    echo "PASSWORD HASHING test passed." . PHP_EOL;
    // TEST 8: VALID USER UPDATE

    $updatedUsername = 'updated_' . $suffix;
    $updatedEmail = 'updated_' . $suffix . '@example.test';

    $result = $userService->update(
        $userId,
        [
            'username' => $updatedUsername,
            'name' => 'Updated Test User',
            'email' => $updatedEmail,
            'role' => 'manager',
            'status' => 'active',
            'password' => '',
        ]
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Valid user update was rejected.'
        );
    }

    $updatedUser = $userRepository->findById($userId);

    if (
        $updatedUser === null ||
        $updatedUser['username'] !== $updatedUsername ||
        $updatedUser['email'] !== $updatedEmail ||
        $updatedUser['role'] !== 'manager'
    ) {
        throw new RuntimeException(
            'User was not correctly updated.'
        );
    }

    echo "VALID USER UPDATE test passed." . PHP_EOL;


    // TEST 9: OLD PASSWORD MUST STILL WORK
    // No new password was supplied in Test 8.

    $statement = $pdo->prepare(
        'SELECT password_hash
         FROM users
         WHERE id = :id'
    );

    $statement->execute([
        'id' => $userId,
    ]);

    $passwordData = $statement->fetch();

    if (
        $passwordData === false ||
        !password_verify(
            'SecureTest123!',
            $passwordData['password_hash']
        )
    ) {
        throw new RuntimeException(
            'Existing password was changed during normal update.'
        );
    }

    echo "PASSWORD PRESERVATION test passed." . PHP_EOL;


    // TEST 10: INVALID ROLE UPDATE

    $result = $userService->update(
        $userId,
        [
            'username' => $updatedUsername,
            'name' => 'Updated Test User',
            'email' => $updatedEmail,
            'role' => 'worker',
            'status' => 'active',
            'password' => '',
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['role'])
    ) {
        throw new RuntimeException(
            'Invalid role update was accepted.'
        );
    }

    echo "INVALID UPDATE ROLE rejection test passed."
        . PHP_EOL;


    // TEST 11: INVALID STATUS UPDATE

    $result = $userService->update(
        $userId,
        [
            'username' => $updatedUsername,
            'name' => 'Updated Test User',
            'email' => $updatedEmail,
            'role' => 'manager',
            'status' => 'deleted',
            'password' => '',
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['status'])
    ) {
        throw new RuntimeException(
            'Invalid status update was accepted.'
        );
    }

    echo "INVALID UPDATE STATUS rejection test passed."
        . PHP_EOL;


    // TEST 12:
    // CREATE SECOND USER FOR DUPLICATE UPDATE TESTS

    $secondUsername = 'second_' . $suffix;
    $secondEmail = 'second_' . $suffix . '@example.test';

    $secondResult = $userService->create([
        'username' => $secondUsername,
        'name' => 'Second Test User',
        'email' => $secondEmail,
        'password' => 'SecondSecure123!',
        'role' => 'staff',
        'status' => 'active',
    ]);

    if (
        $secondResult['success'] !== true ||
        !isset($secondResult['id'])
    ) {
        throw new RuntimeException(
            'Second test user could not be created.'
        );
    }

    $secondUserId = (int) $secondResult['id'];


    // Try to give user 1 the username of user 2.

    $result = $userService->update(
        $userId,
        [
            'username' => $secondUsername,
            'name' => 'Updated Test User',
            'email' => $updatedEmail,
            'role' => 'manager',
            'status' => 'active',
            'password' => '',
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['username'])
    ) {
        throw new RuntimeException(
            'Duplicate username update was accepted.'
        );
    }

    echo "DUPLICATE UPDATE USERNAME rejection test passed."
        . PHP_EOL;


    // TEST 13: DUPLICATE EMAIL UPDATE

    $result = $userService->update(
        $userId,
        [
            'username' => $updatedUsername,
            'name' => 'Updated Test User',
            'email' => $secondEmail,
            'role' => 'manager',
            'status' => 'active',
            'password' => '',
        ]
    );

    if (
        $result['success'] !== false ||
        !isset($result['errors']['email'])
    ) {
        throw new RuntimeException(
            'Duplicate email update was accepted.'
        );
    }

    echo "DUPLICATE UPDATE EMAIL rejection test passed."
        . PHP_EOL;


    // TEST 14: CHANGE PASSWORD

    $newPassword = 'NewSecure456!';

    $result = $userService->update(
        $userId,
        [
            'username' => $updatedUsername,
            'name' => 'Updated Test User',
            'email' => $updatedEmail,
            'role' => 'manager',
            'status' => 'active',
            'password' => $newPassword,
        ]
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Password update was rejected.'
        );
    }

    $statement = $pdo->prepare(
        'SELECT password_hash
         FROM users
         WHERE id = :id'
    );

    $statement->execute([
        'id' => $userId,
    ]);

    $passwordData = $statement->fetch();

    if ($passwordData === false) {
        throw new RuntimeException(
            'Updated password could not be checked.'
        );
    }

    if ($passwordData['password_hash'] === $newPassword) {
        throw new RuntimeException(
            'New password was stored as plain text.'
        );
    }

    if (
        !password_verify(
            $newPassword,
            $passwordData['password_hash']
        )
    ) {
        throw new RuntimeException(
            'New password hash is invalid.'
        );
    }

    if (
        password_verify(
            'SecureTest123!',
            $passwordData['password_hash']
        )
    ) {
        throw new RuntimeException(
            'Old password still works after password change.'
        );
    }

    echo "PASSWORD UPDATE test passed." . PHP_EOL;
// --------------------------------------------------
// USER DELETE TEST
// --------------------------------------------------

$deleteUserId = $userRepository->create([
    'username' => 'delete_user_' . $suffix,
    'name' => 'Delete Test User',
    'email' => 'delete_user_' . $suffix . '@example.test',
    'password_hash' => password_hash(
        'TestPassword123!',
        PASSWORD_DEFAULT
    ),
    'role' => 'staff',
    'status' => 'active',
]);

$result = $userService->delete(
    $deleteUserId
);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'Normal user deletion failed.'
    );
}

if (
    $userRepository->findById($deleteUserId)
    !== null
) {
    throw new RuntimeException(
        'User still exists after deletion.'
    );
}

echo "USER DELETE test passed." . PHP_EOL;


// --------------------------------------------------
// USER TEAM MEMBERSHIP CASCADE DELETE TEST
// --------------------------------------------------

$cascadeUserId = $userRepository->create([
    'username' => 'user_cascade_' . $suffix,
    'name' => 'User Cascade Test',
    'email' => 'user_cascade_' . $suffix . '@example.test',
    'password_hash' => password_hash(
        'TestPassword123!',
        PASSWORD_DEFAULT
    ),
    'role' => 'staff',
    'status' => 'active',
]);

$cascadeTeamId = $teamRepository->create([
    'name' => 'User Cascade Team ' . $suffix,
    'description' => 'Temporary team for user cascade test',
]);

$teamMemberRepository->addMember(
    $cascadeTeamId,
    $cascadeUserId
);

/*
 * Verify membership exists before deleting user.
 */
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

$result = $userService->delete(
    $cascadeUserId
);

if ($result['success'] !== true) {
    throw new RuntimeException(
        'User with team membership could not be deleted.'
    );
}

/*
 * User must be gone.
 */
if (
    $userRepository->findById($cascadeUserId)
    !== null
) {
    throw new RuntimeException(
        'User still exists after deletion.'
    );
}

/*
 * team_members uses ON DELETE CASCADE,
 * therefore membership must also be gone.
 */
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
 * The team itself must remain.
 */
if (
    $teamRepository->findById($cascadeTeamId)
    === null
) {
    throw new RuntimeException(
        'Team was incorrectly deleted with user.'
    );
}

echo "USER TEAM MEMBERSHIP CASCADE DELETE test passed."
    . PHP_EOL;


// --------------------------------------------------
// USER ASSIGNED TO WORK DELETE REJECTION TEST
// --------------------------------------------------

/*
 * Create temporary villa hierarchy:
 *
 * group -> typology -> villa -> work
 */

$userDeleteGroupId = $groupRepository->create([
    'name' => 'User Delete Test Group ' . $suffix,
    'description' => 'Temporary test group',
]);

$userDeleteTypologyId = $typologyRepository->create([
    'group_id' => $userDeleteGroupId,
    'name' => 'User Delete Test Typology ' . $suffix,
    'description' => 'Temporary test typology',
    'floors' => 1,
    'area' => 100.00,
]);

$userDeleteVillaId = $villaRepository->create([
    'typology_id' => $userDeleteTypologyId,
    'name' => 'USER-DELETE-TEST-VILLA-' . $suffix,
    'status' => 'planned',
    'description' => 'Temporary villa for user delete test',
    'planned_date' => null,
    'actual_date' => null,
]);

$assignedUserId = $userRepository->create([
    'username' => 'assigned_user_' . $suffix,
    'name' => 'Assigned User Test',
    'email' => 'assigned_user_' . $suffix . '@example.test',
    'password_hash' => password_hash(
        'TestPassword123!',
        PASSWORD_DEFAULT
    ),
    'role' => 'staff',
    'status' => 'active',
]);

$assignedWorkId = $workRepository->create([
    'villa_id' => $userDeleteVillaId,
    'title' => 'User Delete Protection Test',
    'category' => 'Test',
    'status' => 'planned',
    'description' => 'Temporary work for user delete test',
    'planned_date' => null,
    'actual_date' => null,
]);

/*
 * Assign work directly to the user.
 *
 * team_id is null because a work is assigned
 * to either a user OR a team.
 */
$workAssignmentRepository->create(
    $assignedWorkId,
    $assignedUserId,
    null
);

$result = $userService->delete(
    $assignedUserId
);

/*
 * Deletion must be rejected.
 */
if (
    $result['success'] !== false ||
    !isset($result['errors']['user_id'])
) {
    throw new RuntimeException(
        'Assigned user was incorrectly allowed to be deleted.'
    );
}

/*
 * Protected user must still exist.
 */
if (
    $userRepository->findById($assignedUserId)
    === null
) {
    throw new RuntimeException(
        'Assigned user was incorrectly deleted.'
    );
}

echo "USER ASSIGNED TO WORK DELETE rejection test passed."
    . PHP_EOL;


// --------------------------------------------------
// DELETE TEST CLEANUP
// --------------------------------------------------

/*
 * Remove work assignment first.
 */
$workAssignmentRepository->deleteByWork(
    $assignedWorkId
);

/*
 * Then delete work.
 */
$workRepository->delete(
    $assignedWorkId
);

/*
 * User is no longer assigned to a work,
 * so the temporary user can be removed.
 */
$userRepository->delete(
    $assignedUserId
);

/*
 * Remove temporary villa hierarchy
 * from child to parent.
 */
$villaRepository->delete(
    $userDeleteVillaId
);

$typologyRepository->delete(
    $userDeleteTypologyId
);

$groupRepository->delete(
    $userDeleteGroupId
);

/*
 * cascadeTeamId remained after its user
 * was deleted, so remove the test team.
 */
$teamRepository->delete(
    $cascadeTeamId
);
    // CLEAN UP
    $userRepository->delete($secondUserId);
    $userRepository->delete($userId);

    echo "TEST DATA cleanup successful." . PHP_EOL;

    echo "All UserService validation tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo "UserService test failed." . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
