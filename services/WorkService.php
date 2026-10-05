<?php

declare(strict_types=1);

class WorkService
{
    public function __construct(
        private PDO $pdo,
        private WorkRepository $workRepository,
        private VillaRepository $villaRepository,
        private WorkAssignmentRepository $assignmentRepository,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private MaterialUsageRepository $materialUsageRepository
    ) {
    }

    public function create(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required(
                'villa_id',
                $data['villa_id'] ?? null,
                'Villa is required.'
            )
            ->positive(
                'villa_id',
                $data['villa_id'] ?? null,
                'Invalid villa.'
            )
            ->required(
                'title',
                $data['title'] ?? null,
                'Work title is required.'
            )
            ->maxLength(
                'title',
                $data['title'] ?? null,
                150,
                'Work title cannot exceed 150 characters.'
            )
            ->required(
                'category',
                $data['category'] ?? null,
                'Work category is required.'
            )
            ->maxLength(
                'category',
                $data['category'] ?? null,
                100,
                'Work category cannot exceed 100 characters.'
            )
            ->required(
                'status',
                $data['status'] ?? null,
                'Work status is required.'
            )
            ->in(
                'status',
                $data['status'] ?? null,
                ['planned', 'in_progress', 'completed'],
                'Invalid work status.'
            )
            ->date(
                'planned_date',
                $data['planned_date'] ?? null,
                'Planned date must be a valid date.'
            )
            ->date(
                'actual_date',
                $data['actual_date'] ?? null,
                'Actual date must be a valid date.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $villaId = (int) $data['villa_id'];

        if ($this->villaRepository->findById($villaId) === null) {
            $validator->addError(
                'villa_id',
                'The selected villa does not exist.'
            );

            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $workId = $this->workRepository->create([
            'villa_id' => $villaId,
            'title' => trim((string) $data['title']),
            'category' => trim((string) $data['category']),
            'status' => $data['status'],
            'description' => isset($data['description'])
                ? trim((string) $data['description'])
                : null,
            'planned_date' => $this->nullableValue(
                $data['planned_date'] ?? null
            ),
            'actual_date' => $this->nullableValue(
                $data['actual_date'] ?? null
            ),
        ]);

        return [
            'success' => true,
            'id' => $workId,
        ];
    }

    public function assign(
        int $workId,
        ?int $userId,
        ?int $teamId
    ): array {
        $validator = new Validator();

        if ($workId <= 0) {
            $validator->addError(
                'work_id',
                'Invalid work.'
            );
        } elseif (
            $this->workRepository->findById($workId) === null
        ) {
            $validator->addError(
                'work_id',
                'The selected work does not exist.'
            );
        }

        /*
         * Exactly one assignment target is required:
         *
         * user only -> valid
         * team only -> valid
         * both      -> invalid
         * neither   -> invalid
         */
        if (
            ($userId === null && $teamId === null) ||
            ($userId !== null && $teamId !== null)
        ) {
            $validator->addError(
                'assignment',
                'A work must be assigned to either one user or one team.'
            );
        }

        if (
            $userId !== null &&
            $userId <= 0
        ) {
            $validator->addError(
                'user_id',
                'Invalid user.'
            );
        }

        if (
            $teamId !== null &&
            $teamId <= 0
        ) {
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

        if (
            $userId !== null &&
            $this->userRepository->findById($userId) === null
        ) {
            $validator->addError(
                'user_id',
                'The selected user does not exist.'
            );
        }

        if (
            $teamId !== null &&
            $this->teamRepository->findById($teamId) === null
        ) {
            $validator->addError(
                'team_id',
                'The selected team does not exist.'
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        if (
            $this->assignmentRepository->findByWork($workId)
            !== null
        ) {
            $validator->addError(
                'assignment',
                'This work already has an assignment.'
            );

            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $assignmentId = $this->assignmentRepository->create(
            $workId,
            $userId,
            $teamId
        );

        return [
            'success' => true,
            'id' => $assignmentId,
        ];
    }
    
    public function createWithAssignment(
        array $data,
        ?int $userId,
        ?int $teamId
        ): array
    {
        $validator = new Validator();

        if (
            ($userId === null && $teamId === null) ||
            ($userId !== null && $teamId !== null)
        ) {
            $validator->addError(
                'assignment',
                'A work must be assigned to either one user or one team.'
            );
        }

        if ($userId !== null && $userId <= 0) {
            $validator->addError(
                'user_id',
                'Invalid user.'
            );
        }
        if ($teamId !== null && $teamId <= 0) {
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
        if (
            $userId !== null &&
            $this->userRepository->findById($userId) === null
        ) {
            $validator->addError(
                'user_id',
                'The selected user does not exist.'
            );
        }
        if (
            $teamId !== null &&
            $this->teamRepository->findById($teamId) === null
        ) {
            $validator->addError(
                'team_id',
                'The selected team does not exist.'
            );
        }
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        try {
            $this->pdo->beginTransaction();

            $workResult = $this->create($data);

            if ($workResult['success'] === false) {
                $this->pdo->rollBack();
                return $workResult;
            }

            $workId = (int) $workResult['id'];

            $assignmentId = $this->assignmentRepository->create(
                $workId,
                $userId,
                $teamId
            );


            $this->pdo->commit();

            return [
                'success' => true,
                'id' => $workId,
                'assignment_id' => $assignmentId,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

    }
    public function update(int $workId, array $data): array
    {
        $validator = new Validator();

        if ($workId <= 0) {
            $validator->addError(
                'work_id',
                'Invalid work.'
            );
        }

        $validator
            ->required(
                'villa_id',
                $data['villa_id'] ?? null,
                'Villa is required.'
            )
            ->positive(
                'villa_id',
                $data['villa_id'] ?? null,
                'Invalid villa.'
            )
            ->required(
                'title',
                $data['title'] ?? null,
                'Work title is required.'
            )
            ->maxLength(
                'title',
                $data['title'] ?? null,
                150,
                'Work title cannot exceed 150 characters.'
            )
            ->required(
                'category',
                $data['category'] ?? null,
                'Work category is required.'
            )
            ->maxLength(
                'category',
                $data['category'] ?? null,
                100,
                'Work category cannot exceed 100 characters.'
            )
            ->required(
                'status',
                $data['status'] ?? null,
                'Work status is required.'
            )
            ->in(
                'status',
                $data['status'] ?? null,
                ['planned', 'in_progress', 'completed'],
                'Invalid work status.'
            )
            ->date(
                'planned_date',
                $data['planned_date'] ?? null,
                'Planned date must be a valid date.'
            )
            ->date(
                'actual_date',
                $data['actual_date'] ?? null,
                'Actual date must be a valid date.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        if ($this->workRepository->findById($workId) === null) {
            $validator->addError(
                'work_id',
                'The selected work does not exist.'
            );
        }

        $villaId = (int) $data['villa_id'];
        if ($this->villaRepository->findById($villaId) === null) {
            $validator->addError(
                'villa_id',
                'The selected villa does not exist.'
            );
        }
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $this->workRepository->update($workId, [
            'villa_id' => $villaId,
            'title' => trim((string) $data['title']),
            'category' => trim((string) $data['category']),
            'status' => $data['status'],
            'description' => $this->nullableValue(
                $data['description'] ?? null
            ),
            'planned_date' => $this->nullableValue(
                $data['planned_date'] ?? null
            ),
            'actual_date' => $this->nullableValue(
                $data['actual_date'] ?? null
            ),
        ]);

        return [
            'success' => true,
        ];
    }

    public function changeAssignment(
        int $workId,
        ?int $userId,
        ?int $teamId
    ): array {
        $validator = new Validator();

        if ($workId <= 0) {
            $validator->addError(
                'work_id',
                'Invalid work.'
            );
        }

        /*
         * Exactly one assignment target is required:
         *
         * user only -> valid
         * team only -> valid
         * both      -> invalid
         * neither   -> invalid
         */
        if (
            ($userId === null && $teamId === null) ||
            ($userId !== null && $teamId !== null)
        ) {
            $validator->addError(
                'assignment',
                'A work must be assigned to either one user or one team.'
            );
        }

        if (
            $userId !== null &&
            $userId <= 0
        ) {
            $validator->addError(
                'user_id',
                'Invalid user.'
            );
        }

        if (
            $teamId !== null &&
            $teamId <= 0
        ) {
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

        if (
            $this->workRepository->findById($workId) === null
        ) {
            $validator->addError(
                'work_id',
                'The selected work does not exist.'
            );
        }

        if (
            $userId !== null &&
            $this->userRepository->findById($userId) === null
        ) {
            $validator->addError(
                'user_id',
                'The selected user does not exist.'
            );
        }

        if (
            $teamId !== null &&
            $this->teamRepository->findById($teamId) === null
        ) {
            $validator->addError(
                'team_id',
                'The selected team does not exist.'
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }
        $existingAssignment = $this->assignmentRepository->findByWork($workId);

        if ($existingAssignment === null) {
            return [
                'success' => false,
                'errors' => [
                    'assignment' => 'This work does not have an existing assignment.'
                ],
            ];
        }

        $this->assignmentRepository->update(
            $workId,
            $userId,
            $teamId
        );

        return [
            'success' => true,
        ];
    }

    private function nullableValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }
    public function delete(int $workId): array
{
    $validator = new Validator();

    if ($workId <= 0) {
        $validator->addError(
            'work_id',
            'Invalid work.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Work must exist.
     */
    if (
        $this->workRepository->findById($workId)
        === null
    ) {
        $validator->addError(
            'work_id',
            'Work not found.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Preserve material usage history.
     */
    if (
        $this->materialUsageRepository
            ->existsByWorkId($workId)
    ) {
        $validator->addError(
            'work_id',
            'Cannot delete this work because material usage history exists.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Delete the work.
     *
     * Any work_assignment belonging to this work
     * is removed automatically by ON DELETE CASCADE.
     */
    $this->workRepository->delete($workId);

    return [
        'success' => true,
    ];
}
}