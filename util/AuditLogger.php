<?php

declare(strict_types=1);

final class AuditLogger
{
    public function __construct(
        private Logger $logger
    ) {
    }

    public function record(
        string $event,
        string $entityType,
        int $entityId,
        array $details = []
    ): void {
        $context = [
            'actor_user_id' => SessionManager::userId(),
            'actor_role' => SessionManager::role(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ];

        // Accept only explicitly supplied, non-sensitive metadata.
        $allowedKeys = [
            'old_role',
            'new_role',
            'old_status',
            'new_status',
            'work_id',
            'progress_id',
            'material_id',
            'quantity',
            'assignment_type',
            'assigned_user_id',
            'assigned_team_id',
            'old_assigned_user_id',
            'new_assigned_user_id',
            'old_assigned_team_id',
            'new_assigned_team_id',

            'movement_id',
            'remaining_stock',
        ];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $details)) {
                $context[$key] = $details[$key];
            }
        }

        $this->logger->info(
            'AUDIT_' . $event,
            $context
        );
    }
}
