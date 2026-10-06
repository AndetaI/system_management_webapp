<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__
    . '/../repositories/WorkAssignmentRepository.php';

try {
    $pdo = Database::getConnection();

    $repository = new WorkAssignmentRepository($pdo);

    /*
     * staff_test has user ID 63.
     *
     * Work 42 is assigned directly to user 63.
     */
    $assigned = $repository->isWorkAssignedToUser(
        42,
        63
    );

    if ($assigned !== true) {
        throw new RuntimeException(
            'Assigned work was not recognized.'
        );
    }

    /*
     * Work 8 belongs to another user.
     */
    $notAssigned = $repository->isWorkAssignedToUser(
        8,
        63
    );

    if ($notAssigned !== false) {
        throw new RuntimeException(
            'Unassigned work was incorrectly allowed.'
        );
    }

    echo "DIRECT ASSIGNMENT test passed."
        . PHP_EOL;

    echo "UNASSIGNED WORK test passed."
        . PHP_EOL;

    echo "All work assignment RBAC tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {
    echo 'TEST FAILED: '
        . $exception->getMessage()
        . PHP_EOL;

    exit(1);
}
