<?php

declare(strict_types=1);

class TeamMemberRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function addMember(int $teamId, int $userId): bool
    {
        $sql = '
            INSERT INTO team_members (
                team_id,
                user_id
            )
            VALUES (
                :team_id,
                :user_id
            )
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'team_id' => $teamId,
            'user_id' => $userId,
        ]);
    }

    public function findByTeam(int $teamId): array
    {
        $sql = '
            SELECT
                team_id,
                user_id
            FROM team_members
            WHERE team_id = :team_id
            ORDER BY user_id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'team_id' => $teamId,
        ]);

        return $statement->fetchAll();
    }

    public function findByUser(int $userId): array
    {
        $sql = '
            SELECT
                team_id,
                user_id
            FROM team_members
            WHERE user_id = :user_id
            ORDER BY team_id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function removeMember(int $teamId, int $userId): bool
    {
        $sql = '
            DELETE FROM team_members
            WHERE team_id = :team_id
              AND user_id = :user_id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'team_id' => $teamId,
            'user_id' => $userId,
        ]);
    }
}