<?php

declare(strict_types=1);

require_once __DIR__
    . '/../security/SessionManager.php';

require_once __DIR__
    . '/../security/Csrf.php';

SessionManager::start();

$passed = 0;
$failed = 0;

function testResult(
    string $name,
    bool $condition
): void {
    global $passed, $failed;

    if ($condition) {
        echo "[PASS] {$name}\n";
        $passed++;
    } else {
        echo "[FAIL] {$name}\n";
        $failed++;
    }
}


/*
 * Test 1:
 * A token should be generated.
 */
$token = Csrf::token();

testResult(
    'CSRF token is generated',
    $token !== ''
);


/*
 * Test 2:
 * Token should be stored in the session.
 */
testResult(
    'CSRF token is stored in session',
    isset($_SESSION['csrf_token'])
    && $_SESSION['csrf_token'] === $token
);


/*
 * Test 3:
 * Calling token() again should return
 * the same token for the current session.
 */
$secondToken = Csrf::token();

testResult(
    'CSRF token remains stable in session',
    $token === $secondToken
);


/*
 * Test 4:
 * Correct token should be accepted.
 */
testResult(
    'Valid CSRF token is accepted',
    Csrf::verify($token)
);


/*
 * Test 5:
 * Fake token should be rejected.
 */
testResult(
    'Invalid CSRF token is rejected',
    !Csrf::verify(
        'this-is-not-a-valid-token'
    )
);


/*
 * Test 6:
 * Missing token should be rejected.
 */
testResult(
    'Missing CSRF token is rejected',
    !Csrf::verify(null)
);


/*
 * Test 7:
 * Empty token should be rejected.
 */
testResult(
    'Empty CSRF token is rejected',
    !Csrf::verify('')
);


/*
 * Test 8:
 * Generated form field should contain
 * the CSRF field name.
 */
$field = Csrf::field();

testResult(
    'CSRF form field is generated',
    str_contains(
        $field,
        'name="csrf_token"'
    )
);


echo "\n";

echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if ($failed > 0) {
    exit(1);
}

echo "\nAll CSRF tests passed.\n";
