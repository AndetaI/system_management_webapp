<?php

declare(strict_types=1);

class WorkRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO works (
                villa_id,
                title,
                category,
                status,
                description,
                planned_date,
                actual_date
            )
            VALUES (
                :villa_id,
                :title,
                :category,
                :status,
                :description,
                :planned_date,
                :actual_date
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'villa_id' => $data['villa_id'],
            'title' => $data['title'],
            'category' => $data['category'],
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
                villa_id,
                title,
                category,
                status,
                description,
                planned_date,
                actual_date
            FROM works
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
                villa_id,
                title,
                category,
                status,
                description,
                planned_date,
                actual_date
            FROM works
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $work = $statement->fetch();

        return $work === false ? null : $work;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE works
            SET
                villa_id = :villa_id,
                title = :title,
                category = :category,
                status = :status,
                description = :description,
                planned_date = :planned_date,
                actual_date = :actual_date
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'villa_id' => $data['villa_id'],
            'title' => $data['title'],
            'category' => $data['category'],
            'status' => $data['status'],
            'description' => $data['description'],
            'planned_date' => $data['planned_date'],
            'actual_date' => $data['actual_date'],
            'id' => $id,
        ]);
    }
public function existsByVillaId(int $villaId): bool
{
    $sql = '
        SELECT 1
        FROM works
        WHERE villa_id = :villa_id
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'villa_id' => $villaId,
    ]);

    return $statement->fetchColumn() !== false;
}
    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM works
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}