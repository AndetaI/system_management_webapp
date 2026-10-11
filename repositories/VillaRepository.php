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

        
    public function search(
        string $search,
        int $groupId,
        string $typologyName,
        string $status
    ): array {
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
            INNER JOIN villa_typologies vt
                ON vt.id = v.typology_id
            INNER JOIN villa_groups vg
                ON vg.id = vt.group_id
            WHERE 1 = 1
        ';

        $params = [];

        if ($search !== '') {
            $sql .= '
                AND (
                    v.name LIKE :villa_search
                    OR vg.name LIKE :group_search
                    OR vt.name LIKE :typology_search
                    OR v.status LIKE :status_search
                )
            ';

            $searchValue = '%' . $search . '%';

            $params['villa_search'] = $searchValue;
            $params['group_search'] = $searchValue;
            $params['typology_search'] = $searchValue;
            $params['status_search'] = $searchValue;
        }

        if ($groupId > 0) {
            $sql .= ' AND vg.id = :group_id';
            $params['group_id'] = $groupId;
        }

        if ($typologyName !== '') {
            $sql .= ' AND vt.name = :typology_name';
            $params['typology_name'] = $typologyName;
        }

        if ($status !== '') {
            $sql .= ' AND v.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY v.name ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findFilterGroups(): array
    {
        $statement = $this->pdo->query('
            SELECT id, name
            FROM villa_groups
            ORDER BY name ASC
        ');

        return $statement->fetchAll();
    }

    public function findFilterStatuses(): array
    {
        $statement = $this->pdo->query('
            SELECT DISTINCT status
            FROM villas
            WHERE status IS NOT NULL
            AND status <> \'\'
            ORDER BY status ASC
        ');

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function findFilterTypologies(): array
    {
        $statement = $this->pdo->query('
            SELECT DISTINCT name
            FROM villa_typologies
            ORDER BY name ASC
        ');

        return $statement->fetchAll(PDO::FETCH_COLUMN);
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