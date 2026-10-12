<?php

declare(strict_types=1);

class ReportRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    // Overall material usage and estimated cost
    public function getSummary(): array
    {
        $statement = $this->pdo->query('
            SELECT
                COUNT(mu.id) AS total_usage_records,
                COALESCE(SUM(mu.quantity * m.price), 0)
                    AS total_material_cost
            FROM material_usage mu
            INNER JOIN materials m
                ON m.id = mu.material_id
        ');

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    // Total material usage and cost for each villa
    public function getMaterialCostsByVilla(): array
    {
        $statement = $this->pdo->query('
            SELECT
                v.id AS villa_id,
                v.name AS villa_name,
                COUNT(mu.id) AS usage_records,
                COALESCE(SUM(mu.quantity * m.price), 0)
                    AS total_cost
            FROM villas v
            LEFT JOIN works w
                ON w.villa_id = v.id
            LEFT JOIN material_usage mu
                ON mu.work_id = w.id
            LEFT JOIN materials m
                ON m.id = mu.material_id
            GROUP BY v.id, v.name
            ORDER BY v.name ASC
        ');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detailed material usage per villa
    public function getMaterialUsageDetails(int $villaId): array
    {
        $statement = $this->pdo->prepare('
            SELECT
                m.name AS material_name,
                m.unit,
                SUM(mu.quantity) AS total_quantity,
                m.price AS unit_price,
                SUM(mu.quantity * m.price) AS total_cost
            FROM material_usage mu
            INNER JOIN works w
                ON w.id = mu.work_id
            INNER JOIN materials m
                ON m.id = mu.material_id
            WHERE w.villa_id = :villa_id
            GROUP BY m.id, m.name, m.unit, m.price
            ORDER BY m.name ASC
        ');

        $statement->execute([
            'villa_id' => $villaId
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findVillaById(int $villaId): ?array
    {
        $statement = $this->pdo->prepare('
            SELECT id, name
            FROM villas
            WHERE id = :villa_id
            LIMIT 1
        ');

        $statement->execute([
            'villa_id' => $villaId
        ]);

        $villa = $statement->fetch(PDO::FETCH_ASSOC);

        return $villa ?: null;
    }

}
