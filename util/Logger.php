<?php

declare(strict_types=1);

class Logger
{
    private string $logFile;

    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile
            ?? __DIR__ . '/../storage/logs/app.log';
    }

    public function error(
        string $message,
        array $context = []
    ): void {
        $this->write('ERROR', $message, $context);
    }

    public function warning(
        string $message,
        array $context = []
    ): void {
        $this->write('WARNING', $message, $context);
    }

    public function info(
        string $message,
        array $context = []
    ): void {
        $this->write('INFO', $message, $context);
    }

    public function exception(Throwable $exception): void
    {
        $context = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        $this->error(
            $exception->getMessage(),
            $context
        );
    }

    private function write(
        string $level,
        string $message,
        array $context
    ): void {
        $directory = dirname($this->logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }

        $entry = sprintf(
            "[%s] %s: %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            PHP_EOL
        );

        if ($context !== []) {
            $entry .= $this->formatContext($context);
        }

        $entry .= PHP_EOL;

        error_log(
            $entry,
            3,
            $this->logFile
        );
    }

    private function formatContext(array $context): string
    {
        $safeContext = [];

        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $safeContext[$key] = '[REDACTED]';

                continue;
            }

            if (
                is_string($value) ||
                is_int($value) ||
                is_float($value) ||
                is_bool($value) ||
                $value === null
            ) {
                $safeContext[$key] = $value;
            } else {
                $safeContext[$key] = '[COMPLEX VALUE]';
            }
        }

        return 'Context: '
            . json_encode(
                $safeContext,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            )
            . PHP_EOL;
    }

    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        $sensitiveKeys = [
            'password',
            'password_hash',
            'token',
            'secret',
            'authorization',
            'cookie',
            'api_key',
            'apikey',
        ];

        foreach ($sensitiveKeys as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }
}