<?php

declare(strict_types=1);

class StockRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $materialId, float $quantity): int
    {
        $sql = '
            INSERT INTO stock (
                material_id,
                quantity
            )
            VALUES (
                :material_id,
                :quantity
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'material_id' => $materialId,
            'quantity' => $quantity,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                material_id,
                quantity
            FROM stock
            ORDER BY id
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findByMaterial(int $materialId): ?array
    {
        $sql = '
            SELECT
                id,
                material_id,
                quantity
            FROM stock
            WHERE material_id = :material_id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'material_id' => $materialId,
        ]);

        $stock = $statement->fetch();

        return $stock === false ? null : $stock;
    }

    public function findByMaterialForUpdate(int $materialId): ?array
    {
        $sql = '
            SELECT
                id,
                material_id,
                quantity
            FROM stock
            WHERE material_id = :material_id
            FOR UPDATE
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'material_id' => $materialId,
        ]);

        $stock = $statement->fetch();

        return $stock === false ? null : $stock;
    }

    public function updateQuantity(
        int $materialId,
        float $quantity
    ): bool {
        $sql = '
            UPDATE stock
            SET quantity = :quantity
            WHERE material_id = :material_id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'quantity' => $quantity,
            'material_id' => $materialId,
        ]);
    }

    public function deleteByMaterial(int $materialId): bool
    {
        $sql = '
            DELETE FROM stock
            WHERE material_id = :material_id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'material_id' => $materialId,
        ]);
    }
}