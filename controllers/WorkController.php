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
        private TeamRepository $teamRepository,
        private AuditLogger $auditLogger,
        private Logger $logger
    ) {
    }

        
    public function index(): void
    {
        Authorization::requirePermission('works.view');

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));

        $villaId = filter_var(
            $_GET['villa_id'] ?? 0,
            FILTER_VALIDATE_INT
        );

        if ($villaId === false || $villaId < 0) {
            $villaId = 0;
        }

        // Whitelist permitted work statuses.
        $allowedStatuses = [
            '',
            'planned',
            'in_progress',
            'completed',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $categories = $this->workRepository->findCategories();

        if ($category !== '' && !in_array($category, $categories, true)) {
            $category = '';
        }

        $villas = $this->villaRepository->findAll();

        $works = $this->workRepository->search(
            $search,
            $status,
            $category,
            $villaId
        );

        // Preserve existing work-edit authorization.
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

        require __DIR__ . '/../views/pages/works/index.php';
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
                    $newWorkId = (int) $result['id'];

                    $this->auditLogger->record(
                        'WORK_CREATED',
                        'work',
                        $newWorkId
                    );

                    $this->auditLogger->record(
                        'WORK_ASSIGNMENT_CREATED',
                        'work',
                        $newWorkId,
                        [
                            'assignment_type' => $assignmentType,
                            'assigned_user_id' => $userId,
                            'assigned_team_id' => $teamId,
                        ]
                    );

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

    /*Authorization::requireAssignedWork(
        $workId,
        $this->workAssignmentRepository
    );*/
      // Full work editing is reserved for Admin.
    Authorization::requireAdmin();
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
        $canRecordMaterialUsage = Authorization::can(
            'material_usage.create'
        );

        if (
            $canRecordMaterialUsage &&
            SessionManager::role() !== 'admin'
        /*  if (in_array(
                SessionManager::role(),
                ['admin', 'manager'],
                true
            )) {
                return true;
            }*/
        
        ) {
            $userId = SessionManager::userId();

            $canRecordMaterialUsage = $userId !== null
                && $this->workAssignmentRepository
                    ->isWorkAssignedToUser(
                        (int) $work['id'],
                        $userId
                    );
        }
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

        Authorization::requireAdmin();

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


        $oldAssignment = $this->workAssignmentRepository
            ->findByWork($workId);

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
                    $newAssignment = $this->workAssignmentRepository
                        ->findByWork($workId);

                    $this->auditLogger->record(
                        'WORK_UPDATED',
                        'work',
                        $workId
                    );

                    $oldUserId = isset($oldAssignment['user_id'])
                        ? (int) $oldAssignment['user_id']
                        : null;

                    $oldTeamId = isset($oldAssignment['team_id'])
                        ? (int) $oldAssignment['team_id']
                        : null;

                    $newUserId = isset($newAssignment['user_id'])
                        ? (int) $newAssignment['user_id']
                        : null;

                    $newTeamId = isset($newAssignment['team_id'])
                        ? (int) $newAssignment['team_id']
                        : null;

                    if (
                        $oldUserId !== $newUserId ||
                        $oldTeamId !== $newTeamId
                    ) {
                        $this->auditLogger->record(
                            'WORK_ASSIGNMENT_CHANGED',
                            'work',
                            $workId,
                            [
                                'old_assigned_user_id' => $oldUserId,
                                'new_assigned_user_id' => $newUserId,
                                'old_assigned_team_id' => $oldTeamId,
                                'new_assigned_team_id' => $newTeamId,
                            ]
                        );
                    }

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
               $this->logger->exception($e);

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
        Authorization::requirePermission('works.delete');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        Csrf::requireValidToken();

        // First, perform the database operation.
        try {
            $result = $this->workService->delete($workId);
        } catch (Throwable $e) {
            $this->logger->exception($e);

            $_SESSION['flash_error'] =
                'Unable to delete this Work.';

            header('Location: /?page=works');
            exit;
        }

        if ($result['success'] !== true) {
            $_SESSION['flash_error'] =
                'Cannot delete this Work because related history or records exist.';

            header('Location: /?page=works');
            exit;
        }

        // The deletion has succeeded.
        // Audit failure must not be reported as database failure.
        try {
            $this->auditLogger->record(
                'WORK_DELETED',
                'work',
                $workId
            );
        } catch (Throwable $e) {
            $this->logger->exception($e);
        }

        $_SESSION['flash_success'] =
            'Work deleted successfully.';

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

        $this->auditLogger->record(
            'WORK_PROGRESS_RECORDED',
            'work',
            $workId,
            [
                'new_status' => $status,
            ]
        );

        $_SESSION['flash_success'] =
            'Progress update saved successfully.';

    } catch (InvalidArgumentException $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    } catch (Throwable $e) {
       $this->logger->exception($e);

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