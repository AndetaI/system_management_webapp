<?php

declare(strict_types=1);

class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function authenticate(
        string $login,
        string $password
    ): array {
        $login = trim($login);

        if ($login === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Username/email and password are required.',
            ];
        }

        $user = $this->userRepository->findForAuthentication($login);

        if (
            $user === null ||
            !password_verify($password, $user['password_hash'])
        ) {
            return [
                'success' => false,
                'message' => 'Invalid username or password.',
            ];
        }

        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'This account is inactive.',
            ];
        }

        return [
            'success' => true,
            'user' => [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ];
    }
}