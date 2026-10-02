<?php

declare(strict_types=1);

class VillaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO villas (
                typology_id,
                name,
                status,
                description,
                planned_date,
                actual_date
            )
            VALUES (
                :typology_id,
                :name,
                :status,
                :description,
                :planned_date,
                :actual_date
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'typology_id' => $data['typology_id'],
            'name' => $data['name'],
            'status' => $data['status'],
            'description' => $data['description'],
            'planned_date' => $data['planned_date'],
            'actual_date' => $data['actual_date'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                typology_id,
                name,
                status,
                description,
                planned_date,
                actual_date
            FROM villas
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
                typology_id,
                name,
                status,
                description,
                planned_date,
                actual_date
            FROM villas
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $villa = $statement->fetch();

        return $villa === false ? null : $villa;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE villas
            SET
                typology_id = :typology_id,
                name = :name,
                status = :status,
                description = :description,
                planned_date = :planned_date,
                actual_date = :actual_date
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'typology_id' => $data['typology_id'],
            'name' => $data['name'],
            'status' => $data['status'],
            'description' => $data['description'],
            'planned_date' => $data['planned_date'],
            'actual_date' => $data['actual_date'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM villas
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}