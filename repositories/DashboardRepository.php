<?php

declare(strict_types=1);

class DashboardRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getSummary(): array
    {
        $sql = '
            SELECT
                (SELECT COUNT(*) FROM villas) AS total_villas,

                (SELECT COUNT(*) FROM works
                 WHERE status = \'in_progress\') AS active_works,

                (SELECT COUNT(*) FROM materials) AS total_materials,

                (SELECT COUNT(*)
                FROM materials m
                LEFT JOIN stock s ON s.material_id = m.id
                WHERE COALESCE(s.quantity, 0) > 0
                AND COALESCE(s.quantity, 0) < 10
                ) AS low_stock,

                (SELECT COUNT(*)
                FROM materials m
                LEFT JOIN stock s ON s.material_id = m.id
                WHERE COALESCE(s.quantity, 0) = 0
                ) AS out_of_stock
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function getWorksByStatus(): array
    {
        $statement = $this->pdo->query('
            SELECT status, COUNT(*) AS total
            FROM works
            GROUP BY status
            ORDER BY status
        ');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentWorks(): array
    {
        $statement = $this->pdo->query('
            SELECT
                w.id,
                w.title,
                v.name AS villa_name,
                w.category,
                w.status
            FROM works w
            INNER JOIN villas v ON v.id = w.villa_id
            ORDER BY w.id DESC
            LIMIT 5
        ');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
