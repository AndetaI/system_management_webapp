<?php

declare(strict_types=1);

class ErrorHandler
{
    public function __construct(
        private Logger $logger
    ) {
    }

    public function register(): void
    {
        set_exception_handler(
            [$this, 'handleException']
        );

        set_error_handler(
            [$this, 'handleError']
        );
    }

    public function handleException(
        Throwable $exception
    ): void {
        $this->logger->exception($exception);

        if (!headers_sent()) {
            http_response_code(500);
        }

        $this->showSafeMessage();
    }

    public function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }

    private function showSafeMessage(): void
    {
        echo 'Something went wrong. Please try again later.';
    }
}
