<?php

declare(strict_types=1);

class MaterialUsageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO material_usage (
                work_id,
                material_id,
                quantity,
                date
            )
            VALUES (
                :work_id,
                :material_id,
                :quantity,
                :date
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'work_id' => $data['work_id'],
            'material_id' => $data['material_id'],
            'quantity' => $data['quantity'],
            'date' => $data['date'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                work_id,
                material_id,
                quantity,
                date
            FROM material_usage
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
                work_id,
                material_id,
                quantity,
                date
            FROM material_usage
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);

        $usage = $statement->fetch();

        return $usage === false ? null : $usage;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE material_usage
            SET
                work_id = :work_id,
                material_id = :material_id,
                quantity = :quantity,
                date = :date
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'work_id' => $data['work_id'],
            'material_id' => $data['material_id'],
            'quantity' => $data['quantity'],
            'date' => $data['date'],
            'id' => $id,
        ]);
    }

    public function existsByMaterialId(int $materialId): bool
{
    $sql = '
        SELECT 1
        FROM material_usage
        WHERE material_id = :material_id
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'material_id' => $materialId,
    ]);

    return $statement->fetchColumn() !== false;
}
public function existsByWorkId(int $workId): bool
{
    $sql = '
        SELECT 1
        FROM material_usage
        WHERE work_id = :work_id
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'work_id' => $workId,
    ]);

    return $statement->fetchColumn() !== false;
}
    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM material_usage
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}