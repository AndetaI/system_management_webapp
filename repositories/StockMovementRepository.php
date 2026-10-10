<?php

declare(strict_types=1);

class StockMovementRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO stock_movements (
                material_id,
                supplier_id,
                type,
                quantity,
                date,
                description
            )
            VALUES (
                :material_id,
                :supplier_id,
                :type,
                :quantity,
                :date,
                :description
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'material_id' => $data['material_id'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'date' => $data['date'],
            'description' => $data['description'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

  
    public function findAll(): array
    {
        $sql = '
            SELECT
                sm.id,
                sm.material_id,
                m.name AS material_name,
                sm.supplier_id,
                s.name AS supplier_name,
                sm.type,
                sm.quantity,
                sm.date,
                sm.description
            FROM stock_movements sm
            INNER JOIN materials m
                ON m.id = sm.material_id
            LEFT JOIN suppliers s
                ON s.id = sm.supplier_id
            ORDER BY sm.date DESC, sm.id DESC
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                id,
                material_id,
                supplier_id,
                type,
                quantity,
                date,
                description
            FROM stock_movements
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);

        $movement = $statement->fetch();

        return $movement === false ? null : $movement;
    }

    public function findByMaterial(int $materialId): array
    {
        $sql = '
            SELECT
                id,
                material_id,
                supplier_id,
                type,
                quantity,
                date,
                description
            FROM stock_movements
            WHERE material_id = :material_id
            ORDER BY date DESC, id DESC
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'material_id' => $materialId,
        ]);

        return $statement->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE stock_movements
            SET
                material_id = :material_id,
                supplier_id = :supplier_id,
                type = :type,
                quantity = :quantity,
                date = :date,
                description = :description
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'material_id' => $data['material_id'],
            'supplier_id' => $data['supplier_id'],
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'date' => $data['date'],
            'description' => $data['description'],
            'id' => $id,
        ]);
    }

    public function existsByMaterialId(int $materialId): bool
{
    $sql = '
        SELECT 1
        FROM stock_movements
        WHERE material_id = :material_id
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'material_id' => $materialId,
    ]);

    return $statement->fetchColumn() !== false;
}

    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM stock_movements
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}