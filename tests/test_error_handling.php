<?php

declare(strict_types=1);

require_once __DIR__ . '/../util/Logger.php';
require_once __DIR__ . '/../security/ErrorHandler.php';

$testLogFile = __DIR__ . '/../storage/logs/test_app.log';

// Start with a clean test log.
if (file_exists($testLogFile)) {
    unlink($testLogFile);
}

$logger = new Logger($testLogFile);


// --------------------------------------------------
// TEST 1: NORMAL LOG MESSAGE
// --------------------------------------------------

$logger->info(
    'Test information message.',
    [
        'user_id' => 10,
        'operation' => 'test',
    ]
);

if (!file_exists($testLogFile)) {
    throw new RuntimeException(
        'Log file was not created.'
    );
}

$logContent = file_get_contents($testLogFile);

if (
    $logContent === false ||
    !str_contains(
        $logContent,
        'Test information message.'
    )
) {
    throw new RuntimeException(
        'Information message was not logged.'
    );
}

echo "LOGGER test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 2: SENSITIVE DATA REDACTION
// --------------------------------------------------

$logger->error(
    'Test login error.',
    [
        'username' => 'test_user',
        'password' => 'SuperSecret123!',
        'api_key' => 'SECRET-API-KEY',
        'token' => 'SECRET-TOKEN',
    ]
);

$logContent = file_get_contents($testLogFile);

if (
    str_contains($logContent, 'SuperSecret123!') ||
    str_contains($logContent, 'SECRET-API-KEY') ||
    str_contains($logContent, 'SECRET-TOKEN')
) {
    throw new RuntimeException(
        'Sensitive information was written to the log.'
    );
}

if (!str_contains($logContent, '[REDACTED]')) {
    throw new RuntimeException(
        'Sensitive values were not redacted.'
    );
}

echo "SENSITIVE DATA REDACTION test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 3: EXCEPTION LOGGING
// --------------------------------------------------

try {
    throw new RuntimeException(
        'Technical test exception.'
    );
} catch (Throwable $exception) {
    $logger->exception($exception);
}

$logContent = file_get_contents($testLogFile);

if (
    !str_contains(
        $logContent,
        'Technical test exception.'
    )
) {
    throw new RuntimeException(
        'Exception was not logged.'
    );
}

if (
    !str_contains(
        $logContent,
        'RuntimeException'
    )
) {
    throw new RuntimeException(
        'Exception type was not logged.'
    );
}

echo "EXCEPTION LOGGING test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 4: SAFE USER-FACING ERROR MESSAGE
// --------------------------------------------------

$errorHandler = new ErrorHandler($logger);

ob_start();

$errorHandler->handleException(
    new RuntimeException(
        'SQLSTATE[HY000] Secret database error'
    )
);

$userOutput = ob_get_clean();

if (
    str_contains($userOutput, 'SQLSTATE') ||
    str_contains($userOutput, 'database') ||
    str_contains($userOutput, '/var/www/')
) {
    throw new RuntimeException(
        'Technical information was exposed to the user.'
    );
}

if (
    !str_contains(
        $userOutput,
        'Something went wrong. Please try again later.'
    )
) {
    throw new RuntimeException(
        'Safe user message was not displayed.'
    );
}

echo "SAFE ERROR MESSAGE test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 5: TECHNICAL ERROR EXISTS IN CONTROLLED LOG
// --------------------------------------------------

$logContent = file_get_contents($testLogFile);

if (
    !str_contains(
        $logContent,
        'SQLSTATE[HY000] Secret database error'
    )
) {
    throw new RuntimeException(
        'Technical error was not written to the log.'
    );
}

echo "CONTROLLED ERROR LOGGING test passed." . PHP_EOL;


// --------------------------------------------------
// CLEAN UP TEST LOG
// --------------------------------------------------

if (file_exists($testLogFile)) {
    unlink($testLogFile);
}

echo "TEST LOG cleanup successful." . PHP_EOL;

echo "All secure error handling tests passed." . PHP_EOL;
