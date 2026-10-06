<?php

declare(strict_types=1);

class AuthController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function login(): void
    {
        if (SessionManager::isAuthenticated()) {
            header('Location: /?page=dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim((string) ($_POST['login'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            $result = $this->authService->authenticate(
                $login,
                $password
            );

            if ($result['success'] === true) {
                SessionManager::login($result['user']);

                header('Location: /?page=dashboard');
                exit;
            }

            $error = $result['message'];
        }

        require __DIR__
            . '/../views/pages/auth/login.php';
    }

    public function logout(): void
    {
        SessionManager::logout();

        header('Location: /?page=login');
        exit;
    }
}