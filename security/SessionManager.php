<?php

declare(strict_types=1);

class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = (
            (!empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off')
            ||
            (isset($_SERVER['SERVER_PORT'])
                && (int) $_SERVER['SERVER_PORT'] === 443)
        );


        if (!$isHttps) {
            throw new RuntimeException(
                'Secure sessions require HTTPS.'
            );
        }

        ini_set('session.use_strict_mode', '1');#rejects uninitialized session identifiers, helping mitigate session fixation.
        ini_set('session.use_only_cookies', '1');#ensures session cookies are restricted to HTTPS.
        ini_set('session.use_trans_sid', '0');#prevents session identifiers being accepted through URL parameters.
       
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function login(array $user): void
    {
        self::start();

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
    }

    public static function logout(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $parameters['path'],
                    'domain' => $parameters['domain'],
                    'secure' => $parameters['secure'],
                    'httponly' => $parameters['httponly'],
                    'samesite' => $parameters['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();
    }

    public static function isAuthenticated(): bool
    {
        self::start();

        return isset(
            $_SESSION['user']['id'],
            $_SESSION['user']['username'],
            $_SESSION['user']['role']
        );
    }

    public static function user(): ?array
    {
        self::start();

        return self::isAuthenticated()
            ? $_SESSION['user']
            : null;
    }

    public static function userId(): ?int
    {
        $user = self::user();

        return $user === null
            ? null
            : (int) $user['id'];
    }

    public static function role(): ?string
    {
        $user = self::user();

        return $user['role'] ?? null;
    }
}
