<?php

declare(strict_types=1);

require_once __DIR__ . '/../security/SessionManager.php';

$passedTests = [];

try {
    /*
     * Start session.
     */
    SessionManager::start();

    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException(
            'Session was not started.'
        );
    }

    $passedTests[] = 'SESSION START test passed.';

    /*
     * Check cookie security configuration.
     */
    $cookieParams = session_get_cookie_params();

    if ($cookieParams['httponly'] !== true) {
        throw new RuntimeException(
            'HttpOnly cookie flag is not enabled.'
        );
    }

    $passedTests[] = 'HTTPONLY COOKIE test passed.';

    if (($cookieParams['samesite'] ?? '') !== 'Lax') {
        throw new RuntimeException(
            'SameSite cookie setting is incorrect.'
        );
    }

    $passedTests[] = 'SAMESITE COOKIE test passed.';

    /*
     * Simulate successful login.
     */
    $testUser = [
        'id' => 999999,
        'username' => 'session_test',
        'name' => 'Session Test User',
        'role' => 'staff',
    ];

    $oldSessionId = session_id();

    SessionManager::login($testUser);

    $newSessionId = session_id();

    if (
        $oldSessionId === '' ||
        $newSessionId === '' ||
        $oldSessionId === $newSessionId
    ) {
        throw new RuntimeException(
            'Session ID was not regenerated after login.'
        );
    }

    $passedTests[] =
        'SESSION ID REGENERATION test passed.';

    /*
     * Authentication state.
     */
    if (!SessionManager::isAuthenticated()) {
        throw new RuntimeException(
            'Authenticated session was not detected.'
        );
    }

    $passedTests[] =
        'AUTHENTICATION STATE test passed.';

    /*
     * Check session user data.
     */
    $sessionUser = SessionManager::user();

    if (
        $sessionUser === null ||
        $sessionUser['id'] !== 999999 ||
        $sessionUser['username'] !== 'session_test' ||
        $sessionUser['role'] !== 'staff'
    ) {
        throw new RuntimeException(
            'Session user information is incorrect.'
        );
    }

    $passedTests[] =
        'SESSION USER DATA test passed.';

    /*
     * Passwords/hashes must never be stored
     * in the authenticated session.
     */
    if (
        isset($_SESSION['user']['password']) ||
        isset($_SESSION['user']['password_hash'])
    ) {
        throw new RuntimeException(
            'Sensitive password data was stored in session.'
        );
    }

    $passedTests[] =
        'NO PASSWORD IN SESSION test passed.';

    /*
     * Logout and destroy session.
     */
    SessionManager::logout();

    if (session_status() === PHP_SESSION_ACTIVE) {
        throw new RuntimeException(
            'Session remained active after logout.'
        );
    }

    $passedTests[] =
        'LOGOUT SESSION DESTRUCTION test passed.';

    /*
     * Print results only AFTER all operations that
     * may modify session headers are complete.
     */
    foreach ($passedTests as $message) {
        echo $message . PHP_EOL;
    }

    echo "All SessionManager tests passed.\n";

} catch (Throwable $exception) {
    echo 'TEST FAILED: '
        . $exception->getMessage()
        . PHP_EOL;

    exit(1);
}