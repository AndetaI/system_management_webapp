<?php
declare(strict_types=1);

class TeamRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO teams (name, description) VALUES (:name, :description)');
        $stmt->execute(['name' => $data['name'], 'description' => $data['description']]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        return $this->pdo->query('SELECT id, name, description, created_at FROM teams ORDER BY id')->fetchAll();
    }

    public function findAllWithMemberCounts(): array
    {
        return $this->pdo->query('SELECT t.id, t.name, t.description, t.created_at,
            COUNT(tm.user_id) AS member_count
            FROM teams t LEFT JOIN team_members tm ON tm.team_id = t.id
            GROUP BY t.id, t.name, t.description, t.created_at ORDER BY t.id')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, description, created_at FROM teams WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, description, created_at FROM teams WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE teams SET name = :name, description = :description WHERE id = :id');
        return $stmt->execute(['id' => $id, 'name' => $data['name'], 'description' => $data['description']]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM teams WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
