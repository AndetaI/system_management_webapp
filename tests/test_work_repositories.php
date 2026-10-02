<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/TeamRepository.php';
require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';
require_once __DIR__ . '/../repositories/WorkRepository.php';
require_once __DIR__ . '/../repositories/WorkAssignmentRepository.php';

try {
    $pdo = Database::getConnection();

    $userRepository = new UserRepository($pdo);
    $teamRepository = new TeamRepository($pdo);
    $groupRepository = new VillaGroupRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $villaRepository = new VillaRepository($pdo);
    $workRepository = new WorkRepository($pdo);
    $assignmentRepository = new WorkAssignmentRepository($pdo);

    $suffix = (string) time();


    // 1. CREATE TEST USER
    $userId = $userRepository->create([
        'username' => 'work_test_' . $suffix,
        'name' => 'Work Test User',
        'email' => 'work_test_' . $suffix . '@example.test',
        'password_hash' => password_hash(
            'Temporary123!',
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'active',
    ]);

    echo "USER CREATE successful. ID: {$userId}" . PHP_EOL;


    // 2. CREATE TEST TEAM
    $teamId = $teamRepository->create([
        'name' => 'Work Test Team ' . $suffix,
        'description' => 'Temporary team for work testing',
    ]);

    echo "TEAM CREATE successful. ID: {$teamId}" . PHP_EOL;


    // 3. CREATE TEST VILLA GROUP
    $groupId = $groupRepository->create([
        'name' => 'Work Test Group ' . $suffix,
        'description' => 'Temporary villa group',
    ]);

    echo "GROUP CREATE successful. ID: {$groupId}" . PHP_EOL;


    // 4. CREATE TEST TYPOLOGY
    $typologyId = $typologyRepository->create([
        'group_id' => $groupId,
        'name' => 'Work Test Typology ' . $suffix,
        'description' => 'Temporary typology',
        'floors' => 2,
        'area' => 150.00,
    ]);

    echo "TYPOLOGY CREATE successful. ID: {$typologyId}" . PHP_EOL;


    // 5. CREATE TEST VILLA
    $villaId = $villaRepository->create([
        'typology_id' => $typologyId,
        'name' => 'WORK-TEST-' . $suffix,
        'status' => 'planned',
        'description' => 'Temporary villa',
        'planned_date' => '2026-10-02',
        'actual_date' => null,
    ]);

    echo "VILLA CREATE successful. ID: {$villaId}" . PHP_EOL;


    // 6. CREATE WORK
    $workId = $workRepository->create([
        'villa_id' => $villaId,
        'title' => 'Test Electrical Work',
        'category' => 'Electrical',
        'status' => 'planned',
        'description' => 'Temporary work for repository testing',
        'planned_date' => '2026-10-02',
        'actual_date' => null,
    ]);

    echo "WORK CREATE successful. ID: {$workId}" . PHP_EOL;


    // 7. READ WORK
    $work = $workRepository->findById($workId);

    if ($work === null) {
        throw new RuntimeException('Work could not be found.');
    }

    echo "WORK READ successful. Title: {$work['title']}" . PHP_EOL;


    // 8. UPDATE WORK
    $workRepository->update($workId, [
        'villa_id' => $villaId,
        'title' => 'Test Electrical Work',
        'category' => 'Electrical',
        'status' => 'in_progress',
        'description' => 'Updated work description',
        'planned_date' => '2026-10-02',
        'actual_date' => null,
    ]);

    $updatedWork = $workRepository->findById($workId);

    if (
        $updatedWork === null ||
        $updatedWork['status'] !== 'in_progress'
    ) {
        throw new RuntimeException(
            'Work was not updated correctly.'
        );
    }

    echo "WORK UPDATE successful." . PHP_EOL;


    // 9. TEST findAll()
    $works = $workRepository->findAll();

    echo "WORK READ ALL successful. Works found: "
        . count($works)
        . PHP_EOL;


    // 10. ASSIGN WORK TO USER
    $assignmentRepository->create(
        $workId,
        $userId,
        null
    );

    $assignment = $assignmentRepository->findByWork($workId);

    if (
        $assignment === null ||
        (int) $assignment['user_id'] !== $userId ||
        $assignment['team_id'] !== null
    ) {
        throw new RuntimeException(
            'User assignment was not created correctly.'
        );
    }

    echo "USER ASSIGNMENT successful." . PHP_EOL;


    // 11. CHANGE ASSIGNMENT FROM USER TO TEAM
    $assignmentRepository->update(
        $workId,
        null,
        $teamId
    );

    $assignment = $assignmentRepository->findByWork($workId);

    if (
        $assignment === null ||
        $assignment['user_id'] !== null ||
        (int) $assignment['team_id'] !== $teamId
    ) {
        throw new RuntimeException(
            'Team assignment was not updated correctly.'
        );
    }

    echo "TEAM ASSIGNMENT successful." . PHP_EOL;


    // 12. DELETE ASSIGNMENT
    $assignmentRepository->deleteByWork($workId);

    if ($assignmentRepository->findByWork($workId) !== null) {
        throw new RuntimeException(
            'Work assignment was not deleted.'
        );
    }

    echo "ASSIGNMENT DELETE successful." . PHP_EOL;


    // 13. DELETE WORK
    $workRepository->delete($workId);

    if ($workRepository->findById($workId) !== null) {
        throw new RuntimeException(
            'Work was not deleted.'
        );
    }

    echo "WORK DELETE successful." . PHP_EOL;


    // 14. CLEAN UP TEST DATA
    $villaRepository->delete($villaId);
    $typologyRepository->delete($typologyId);
    $groupRepository->delete($groupId);
    $teamRepository->delete($teamId);
    $userRepository->delete($userId);

    echo "TEST DATA cleanup successful." . PHP_EOL;

    echo "All work repository tests passed." . PHP_EOL;

} catch (Throwable $exception) {
    echo "Work repository test failed." . PHP_EOL;
}
