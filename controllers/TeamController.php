<?php
declare(strict_types=1);

class TeamController
{
    public function __construct(
        private TeamRepository $teamRepository,
        private TeamService $teamService,
        private TeamMemberRepository $teamMemberRepository,
        private UserRepository $userRepository,
        private AuditLogger $auditLogger
    ) {}

    public function index(): void
    {
        Authorization::requirePermission('teams.view');
        $teams = $this->teamRepository->findAllWithMemberCounts();
        require __DIR__ . '/../views/pages/teams/index.php';
    }

    public function create(): void
    {
        Authorization::requirePermission('teams.create');
        $errors = []; $old = [];
        require __DIR__ . '/../views/pages/teams/create.php';
    }

    public function store(): void
    {
        Authorization::requirePermission('teams.create');
        $this->requirePost();
        $result = $this->teamService->create($_POST);
        if (!$result['success']) {
            $errors = $result['errors']; $old = $_POST;
            require __DIR__ . '/../views/pages/teams/create.php';
            return;
        }
        $this->auditLogger->record(
            'TEAM_CREATED',
            'team',
            (int) $result['id']
        );
        $this->redirect('show', (int)$result['id']);
    }

    public function show(int $id): void
    {
        Authorization::requirePermission('teams.view');
        $team = $this->findTeamOr404($id);
        $members = $this->teamMemberRepository->findMembersWithDetails($id);
        $users = $this->userRepository->findAll();
        $memberIds = array_map('intval', array_column($members, 'id'));
        $eligibleUsers = array_values(array_filter($users, static function (array $user) use ($memberIds): bool {
            return $user['role'] === 'staff'
                && $user['status'] === 'active'
                && !in_array((int)$user['id'], $memberIds, true);
        }));
        $errors = [];
        require __DIR__ . '/../views/pages/teams/show.php';
    }

    public function edit(int $id): void
    {
        Authorization::requirePermission('teams.update');
        $team = $this->findTeamOr404($id);
        $errors = []; $old = $team;
        require __DIR__ . '/../views/pages/teams/edit.php';
    }

    public function update(int $id): void
    {
        Authorization::requirePermission('teams.update');
        $this->requirePost();
        $team = $this->findTeamOr404($id);
        $result = $this->teamService->update($id, $_POST);
        if (!$result['success']) {
            $errors = $result['errors']; $old = $_POST;
            require __DIR__ . '/../views/pages/teams/edit.php';
            return;
        }
        $this->auditLogger->record(
            'TEAM_UPDATED',
            'team',
            $id
        );
        $this->redirect('show', $id);
    }

    public function delete(int $id): void
    {
        Authorization::requirePermission('teams.delete');
        $this->requirePost();
        $result = $this->teamService->delete($id);
        if (!$result['success']) {
            $this->renderShowError($id, $result['errors']);
            return;
        }
        $this->auditLogger->record(
            'TEAM_DELETED',
            'team',
            $id
        );
        header('Location: /?page=teams', true, 303);
        exit;
    }

    public function addMember(int $id): void
    {
        Authorization::requirePermission('teams.update');
        $this->requirePost();
        $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
        $result = $this->teamService->addMember($id, $userId === false ? 0 : (int)$userId);
        if (!$result['success']) {
            $this->renderShowError($id, $result['errors']);
            return;
        }

        $this->auditLogger->record(
            'TEAM_MEMBER_ADDED',
            'team',
            $id,
            [
                'assigned_user_id' => (int) $userId,
            ]
        );
        $this->redirect('show', $id);
    }

    public function removeMember(int $id): void
    {
        Authorization::requirePermission('teams.update');
        $this->requirePost();
        $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
        $result = $this->teamService->removeMember($id, $userId === false ? 0 : (int)$userId);
        if (!$result['success']) {
            $this->renderShowError($id, $result['errors']);
            return;
        }

        $this->auditLogger->record(
            'TEAM_MEMBER_REMOVED',
            'team',
            $id,
            [
                'assigned_user_id' => (int) $userId,
            ]
        );
        $this->redirect('show', $id);
    }

    private function renderShowError(int $id, array $errors): void
    {
        $team = $this->findTeamOr404($id);
        $members = $this->teamMemberRepository->findMembersWithDetails($id);
        $users = $this->userRepository->findAll();
        $memberIds = array_map('intval', array_column($members, 'id'));
        $eligibleUsers = array_values(array_filter($users, static function (array $user) use ($memberIds): bool {
            return $user['role'] === 'staff'
                && $user['status'] === 'active'
                && !in_array((int)$user['id'], $memberIds, true);
        }));
        http_response_code(422);
        require __DIR__ . '/../views/pages/teams/show.php';
    }

    private function findTeamOr404(int $id): array
    {
        $team = $id > 0 ? $this->teamRepository->findById($id) : null;
        if ($team === null) {
            http_response_code(404);
            exit('Team not found.');
        }
        return $team;
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        Csrf::requireValidToken();
    }

    private function redirect(string $action, int $id): void
    {
        header('Location: /?page=teams&action=' . $action . '&id=' . $id, true, 303);
        exit;
    }
}
