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
                v.id,
                v.typology_id,
                v.name,
                v.status,
                v.description,
                v.planned_date,
                v.actual_date,
                vt.name AS typology_name,
                vg.name AS group_name
            FROM villas v
            INNER JOIN villa_typologies vt ON vt.id = v.typology_id
            INNER JOIN villa_groups vg ON vg.id = vt.group_id
            ORDER BY v.name
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                v.id,
                v.typology_id,
                v.name,
                v.status,
                v.description,
                v.planned_date,
                v.actual_date,
                vt.name AS typology_name,
                vg.id AS group_id,
                vg.name AS group_name
            FROM villas v
            INNER JOIN villa_typologies vt ON vt.id = v.typology_id
            INNER JOIN villa_groups vg ON vg.id = vt.group_id
            WHERE v.id = :id
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $villa = $statement->fetch();

        return $villa === false ? null : $villa;
    }

    public function findByName(string $name): ?array
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
            WHERE name = :name
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'name' => $name,
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