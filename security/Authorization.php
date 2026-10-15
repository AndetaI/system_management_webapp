<?php

declare(strict_types=1);

class Authorization
{
    private static ?Logger $logger = null;
    public static function setLogger(Logger $logger): void{
        self::$logger = $logger;
    }
    private static ?array $roles = null;

    /**
     * Load role/permission configuration.
     */
    private static function roles(): array
    {
        if (self::$roles === null) {
            self::$roles = require __DIR__
                . '/../config/roles.php';
        }

        return self::$roles;
    }

    /*
    It preserves the existing Admin-only policy and records the denial
    */
    public static function requireAdmin(): void
    {
        if (!SessionManager::isAuthenticated()) {
            header('Location: /?page=login');
            exit;
        }

        if (SessionManager::role() !== 'admin') {
            self::deny(
                'admin_only',
                'admin.access'
            );
        }
    }


    /**
     * Check whether a specific role
     * has a permission.
     */
    public static function roleCan(
        string $role,
        string $permission
    ): bool {
        $roles = self::roles();

        if (!isset($roles[$role])) {
            return false;
        }

        return in_array(
            $permission,
            $roles[$role],
            true
        );
    }

    /**
     * Check whether the currently authenticated
     * user has a permission.
     */
    public static function can(
        string $permission
    ): bool {
        $role = SessionManager::role();

        if ($role === null) {
            return false;
        }

        return self::roleCan(
            $role,
            $permission
        );
    }

    /**
     * Require a permission.
     *
     * Unauthenticated users are redirected
     * to the login page.
     *
     * Authenticated users without permission
     * receive HTTP 403.
     */
    public static function requirePermission(
        string $permission
    ): void {
        if (!SessionManager::isAuthenticated()) {
            header('Location: /?page=login');
            exit;
        }

        if (!self::can($permission)) {
            self::deny(
                'permission_denied',
                $permission
            );
        }
    }

    /**
     * Require authorization to update a work.
     *
     * Admin:
     * May update any work because Admin has
     * works.update permission.
     *
     * Staff:
     * May update only work assigned directly
     * to the Staff user or to their team.
     *
     * Manager:
     * Does not have works.update and is denied
     * by requirePermission().
     */
    public static function requireAssignedWork(
        int $workId,
        WorkAssignmentRepository $repository,
        string $permission = 'works.update'
    ): void {
        self::requirePermission(
            $permission
        );

        /*
         * Only Staff requires the additional
         * resource-level assignment check.
         */
        if (SessionManager::role() !== 'staff') {
            return;
        }

        $userId = SessionManager::userId();

        if (
            $userId === null ||
            !$repository->isWorkAssignedToUser(
                $workId,
                $userId
            )
        ) {
            self::deny('work_not_assigned', $permission);
        }
    }

    /**
     * Deny an authenticated request safely.
     */
    private static function deny(
    string $reason,    
    string $permission
    ): void
    {
        if(self::$logger !== null) {
            self::$logger->warning(
                'AUTHORIZATION_DENIED',
                [
                    'user_id'=>SessionManager::userId(),
                    'role'=>SessionManager::role(),
                    'reason'=>$reason,
                    'permission'=>$permission
                ]
            );
        }
        
        http_response_code(403);

        require __DIR__
            . '/../views/pages/errors/403.php';

        exit;
    }
    public static function denyAction(
        string $reason,
        string $permission
    ): void {
        self::deny($reason, $permission);
    }
}