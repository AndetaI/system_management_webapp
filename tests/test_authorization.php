<?php

declare(strict_types=1);

require_once __DIR__
    . '/../security/SessionManager.php';

require_once __DIR__
    . '/../security/Authorization.php';

$tests = [];

function assertPermission(
    string $role,
    string $permission,
    bool $expected
): void {
    global $tests;

    $actual = Authorization::roleCan(
        $role,
        $permission
    );

    if ($actual !== $expected) {
        throw new RuntimeException(
            "Permission test failed: {$role} -> {$permission}"
        );
    }

    $result = $expected ? 'ALLOW' : 'DENY';

    $tests[] =
        "{$role}: {$permission} -> {$result} passed.";
}

try {

    /*
     * ADMIN
     */
    assertPermission(
        'admin',
        'users.create',
        true
    );

    assertPermission(
        'admin',
        'users.delete',
        true
    );

    assertPermission(
        'admin',
        'reports.view',
        true
    );

    /*
     * MANAGER
     */
    assertPermission(
        'manager',
        'reports.view',
        true
    );

    assertPermission(
        'manager',
        'users.create',
        false
    );

    assertPermission(
        'manager',
        'users.delete',
        false
    );

    /*
     * STAFF
     */
    assertPermission(
        'staff',
        'works.view',
        true
    );

    assertPermission(
        'staff',
        'works.update',
        true
    );

    assertPermission(
        'staff',
        'reports.view',
        false
    );

    assertPermission(
        'staff',
        'users.create',
        false
    );

    assertPermission(
        'staff',
        'materials.delete',
        false
    );

    /*
     * Unknown roles must have no permissions.
     */
    assertPermission(
        'unknown_role',
        'dashboard.view',
        false
    );

    foreach ($tests as $test) {
        echo $test . PHP_EOL;
    }

    echo PHP_EOL;
    echo "All RBAC authorization tests passed."
        . PHP_EOL;

} catch (Throwable $exception) {

    echo 'TEST FAILED: '
        . $exception->getMessage()
        . PHP_EOL;

    exit(1);
}
