<?php
declare(strict_types=1);

class TeamService
{
    public function __construct(
        private TeamRepository $teamRepository,
        private UserRepository $userRepository,
        private TeamMemberRepository $teamMemberRepository,
        private WorkAssignmentRepository $workAssignmentRepository
    ) {}

    public function create(array $data): array
    {
        $errors = $this->validateTeam($data);
        $name = trim((string)($data['name'] ?? ''));
        if ($name !== '' && $this->teamRepository->findByName($name) !== null) {
            $errors['name'][] = 'A team with this name already exists.';
        }
        if ($errors) return ['success' => false, 'errors' => $errors];
        $id = $this->teamRepository->create([
            'name' => $name, 'description' => $this->nullableString($data['description'] ?? null)
        ]);
        return ['success' => true, 'id' => $id];
    }

    public function update(int $teamId, array $data): array
    {
        if ($teamId <= 0 || $this->teamRepository->findById($teamId) === null) {
            return $this->fail('team_id', 'Team not found.');
        }
        $errors = $this->validateTeam($data);
        $name = trim((string)($data['name'] ?? ''));
        $sameName = $name !== '' ? $this->teamRepository->findByName($name) : null;
        if ($sameName !== null && (int)$sameName['id'] !== $teamId) {
            $errors['name'][] = 'A team with this name already exists.';
        }
        if ($errors) return ['success' => false, 'errors' => $errors];
        $this->teamRepository->update($teamId, [
            'name' => $name, 'description' => $this->nullableString($data['description'] ?? null)
        ]);
        return ['success' => true];
    }

    public function delete(int $teamId): array
    {
        if ($teamId <= 0 || $this->teamRepository->findById($teamId) === null) {
            return $this->fail('team_id', 'Team not found.');
        }
        if ($this->workAssignmentRepository->existsByTeamId($teamId)) {
            return $this->fail('team_id', 'Cannot delete a team assigned to a work.');
        }
        // Database should have ON DELETE CASCADE for team_members.team_id.
        $this->teamRepository->delete($teamId);
        return ['success' => true];
    }

    public function addMember(int $teamId, int $userId): array
    {
        if ($teamId <= 0 || $this->teamRepository->findById($teamId) === null) {
            return $this->fail('team_id', 'Team not found.');
        }
        $user = $userId > 0 ? $this->userRepository->findById($userId) : null;
        if ($user === null) return $this->fail('user_id', 'User not found.');
        if ($user['role'] !== 'staff' || $user['status'] !== 'active') {
            return $this->fail('user_id', 'Only active Staff users can join teams.');
        }
        if ($this->teamMemberRepository->exists($teamId, $userId)) {
            return $this->fail('user_id', 'User is already a member of this team.');
        }
        $this->teamMemberRepository->addMember($teamId, $userId);
        return ['success' => true];
    }

    public function removeMember(int $teamId, int $userId): array
    {
        if ($teamId <= 0 || $this->teamRepository->findById($teamId) === null) {
            return $this->fail('team_id', 'Team not found.');
        }
        if ($userId <= 0 || !$this->teamMemberRepository->exists($teamId, $userId)) {
            return $this->fail('user_id', 'User is not a member of this team.');
        }
        $this->teamMemberRepository->removeMember($teamId, $userId);
        return ['success' => true];
    }

    private function validateTeam(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $errors = [];
        if ($name === '') $errors['name'][] = 'Team name is required.';
        if (mb_strlen($name) > 100) $errors['name'][] = 'Team name cannot exceed 100 characters.';
        if (mb_strlen($description) > 1000) $errors['description'][] = 'Description cannot exceed 1000 characters.';
        return $errors;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function fail(string $field, string $message): array
    {
        return ['success' => false, 'errors' => [$field => [$message]]];
    }
}
