<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $pdo = Database::getConnection();

    echo "Database connection successful." . PHP_EOL;
} catch (Throwable $exception) {
    echo "Database connection failed." . PHP_EOL;
}
