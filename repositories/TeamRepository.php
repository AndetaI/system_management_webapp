<?php

declare(strict_types=1);

class TeamRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO teams (
                name,
                description
            )
            VALUES (
                :name,
                :description
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                name,
                description,
                created_at
            FROM teams
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
                name,
                description,
                created_at
            FROM teams
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $team = $statement->fetch();

        return $team === false ? null : $team;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE teams
            SET
                name = :name,
                description = :description
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM teams
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}