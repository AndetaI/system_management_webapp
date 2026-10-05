<?php

declare(strict_types=1);

require_once __DIR__ . '/../util/Logger.php';
require_once __DIR__ . '/../security/ErrorHandler.php';

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

$logger = new Logger();

$errorHandler = new ErrorHandler($logger);
$errorHandler->register();

throw new RuntimeException(
    'PRIVATE TECHNICAL TEST ERROR'
);
