<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

try {
    $pdo = Database::getConnection();
    $repository = new UserRepository($pdo);

    // CREATE
    $suffix = (string) time();

    $userData = [
        'username' => 'test_' . $suffix,
        'name' => 'Repository Test User',
        'email' => 'test_' . $suffix . '@example.test',
        'password_hash' => password_hash(
            'Temporary123!',
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'active',
    ];

    $userId = $repository->create($userData);

    echo "CREATE successful. User ID: {$userId}" . PHP_EOL;


    // READ
    $user = $repository->findById($userId);

    if ($user === null) {
        throw new RuntimeException('User could not be found.');
    }

    echo "READ successful. User: {$user['username']}" . PHP_EOL;


    // UPDATE
    $updatedData = [
        'username' => $userData['username'],
        'name' => 'Updated Test User',
        'email' => $userData['email'],
        'role' => 'manager',
        'status' => 'active',
    ];

    $repository->update($userId, $updatedData);

    $updatedUser = $repository->findById($userId);

    if (
        $updatedUser === null ||
        $updatedUser['name'] !== 'Updated Test User' ||
        $updatedUser['role'] !== 'manager'
    ) {
        throw new RuntimeException('User was not updated correctly.');
    }

    echo "UPDATE successful." . PHP_EOL;


    // READ ALL
    $users = $repository->findAll();

    echo "READ ALL successful. Users found: "
        . count($users)
        . PHP_EOL;


    // DELETE
    $repository->delete($userId);

    $deletedUser = $repository->findById($userId);

    if ($deletedUser !== null) {
        throw new RuntimeException('User was not deleted.');
    }

    echo "DELETE successful." . PHP_EOL;

} catch (Throwable $exception) {
    echo "UserRepository test failed." . PHP_EOL;
}
