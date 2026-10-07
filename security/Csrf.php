<?php

declare(strict_types=1);

class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Return the current CSRF token.
     *
     * A new cryptographically secure token is generated
     * if one does not already exist in the session.
     */
    public static function token(): string
    {
        SessionManager::start();

        if (
            !isset($_SESSION[self::SESSION_KEY]) ||
            !is_string($_SESSION[self::SESSION_KEY])
        ) {
            $_SESSION[self::SESSION_KEY] =
                bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Generate a hidden form field containing
     * the current CSRF token.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(
            self::token(),
            ENT_QUOTES,
            'UTF-8'
        );

        return '<input type="hidden" name="csrf_token" value="'
            . $token
            . '">';
    }

    /**
     * Verify a submitted CSRF token.
     */
    public static function verify(
        ?string $token
    ): bool {
        SessionManager::start();

        $sessionToken =
            $_SESSION[self::SESSION_KEY] ?? null;

        if (
            !is_string($sessionToken) ||
            $sessionToken === '' ||
            $token === null ||
            $token === ''
        ) {
            return false;
        }

        return hash_equals(
            $sessionToken,
            $token
        );
    }

    /**
     * Require a valid CSRF token.
     *
     * Invalid or missing tokens are rejected
     * with HTTP 403.
     */
    public static function requireValidToken(): void
    {
        $token = $_POST['csrf_token'] ?? null;

        if (
            !is_string($token) ||
            !self::verify($token)
        ) {
            http_response_code(403);

            require __DIR__
                . '/../views/pages/errors/403.php';

            exit;
        }
    }
}