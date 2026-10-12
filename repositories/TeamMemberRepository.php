<?php
declare(strict_types=1);

class TeamMemberRepository
{
    public function __construct(private PDO $pdo) {}

    public function addMember(int $teamId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO team_members (team_id, user_id) VALUES (:team_id, :user_id)');
        return $stmt->execute(['team_id' => $teamId, 'user_id' => $userId]);
    }

    public function findByTeam(int $teamId): array
    {
        $stmt = $this->pdo->prepare('SELECT team_id, user_id FROM team_members WHERE team_id = :id ORDER BY user_id');
        $stmt->execute(['id' => $teamId]);
        return $stmt->fetchAll();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT team_id, user_id FROM team_members WHERE user_id = :id ORDER BY team_id');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function exists(int $teamId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM team_members WHERE team_id = :team_id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['team_id' => $teamId, 'user_id' => $userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function removeMember(int $teamId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM team_members WHERE team_id = :team_id AND user_id = :user_id');
        return $stmt->execute(['team_id' => $teamId, 'user_id' => $userId]);
    }

    public function findMembersWithDetails(int $teamId): array
    {
        $stmt = $this->pdo->prepare('SELECT u.id, u.username, u.name, u.role, u.status
            FROM team_members tm INNER JOIN users u ON u.id = tm.user_id
            WHERE tm.team_id = :id ORDER BY u.name, u.username');
        $stmt->execute(['id' => $teamId]);
        return $stmt->fetchAll();
    }
}
