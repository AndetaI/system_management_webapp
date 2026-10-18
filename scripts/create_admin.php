
<?php

declare(strict_types=1);

// Run only from the command line.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

try {
    $pdo = Database::getConnection();

    // Prevent creating another initial administrator.
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM users WHERE role = :role'
    );

    $stmt->execute(['role' => 'admin']);

    if ((int) $stmt->fetchColumn() > 0) {
        exit("An Admin account already exists.\n");
    }

    $username = 'admin';
    $name = 'System Administrator';
    $email = 'admin@barkea.local';

    // Read the password without displaying it.
    echo 'Enter initial Admin password: ';

    try {
        system('stty -echo');
        $password = rtrim(
            (string) fgets(STDIN),
            "\r\n"
        );
    } finally {
        system('stty echo');
        echo PHP_EOL;
    }

    if (strlen($password) < 12) {
        exit("Password must be at least 12 characters.\n");
    }

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $userRepository = new UserRepository($pdo);

    $adminId = $userRepository->create([
        'username' => $username,
        'name' => $name,
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => 'admin',
        'status' => 'active',
    ]);

    echo "Initial Admin account created successfully.\n";
    echo "Admin ID: {$adminId}\n";

} catch (Throwable $exception) {
    fwrite(
        STDERR,
        "Administrator setup failed. Check the configuration.\n"
    );
    exit(1);
}
