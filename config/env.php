<?php

declare(strict_types=1);

$envFile = __DIR__ . '/../.env';

if (!is_readable($envFile)) {
    throw new RuntimeException('Environment configuration file not found.');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    [$name, $value] = array_map(
        'trim',
        explode('=', $line, 2)
    );

    $_ENV[$name] = $value;
}
