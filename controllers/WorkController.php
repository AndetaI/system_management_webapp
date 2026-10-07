<?php

declare(strict_types=1);

class WorkController
{
    public function __construct(
        private WorkService $workService,
        private WorkRepository $workRepository,
        private WorkAssignmentRepository $workAssignmentRepository
    ) {
    }

    /*
     * CREATE
     *
     * Admin only.
     */
    public function create(): void
    {
        Authorization::requirePermission(
            'works.create'
        );

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValidToken();

            $userId = $this->nullablePositiveInt(
                $_POST['user_id'] ?? null
            );

            $teamId = $this->nullablePositiveInt(
                $_POST['team_id'] ?? null
            );

            $result = $this->workService
                ->createWithAssignment(
                    $_POST,
                    $userId,
                    $teamId
                );

            if ($result['success'] === true) {
                header('Location: /?page=works');
                exit;
            }

            $errors = $result['errors'] ?? [];
        }

        require __DIR__
            . '/../views/pages/works/create.php';
    }

    /*
     * EDIT FORM
     *
     * Admin may edit any work.
     * Staff may edit only work assigned directly
     * to them or to their team.
     * Manager has no update permission.
     */
    public function edit(int $workId): void
    {
        Authorization::requireAssignedWork(
            $workId,
            $this->workAssignmentRepository
        );

        $work = $this->workRepository
            ->findById($workId);

        if ($work === null) {
            http_response_code(404);
            echo 'Work not found.';
            return;
        }

        $errors = [];

        require __DIR__
            . '/../views/pages/works/edit.php';
    }

    /*
     * UPDATE
     */
    public function update(int $workId): void
    {
        Authorization::requireAssignedWork(
            $workId,
            $this->workAssignmentRepository
        );

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        Csrf::requireValidToken();

        $result = $this->workService->update(
            $workId,
            $_POST
        );

        if ($result['success'] === true) {
            header('Location: /?page=works');
            exit;
        }

        $errors = $result['errors'] ?? [];

        $work = $this->workRepository
            ->findById($workId);

        if ($work === null) {
            http_response_code(404);
            echo 'Work not found.';
            return;
        }

        /*
         * Keep submitted values after validation
         * errors so the user does not lose input.
         */
        $work = array_merge(
            $work,
            $_POST
        );

        require __DIR__
            . '/../views/pages/works/edit.php';
    }

    /*
     * DELETE
     *
     * Admin only.
     */
    public function delete(int $workId): void
    {
        Authorization::requirePermission(
            'works.delete'
        );

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }
        Csrf::requireValidToken();

        $result = $this->workService->delete(
            $workId
        );

        if ($result['success'] === true) {
            header('Location: /?page=works');
            exit;
        }

        
        header('Location: /?page=works');
        exit;
    }

    /*
     * Converts optional form IDs into ?int.
     *
     * Empty value -> null
     * Positive integer -> integer
     * Invalid/non-positive value -> null
     *
     * WorkService performs the final business
     * validation.
     */
    private function nullablePositiveInt(
        mixed $value
    ): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        $filtered = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if ($filtered === false || $filtered <= 0) {
            return null;
        }

        return $filtered;
    }
}