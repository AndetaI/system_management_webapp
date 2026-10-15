<?php

declare(strict_types=1);

class AuthController
{
    private Logger $logger;

    public function __construct(
        private AuthService $authService,
        Logger $logger
    ) {
        $this->logger = $logger;
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

                $this->logger->info(
                    'AUTH_LOGIN_SUCCESS',
                    [
                        'user_id' => (int) $result['user']['id'],
                        'role' => $result['user']['role'],
                    ]
                );

                header('Location: /?page=dashboard');
                exit;
            }

            $this->logger->warning(
                'AUTH_LOGIN_FAILED',
                [
                    'reason' => 'authentication_rejected',
                ]
            );

            $error = $result['message'];
        }

        require __DIR__
            . '/../views/pages/auth/login.php';
    }

    public function logout(): void
    {
        $userId = SessionManager::userId();

        if ($userId !== null) {
            $this->logger->info(
                'AUTH_LOGOUT',
                [
                    'user_id' => $userId,
                    'role' => SessionManager::role(),
                ]
            );
        }

        SessionManager::logout();

        header('Location: /?page=login');
        exit;
    }
}
