<?php

declare(strict_types=1);

class WorkProgressRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function create(
        int $workId,
        int $userId,
        string $oldStatus,
        string $newStatus,
        ?string $note
    ): int {
        $sql = "
            INSERT INTO work_progress_updates (
                work_id,
                user_id,
                old_status,
                new_status,
                note
            )
            VALUES (
                :work_id,
                :user_id,
                :old_status,
                :new_status,
                :note
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'work_id' => $workId,
            'user_id' => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByWorkId(int $workId): array
    {
        $sql = "
            SELECT
                p.id,
                p.work_id,
                p.user_id,
                u.name AS author_name,
                p.old_status,
                p.new_status,
                p.note,
                p.created_at
            FROM work_progress_updates p
            INNER JOIN users u
                ON u.id = p.user_id
            WHERE p.work_id = :work_id
            ORDER BY p.created_at DESC, p.id DESC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'work_id' => $workId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
