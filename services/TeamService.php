<?php

declare(strict_types=1);

class TeamService
{
    public function __construct(
        private TeamRepository $teamRepository,
        private UserRepository $userRepository,
        private TeamMemberRepository $teamMemberRepository,
        private WorkAssignmentRepository $workAssignmentRepository
    ) {
    }

    public function create(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Team name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                100,
                'Team name cannot exceed 100 characters.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $name = trim((string) $data['name']);

        if ($this->teamRepository->findByName($name) !== null) {
            $validator->addError(
                'name',
                'A team with this name already exists.'
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $teamId = $this->teamRepository->create([
            'name' => $name,
            'description' => $this->nullableString(
                $data['description'] ?? null
            ),
        ]);

        return [
            'success' => true,
            'id' => $teamId,
        ];
    }

public function addMember(
    int $teamId,
    int $userId
): array {
    $validator = new Validator();

    // 1. Validate IDs
    if ($teamId <= 0) {
        $validator->addError(
            'team_id',
            'Invalid team.'
        );
    }

    if ($userId <= 0) {
        $validator->addError(
            'user_id',
            'Invalid user.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    // 2. Check that team exists
    if ($this->teamRepository->findById($teamId) === null) {
        $validator->addError(
            'team_id',
            'The selected team does not exist.'
        );
    }

    // 3. Check that user exists
    if ($this->userRepository->findById($userId) === null) {
        $validator->addError(
            'user_id',
            'The selected user does not exist.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    // 4. Check duplicate membership
    if (
        $this->teamMemberRepository->exists(
            $teamId,
            $userId
        )
    ) {
        $validator->addError(
            'user_id',
            'User is already a member of this team.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    // 5. Add user to team
    $this->teamMemberRepository->addMember(
        $teamId,
        $userId
    );

    return [
        'success' => true,
    ];
}
    public function update(int $teamId, array $data): array
    {
        $validator = new Validator();

        if ($teamId <= 0) {
            $validator->addError(
                'team_id',
                'Invalid team.'
            );
        }

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Team name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                100,
                'Team name cannot exceed 100 characters.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $existingTeam = $this->teamRepository->findById($teamId);

        if ($existingTeam === null) {
            return [
                'success' => false,
                'errors' => [
                    'team_id' => ['The selected team does not exist.'],
                ],
            ];
        }

        $name = trim((string) $data['name']);
        $teamWithSameName = $this->teamRepository->findByName($name);

        if ($teamWithSameName !== null && (int)$teamWithSameName['id'] !== $teamId) {
            return [
                'success' => false,
                'errors' => [
                    'name' => ['A team with this name already exists.'],
                ],
            ];
           
        }

        $this->teamRepository->update($teamId, [
            'name' => $name,
            'description' => $this->nullableString(
                $data['description'] ?? null
            ),
        ]);

        return [
            'success' => true,
        ];
    }
public function delete(int $teamId): array
{
    $validator = new Validator();

    if ($teamId <= 0) {
        $validator->addError(
            'team_id',
            'Invalid team.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Team must exist.
     */
    if (
        $this->teamRepository->findById($teamId)
        === null
    ) {
        $validator->addError(
            'team_id',
            'Team not found.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * A team assigned to a work must not
     * be deleted.
     */
    if (
        $this->workAssignmentRepository
            ->existsByTeamId($teamId)
    ) {
        $validator->addError(
            'team_id',
            'Cannot delete this team because it is assigned to a work.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * team_members rows are automatically removed
     * through ON DELETE CASCADE.
     */
    $this->teamRepository->delete($teamId);

    return [
        'success' => true,
    ];
}
    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}