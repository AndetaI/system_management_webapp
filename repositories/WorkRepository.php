<?php

declare(strict_types=1);

class WorkRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO works (
                villa_id,
                title,
                category,
                status,
                description,
                planned_date,
                actual_date
            )
            VALUES (
                :villa_id,
                :title,
                :category,
                :status,
                :description,
                :planned_date,
                :actual_date
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'villa_id' => $data['villa_id'],
            'title' => $data['title'],
            'category' => $data['category'],
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
                w.id,
                w.villa_id,
                v.name AS villa_name,
                w.title,
                w.category,
                w.status,
                w.description,
                w.planned_date,
                w.actual_date,
                wa.user_id,
                wa.team_id,
                u.name AS assigned_user_name,
                t.name AS assigned_team_name
            FROM works w
            INNER JOIN villas v ON v.id = w.villa_id
            LEFT JOIN work_assignments wa ON wa.work_id = w.id
            LEFT JOIN users u ON u.id = wa.user_id
            LEFT JOIN teams t ON t.id = wa.team_id  
            ORDER BY w.id DESC
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                w.id,
                w.villa_id,
                v.name AS villa_name,
                w.title,
                w.category,
                w.status,
                w.description,
                w.planned_date,
                w.actual_date,
                wa.user_id,
                wa.team_id,
                u.name AS assigned_user_name,
                t.name AS assigned_team_name
            FROM works AS w
            INNER JOIN villas AS v ON v.id = w.villa_id
            LEFT JOIN work_assignments AS wa ON wa.work_id = w.id
            LEFT JOIN users AS u ON u.id = wa.user_id
            LEFT JOIN teams AS t ON t.id = wa.team_id
            WHERE w.id = :id
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $work = $statement->fetch();

        return $work === false ? null : $work;
    }

        
    public function search(
        string $search,
        string $status,
        string $category,
        int $villaId
    ): array {
        $sql = '
            SELECT
                w.id,
                w.villa_id,
                v.name AS villa_name,
                w.title,
                w.category,
                w.status,
                w.description,
                w.planned_date,
                w.actual_date,
                wa.user_id,
                wa.team_id,
                u.name AS assigned_user_name,
                t.name AS assigned_team_name
            FROM works w
            INNER JOIN villas v ON v.id = w.villa_id
            LEFT JOIN work_assignments wa ON wa.work_id = w.id
            LEFT JOIN users u ON u.id = wa.user_id
            LEFT JOIN teams t ON t.id = wa.team_id
            WHERE 1 = 1
        ';

        $params = [];


        if ($search !== '') {

            $workId = filter_var(
                $search,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $sql .= '
                AND (
                    w.title LIKE :title_search
                    OR w.category LIKE :category_search
                    OR v.name LIKE :villa_search
                    OR w.status LIKE :status_search
                    OR u.name LIKE :user_search
                    OR t.name LIKE :team_search
            ';

            if ($workId !== false) {
                $sql .= ' OR w.id = :work_id_search';
                $params['work_id_search'] = $workId;
            }

            $sql .= ' )';

            $searchValue = '%' . $search . '%';

            $params['title_search'] = $searchValue;
            $params['category_search'] = $searchValue;
            $params['villa_search'] = $searchValue;
            $params['status_search'] = $searchValue;
            $params['user_search'] = $searchValue;
            $params['team_search'] = $searchValue;
        }


        if ($status !== '') {
            $sql .= ' AND w.status = :status';
            $params['status'] = $status;
        }

        if ($category !== '') {
            $sql .= ' AND w.category = :category';
            $params['category'] = $category;
        }

        if ($villaId > 0) {
            $sql .= ' AND w.villa_id = :villa_id';
            $params['villa_id'] = $villaId;
        }

        $sql .= ' ORDER BY w.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findCategories(): array
    {
        $statement = $this->pdo->query('
            SELECT DISTINCT category
            FROM works
            WHERE category IS NOT NULL
            AND category <> \'\'
            ORDER BY category ASC
        ');

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }


    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE works
            SET
                villa_id = :villa_id,
                title = :title,
                category = :category,
                status = :status,
                description = :description,
                planned_date = :planned_date,
                actual_date = :actual_date
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'villa_id' => $data['villa_id'],
            'title' => $data['title'],
            'category' => $data['category'],
            'status' => $data['status'],
            'description' => $data['description'],
            'planned_date' => $data['planned_date'],
            'actual_date' => $data['actual_date'],
            'id' => $id,
        ]);
    }
public function existsByVillaId(int $villaId): bool
{
    $sql = '
        SELECT 1
        FROM works
        WHERE villa_id = :villa_id
        LIMIT 1
    ';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'villa_id' => $villaId,
    ]);

    return $statement->fetchColumn() !== false;
}
    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM works
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}