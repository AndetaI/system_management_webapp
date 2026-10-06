<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO users (
                username,
                name,
                email,
                password_hash,
                role,
                status
            )
            VALUES (
                :username,
                :name,
                :email,
                :password_hash,
                :role,
                :status
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                username,
                name,
                email,
                role,
                status,
                created_at
            FROM users
            ORDER BY id
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                id,
                username,
                name,
                email,
                role,
                status,
                created_at
            FROM users
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'id' => $id,
        ]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = '
            SELECT
                id,
                username,
                name,
                email,
                role,
                status,
                created_at
            FROM users
            WHERE username = :username
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'username' => $username,
        ]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    } 

    public function findByEmail(string $email): ?array
    {
        $sql = '
            SELECT
                id,
                username,
                name,
                email,
                role,
                status,
                created_at
            FROM users
            WHERE email = :email
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'email' => $email,
        ]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }
    
    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE users
            SET
                username = :username,
                name = :name,
                email = :email,
                role = :role,
                status = :status
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function updatePassword(
    int $userId,
    string $passwordHash
): bool {
    $sql = '
        UPDATE users
        SET password_hash = :password_hash
        WHERE id = :id
    ';

    $statement = $this->pdo->prepare($sql);

    return $statement->execute([
        'password_hash' => $passwordHash,
        'id' => $userId,
    ]);
}
public function findForAuthentication(string $login): ?array
{
    $sql = '
        SELECT
            id,
            username,
            name,
            email,
            password_hash,
            role,
            status
        FROM users
        WHERE username = :username
           OR email = :email
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'username' => $login,
        'email' => $login,
    ]);

    $user = $statement->fetch();

    return $user === false ? null : $user;
}
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM users WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}