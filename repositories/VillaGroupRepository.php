<?php

declare(strict_types=1);

class VillaGroupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO villa_groups (name, description)
            VALUES (:name, :description)
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
            SELECT id, name, description
            FROM villa_groups
            ORDER BY id
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT id, name, description
            FROM villa_groups
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $villaGroup = $statement->fetch();

        return $villaGroup === false ? null : $villaGroup;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE villa_groups
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
            DELETE FROM villa_groups
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}