<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/TeamRepository.php';
require_once __DIR__ . '/../repositories/TeamMemberRepository.php';

try {
    $pdo = Database::getConnection();

    $userRepository = new UserRepository($pdo);
    $teamRepository = new TeamRepository($pdo);
    $teamMemberRepository = new TeamMemberRepository($pdo);

    $suffix = (string) time();


    // 1. CREATE TEST USER
    $userId = $userRepository->create([
        'username' => 'team_test_' . $suffix,
        'name' => 'Team Test User',
        'email' => 'team_test_' . $suffix . '@example.test',
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
        'name' => 'Test Team ' . $suffix,
        'description' => 'Temporary team for repository testing',
    ]);

    echo "TEAM CREATE successful. ID: {$teamId}" . PHP_EOL;


    // 3. ADD USER TO TEAM
    $teamMemberRepository->addMember($teamId, $userId);

    echo "TEAM MEMBER CREATE successful." . PHP_EOL;


    // 4. FIND MEMBERS OF TEAM
    $members = $teamMemberRepository->findByTeam($teamId);

    if (
        count($members) !== 1 ||
        (int) $members[0]['user_id'] !== $userId
    ) {
        throw new RuntimeException(
            'Team membership could not be found.'
        );
    }

    echo "FIND BY TEAM successful." . PHP_EOL;


    // 5. FIND TEAMS OF USER
    $userTeams = $teamMemberRepository->findByUser($userId);

    if (
        count($userTeams) !== 1 ||
        (int) $userTeams[0]['team_id'] !== $teamId
    ) {
        throw new RuntimeException(
            'User team membership could not be found.'
        );
    }

    echo "FIND BY USER successful." . PHP_EOL;


    // 6. UPDATE TEAM
    $teamRepository->update($teamId, [
        'name' => 'Updated Test Team ' . $suffix,
        'description' => 'Updated team description',
    ]);

    $updatedTeam = $teamRepository->findById($teamId);

    if (
        $updatedTeam === null ||
        $updatedTeam['name'] !== 'Updated Test Team ' . $suffix
    ) {
        throw new RuntimeException(
            'Team was not updated correctly.'
        );
    }

    echo "TEAM UPDATE successful." . PHP_EOL;


    // 7. TEST TEAM findAll()
    $teams = $teamRepository->findAll();

    echo "TEAM READ ALL successful. Teams found: "
        . count($teams)
        . PHP_EOL;


    // 8. REMOVE USER FROM TEAM
    $teamMemberRepository->removeMember($teamId, $userId);

    $membersAfterDelete =
        $teamMemberRepository->findByTeam($teamId);

    if (count($membersAfterDelete) !== 0) {
        throw new RuntimeException(
            'Team membership was not removed.'
        );
    }

    echo "TEAM MEMBER DELETE successful." . PHP_EOL;


    // 9. DELETE TEST TEAM
    $teamRepository->delete($teamId);

    if ($teamRepository->findById($teamId) !== null) {
        throw new RuntimeException(
            'Team was not deleted.'
        );
    }

    echo "TEAM DELETE successful." . PHP_EOL;


    // 10. DELETE TEST USER
    $userRepository->delete($userId);

    if ($userRepository->findById($userId) !== null) {
        throw new RuntimeException(
            'User was not deleted.'
        );
    }

    echo "USER DELETE successful." . PHP_EOL;

    echo "All team repository tests passed." . PHP_EOL;

} catch (Throwable $exception) {
    echo "Team repository test failed." . PHP_EOL;
}
