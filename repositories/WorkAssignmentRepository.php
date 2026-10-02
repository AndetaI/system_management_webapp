<?php

declare(strict_types=1);

class WorkAssignmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(
        int $workId,
        ?int $userId,
        ?int $teamId
    ): int {
        $sql = '
            INSERT INTO work_assignments (
                work_id,
                user_id,
                team_id
            )
            VALUES (
                :work_id,
                :user_id,
                :team_id
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'work_id' => $workId,
            'user_id' => $userId,
            'team_id' => $teamId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByWork(int $workId): ?array
    {
        $sql = '
            SELECT
                id,
                work_id,
                user_id,
                team_id
            FROM work_assignments
            WHERE work_id = :work_id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'work_id' => $workId,
        ]);

        $assignment = $statement->fetch();

        return $assignment === false ? null : $assignment;
    }

    public function update(
        int $workId,
        ?int $userId,
        ?int $teamId
    ): bool {
        $sql = '
            UPDATE work_assignments
            SET
                user_id = :user_id,
                team_id = :team_id
            WHERE work_id = :work_id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'user_id' => $userId,
            'team_id' => $teamId,
            'work_id' => $workId,
        ]);
    }

    public function deleteByWork(int $workId): bool
    {
        $sql = '
            DELETE FROM work_assignments
            WHERE work_id = :work_id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'work_id' => $workId,
        ]);
    }
}