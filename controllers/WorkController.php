<?php

declare(strict_types=1);

class WorkController
{
    public function __construct(
        private WorkService $workService,
        private WorkRepository $workRepository,
        private WorkAssignmentRepository $workAssignmentRepository,
        private VillaRepository $villaRepository,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository
    ) {
    }

    public function index(): void
    {
        Authorization::requirePermission(
            'works.view'
        );

        $works = $this->workRepository->findAll();

        $canEditWork = [];

        $isAdmin = SessionManager::role() === 'admin';
        $isStaff = SessionManager::role() === 'staff';

        foreach ($works as $work) {
            $workId = (int) $work['id'];
            $canEditWork[$workId] = $isAdmin;

            if ($isStaff) {
                $canEditWork[$workId] =
                $this->workAssignmentRepository
                ->isWorkAssignedToUser(
                         $workId,
                    (int) SessionManager::userId()
                );
    }
}

        require __DIR__
            . '/../views/pages/works/index.php';
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

        $villas = $this->villaRepository->findAll();
        $users = $this->userRepository->findAll();
        $teams = $this->teamRepository->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValidToken();


            $assignmentType = $_POST['assignment_type'] ?? '';
            $assignmentId = $_POST['assignment_id'] ?? '';

            $validId = filter_var(
                $assignmentId,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (!is_string($assignmentType) || !in_array($assignmentType, ['user', 'team'], true) || $validId === false) {
                $errors['assignment'] = 'Select a valid user or team for assignment.';
            } else {
                $userId = $assignmentType === 'user' ? $validId : null;
                $teamId = $assignmentType === 'team' ? $validId : null;
                
                $result = $this->workService->createWithAssignment(
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
    Authorization::requirePermission('works.update');

    Authorization::requireAssignedWork(
        $workId,
        $this->workAssignmentRepository
    );

    $work = $this->workRepository->findById($workId);

    if ($work === null) {
        http_response_code(404);
        exit('Work not found.');
    }

    $errors = [];
    $isAdmin = SessionManager::role() === 'admin';

    $villas = $isAdmin
        ? $this->villaRepository->findAll()
        : [];

    $users = $isAdmin
        ? $this->userRepository->findAll()
        : [];

    $teams = $isAdmin
        ? $this->teamRepository->findAll()
        : [];

    require __DIR__ . '/../views/pages/works/edit.php';
}
    public function show(int $workId): void
    {
        Authorization::requirePermission(
            'works.view'
        );

        $work = $this->workRepository
            ->findById($workId);

        if ($work === null) {
            http_response_code(404);
            echo 'Work not found.';
            return;
        }
        $canEdit = SessionManager::role() === 'admin';
            if (SessionManager::role() === 'staff') {
                $canEdit = $this->workAssignmentRepository
                    ->isWorkAssignedToUser(
                        $workId,
                        (int) SessionManager::userId()
                    );
            }
        
        $progressHistory = $this->workService->getProgressHistory($workId);

        $canUpdateProgress = Authorization::can('works.update')
            && (
                SessionManager::role() === 'admin'
                || $this->workAssignmentRepository->isWorkAssignedToUser(
                    $workId,
                    (int) SessionManager::userId()
                )
            );

        require __DIR__
            . '/../views/pages/works/show.php';
    }

    /*
     * UPDATE
     */
    public function update(int $workId): void
    {
        Authorization::requirePermission('works.update');

        // Administrative editing is restricted to Admin.
        if (SessionManager::role() !== 'admin') {
            http_response_code(403);
            exit('Access denied.');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        Csrf::requireValidToken();

        $work = $this->workRepository->findById($workId);

        if ($work === null) {
            http_response_code(404);
            exit('Work not found.');
        }

        $assignmentType = (string) ($_POST['assignment_type'] ?? '');

        $assignmentId = filter_var(
            $_POST['assignment_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $errors = [];

        if (
            !in_array($assignmentType, ['user', 'team'], true)
            || $assignmentId === false
            || $assignmentId === null
            || $assignmentId <= 0
        ) {
            $errors['assignment'] =
                'Select a valid responsible User or Team.';
        } else {
            $userId = $assignmentType === 'user'
                ? (int) $assignmentId
                : null;

            $teamId = $assignmentType === 'team'
                ? (int) $assignmentId
                : null;

            try {
                $result = $this->workService->updateWithAssignment(
                    $workId,
                    $_POST,
                    $userId,
                    $teamId
                );

                if ($result['success'] === true) {
                    $_SESSION['flash_success'] =
                        'Work details updated successfully.';

                    header(
                        'Location: /?page=works&action=show&id=' . $workId
                    );
                    exit;
                }

                $errors = $result['errors'] ?? [
                    'work' => 'Unable to update Work.',
                ];
            } catch (Throwable $e) {
                error_log('Work administrative update failed.');

                $errors['work'] =
                    'Unable to update Work. Please try again.';
            }
        }

        // Reload trusted database values for the form.
        $work = $this->workRepository->findById($workId);

        // Load the same dropdown data used by edit().
        $villas = $this->villaRepository->findAll();
        $users = $this->userRepository->findAll();
        $teams = $this->teamRepository->findAll();

        $isAdmin = true;

        require __DIR__ . '/../views/pages/works/edit.php';
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

        try {
            $result = $this->workService->delete($workId);

            if ($result['success'] === true) {
                $_SESSION['flash_success'] =
                    'Work deleted successfully.';
            } else {
                $_SESSION['flash_error'] =
                    'Cannot delete this Work because related history or records exist.';
            }
        } catch (Throwable $e) {
            error_log('Work deletion failed.');

            $_SESSION['flash_error'] =
                'Unable to delete this Work.';
        }

        header('Location: /?page=works');
        exit;
    }

    public function updateProgress(): void
{
    Authorization::requirePermission('works.update');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }

    Csrf::requireValidToken();

    $workId = filter_var(
        $_GET['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($workId === false || $workId === null || $workId <= 0) {
        http_response_code(400);
        exit('Invalid Work ID.');
    }

    // Critical: enforce authorization for this specific Work.
    Authorization::requireAssignedWork(
        $workId,
        $this->workAssignmentRepository
    );

    $status = trim((string) ($_POST['status'] ?? ''));
    $note = trim((string) ($_POST['note'] ?? ''));
    $actualDate = trim((string) ($_POST['actual_date'] ?? ''));
    // The author comes from the authenticated session,
    // never from a hidden form field.
    $authorId = (int) SessionManager::userId();

    try {
        $this->workService->recordProgress(
            $workId,
            $authorId,
            $status,
            $note,
            $actualDate
        );

        $_SESSION['flash_success'] =
            'Progress update saved successfully.';
    } catch (InvalidArgumentException $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    } catch (Throwable $e) {
        error_log('Work progress update failed: ' . $e->getMessage());

        $_SESSION['flash_error'] =
            'Unable to save the progress update.';
    }

    header(
        'Location: /?page=works&action=show&id=' . $workId
    );
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