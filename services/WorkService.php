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
        private MaterialUsageRepository $materialUsageRepository,
        private WorkProgressRepository $workProgressRepository
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
            $userId !== null){
            $user = $this->userRepository->findById($userId);

            if($user === null) {
                $validator->addError(
                    'user_id',
                    'The selected user does not exist.'
                );
            } elseif ($user['status'] !== 'active') {
                $validator->addError(
                    'user_id',
                    'Cannot assign work to an inactive user.'
                );
            }
        
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
            $userId !== null){
            $user = $this->userRepository->findById($userId);
            if($user === null) {
                $validator->addError(
                    'user_id',
                    'The selected user does not exist.'
                );
            } elseif (($user['status'] ?? null) !== 'active') {
                $validator->addError(
                    'user_id',
                    'Cannot assign work to an inactive user.'
                );
            }
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
    public function updateWithAssignment(
        int $workId,
        array $data,
        ?int $userId,
        ?int $teamId
    ): array {
        if ($this->pdo->inTransaction()) {
            throw new RuntimeException(
                'An unexpected database transaction is already active.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            // Lock the Work to prevent concurrent status overwrites.
            $stmt = $this->pdo->prepare(
                'SELECT status FROM works WHERE id = :id FOR UPDATE'
            );

            $stmt->execute(['id' => $workId]);

            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$current) {
                $this->pdo->rollBack();

                return [
                    'success' => false,
                    'errors' => ['work' => 'Work not found.'],
                ];
            }

            // Never trust status submitted through the Admin edit form.
            // Status changes must use recordProgress().
            $data['status'] = $current['status'];

            $assignmentResult = $this->changeAssignment(
                $workId,
                $userId,
                $teamId
            );

            if ($assignmentResult['success'] !== true) {
                $this->pdo->rollBack();
                return $assignmentResult;
            }

            $updateResult = $this->update($workId, $data);

            if ($updateResult['success'] !== true) {
                $this->pdo->rollBack();
                return $updateResult;
            }

            $this->pdo->commit();

            return ['success' => true];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
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

public function recordProgress(
    int $workId,
    int $authorId,
    string $newStatus,
    string $note,
    ?string $actualDate
): void {
    if ($workId <= 0 || $authorId <= 0) {
        throw new InvalidArgumentException('Invalid Work or User.');
    }

    if (!in_array(
        $newStatus,
        ['planned', 'in_progress', 'completed'],
        true
    )) {
        throw new InvalidArgumentException('Invalid Work status.');
    }

    $note = trim($note);

    if ($note === '' || mb_strlen($note) > 2000) {
        throw new InvalidArgumentException(
            'Progress note is required (maximum 2000 characters).'
        );
    }

    $actualDate = trim((string) $actualDate);
    $actualDate = $actualDate === '' ? null : $actualDate;

    if ($actualDate !== null) {
        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $actualDate
        );

        if (
            $date === false
            || $date->format('Y-m-d') !== $actualDate
        ) {
            throw new InvalidArgumentException(
                'Invalid actual completion date.'
            );
        }
    }

    if ($newStatus === 'completed' && $actualDate === null) {
        throw new InvalidArgumentException(
            'Actual completion date is required for completed Works.'
        );
    }

    if ($this->pdo->inTransaction()) {
        throw new RuntimeException(
            'Unexpected active database transaction.'
        );
    }

    $this->pdo->beginTransaction();

    try {
        $stmt = $this->pdo->prepare(
            'SELECT status, actual_date
             FROM works
             WHERE id = :id
             FOR UPDATE'
        );

        $stmt->execute(['id' => $workId]);

        $work = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$work) {
            throw new InvalidArgumentException('Work not found.');
        }

        // Recheck the current authenticated user's permission.
        if (
            SessionManager::userId() !== $authorId
            || !Authorization::can('works.update')
        ) {
            throw new RuntimeException('Unauthorized progress update.');
        }

        if (
            SessionManager::role() === 'staff'
            && !$this->assignmentRepository->isWorkAssignedToUser(
                $workId,
                $authorId
            )
        ) {
            throw new RuntimeException('Unauthorized Work assignment.');
        }

        if (!in_array(
            SessionManager::role(),
            ['admin', 'staff'],
            true
        )) {
            throw new RuntimeException('Unauthorized role.');
        }

        $oldStatus = (string) $work['status'];

        $update = $this->pdo->prepare(
            'UPDATE works
             SET status = :status,
                 actual_date = :actual_date
             WHERE id = :id'
        );

        $update->execute([
            'status' => $newStatus,
            'actual_date' => $actualDate,
            'id' => $workId,
        ]);

        $this->workProgressRepository->create(
            $workId,
            $authorId,
            $oldStatus,
            $newStatus,
            $note
        );

        $this->pdo->commit();
    } catch (Throwable $e) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        throw $e;
    }
}
public function getProgressHistory(int $workId): array
{
    if ($workId <= 0) {
        throw new InvalidArgumentException('Invalid Work ID.');
    }

    return $this->workProgressRepository->findByWorkId($workId);
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
    $history = $this->workProgressRepository->findByWorkId($workId);

    if (!empty($history)) {
        return [
            'success' => false,
            'errors' => [
                'work' => 'Cannot delete this Work because progress history exists.'
            ],
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