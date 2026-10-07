<?php

declare(strict_types=1);

class VillaTypologyRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO villa_typologies (
                group_id,
                name,
                description,
                floors,
                area
            )
            VALUES (
                :group_id,
                :name,
                :description,
                :floors,
                :area
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'group_id' => $data['group_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'floors' => $data['floors'],
            'area' => $data['area'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                vt.id,
                vt.group_id,
                vt.name,
                vt.description,
                vt.floors,
                vt.area,
                vg.name AS group_name
            FROM villa_typologies vt
            INNER JOIN villa_groups vg ON vg.id = vt.group_id
            ORDER BY vg.name, vt.name
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                id,
                group_id,
                name,
                description,
                floors,
                area
            FROM villa_typologies
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $typology = $statement->fetch();

        return $typology === false ? null : $typology;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE villa_typologies
            SET
                group_id = :group_id,
                name = :name,
                description = :description,
                floors = :floors,
                area = :area
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'group_id' => $data['group_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'floors' => $data['floors'],
            'area' => $data['area'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM villa_typologies
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}