<?php

declare(strict_types=1);

class MaterialRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO materials (
                supplier_id,
                name,
                category,
                unit,
                price,
                description
            )
            VALUES (
                :supplier_id,
                :name,
                :category,
                :unit,
                :price,
                :description
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'supplier_id' => $data['supplier_id'],
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'price' => $data['price'],
            'description' => $data['description'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                supplier_id,
                name,
                category,
                unit,
                price,
                description
            FROM materials
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
                supplier_id,
                name,
                category,
                unit,
                price,
                description
            FROM materials
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $material = $statement->fetch();

        return $material === false ? null : $material;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE materials
            SET
                supplier_id = :supplier_id,
                name = :name,
                category = :category,
                unit = :unit,
                price = :price,
                description = :description
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'supplier_id' => $data['supplier_id'],
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'price' => $data['price'],
            'description' => $data['description'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM materials
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }

    public function existsBySupplierId(int $supplierId): bool
{
    $sql = '
        SELECT 1
        FROM materials
        WHERE supplier_id = :supplier_id
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'supplier_id' => $supplierId,
    ]);

    return $statement->fetchColumn() !== false;
}
}
