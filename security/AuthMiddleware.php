<?php

declare(strict_types=1);

class AuthMiddleware
{
    public static function requireAuthentication(): void
    {
        if (!SessionManager::isAuthenticated()) {
            header('Location: /?page=login');
            exit;
        }
    }
}