<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/AuthService.php';

try {
    $pdo = Database::getConnection();

    $userRepository = new UserRepository($pdo);
    $authService = new AuthService($userRepository);

    $suffix = bin2hex(random_bytes(4));

    $username = 'auth_test_' . $suffix;
    $email = 'auth_' . $suffix . '@example.com';
    $password = 'SecurePass123!';

    /*
     * Create an ACTIVE test user.
     */
    $userId = $userRepository->create([
        'username' => $username,
        'name' => 'Authentication Test User',
        'email' => $email,
        'password_hash' => password_hash(
            $password,
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'active',
    ]);

    /*
     * TEST 1:
     * Correct username + password.
     */
    $result = $authService->authenticate(
        $username,
        $password
    );

    if (
        $result['success'] !== true ||
        $result['user']['id'] !== $userId
    ) {
        throw new RuntimeException(
            'Correct username/password authentication failed.'
        );
    }

    echo "USERNAME LOGIN test passed.\n";

    /*
     * TEST 2:
     * Correct email + password.
     */
    $result = $authService->authenticate(
        $email,
        $password
    );

    if ($result['success'] !== true) {
        throw new RuntimeException(
            'Correct email/password authentication failed.'
        );
    }

    echo "EMAIL LOGIN test passed.\n";

    /*
     * TEST 3:
     * Wrong password.
     */
    $result = $authService->authenticate(
        $username,
        'WrongPassword123!'
    );

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Wrong password was accepted.'
        );
    }

    echo "WRONG PASSWORD rejection test passed.\n";

    /*
     * TEST 4:
     * Unknown username.
     */
    $result = $authService->authenticate(
        'unknown_' . $suffix,
        $password
    );

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Unknown user was accepted.'
        );
    }

    echo "UNKNOWN USER rejection test passed.\n";

    /*
     * TEST 5:
     * Empty credentials.
     */
    $result = $authService->authenticate('', '');

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Empty credentials were accepted.'
        );
    }

    echo "EMPTY CREDENTIALS rejection test passed.\n";

    /*
     * TEST 6:
     * Inactive user.
     */
    $inactiveUsername = 'inactive_' . $suffix;

    $inactiveId = $userRepository->create([
        'username' => $inactiveUsername,
        'name' => 'Inactive Authentication User',
        'email' => 'inactive_' . $suffix . '@example.com',
        'password_hash' => password_hash(
            $password,
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'inactive',
    ]);

    $result = $authService->authenticate(
        $inactiveUsername,
        $password
    );

    if ($result['success'] !== false) {
        throw new RuntimeException(
            'Inactive user was allowed to authenticate.'
        );
    }

    echo "INACTIVE USER rejection test passed.\n";

    /*
     * Cleanup.
     */
    $userRepository->delete($inactiveId);
    $userRepository->delete($userId);

    echo "All AuthService tests passed.\n";

} catch (Throwable $exception) {
    echo "TEST FAILED: "
        . $exception->getMessage()
        . PHP_EOL;

    exit(1);
}