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
                m.id,
                m.supplier_id,
                m.name,
                m.category,
                m.unit,
                m.price,
                m.description,
                COALESCE(s.quantity, 0) AS quantity
            FROM materials m
            LEFT JOIN stock s ON s.material_id = m.id
            ORDER BY m.id DESC
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }
    
    public function findCategories(): array
    {
        $statement = $this->pdo->query('
            SELECT DISTINCT category
            FROM materials
            WHERE category IS NOT NULL AND category <> \'\'
            ORDER BY category ASC
        ');

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function search(
        string $search,
        string $category,
        string $stockStatus
    ): array {
        $sql = '
            SELECT
                m.id,
                m.supplier_id,
                m.name,
                m.category,
                m.unit,
                m.price,
                m.description,
                COALESCE(s.quantity, 0) AS quantity
            FROM materials m
            LEFT JOIN stock s ON s.material_id = m.id
            WHERE 1 = 1
        ';

        $params = [];

        if ($search !== '') {
            $sql .= ' AND m.name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        if ($category !== '') {
            $sql .= ' AND m.category = :category';
            $params['category'] = $category;
        }

        // Stock filter is restricted to known values.
        switch ($stockStatus) {
            case 'in_stock':
                $sql .= ' AND COALESCE(s.quantity, 0) >= 10';
                break;

            case 'low_stock':
                $sql .= ' AND COALESCE(s.quantity, 0) > 0
                        AND COALESCE(s.quantity, 0) < 10';
                break;

            case 'out_of_stock':
                $sql .= ' AND COALESCE(s.quantity, 0) = 0';
                break;
        }

        $sql .= ' ORDER BY m.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

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
