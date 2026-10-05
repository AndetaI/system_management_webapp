<?php

declare(strict_types=1);

class VillaService
{
    public function __construct(
        private VillaRepository $villaRepository,
        private VillaTypologyRepository $villaTypologyRepository,
        private WorkRepository $workRepository
    ) {
    }

    public function create(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Villa name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                50,
                'Villa name cannot exceed 50 characters.'
            )
            ->required(
                'typology_id',
                $data['typology_id'] ?? null,
                'Villa typology is required.'
            )
            ->positive(
                'typology_id',
                $data['typology_id'] ?? null,
                'Invalid villa typology.'
            )
            ->required(
                'status',
                $data['status'] ?? null,
                'Villa status is required.'
            )
            ->in(
                'status',
                $data['status'] ?? null,
                ['planned', 'in_progress', 'completed'],
                'Invalid villa status.'
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

        $typologyId = (int) $data['typology_id'];

        /*
         * Business rule:
         * A villa cannot reference a typology
         * that does not exist.
         */
        $typology = $this->villaTypologyRepository->findById(
            $typologyId
        );

        if ($typology === null) {
            $validator->addError(
                'typology_id',
                'The selected villa typology does not exist.'
            );

            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }
        $name = trim((string) $data['name']);

        if ($this->villaRepository->findByName($name) !== null) {
            $validator->addError(
                'name',
                'The selected villa name is already exists.'
            );

            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $villaId = $this->villaRepository->create([
            'typology_id' => $typologyId,
            'name' => $name,
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
            'id' => $villaId,
        ];
    }

    public function update(int $villaId, array $data): array
    {
        $validator = new Validator();

        if ($villaId <= 0) {
            $validator->addError(
                'villa_id',
                'Invalid villa.'
            );
        }

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Villa name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                50,
                'Villa name cannot exceed 50 characters.'
            )
            ->required(
                'typology_id',
                $data['typology_id'] ?? null,
                'Villa typology is required.'
            )
            ->positive(
                'typology_id',
                $data['typology_id'] ?? null,
                'Invalid villa typology.'
            )
            ->required(
                'status',
                $data['status'] ?? null,
                'Villa status is required.'
            )
            ->in(
                'status',
                $data['status'] ?? null,
                ['planned', 'in_progress', 'completed'],
                'Invalid villa status.'
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

        $existingVilla = $this->villaRepository->findById($villaId);

        if ($existingVilla === null) {
            $validator->addError(
                'villa_id',
                'The selected villa does not exist.'
            );
        }

        $typologyId = (int) $data['typology_id'];

        /*
         * Business rule:
         * A villa cannot reference a typology
         * that does not exist.
         */
        $typology = $this->villaTypologyRepository->findById(
            $typologyId
        );

        if ($typology === null) {
            $validator->addError(
                'typology_id',
                'The selected villa typology does not exist.'
            );

        }
         $name = trim((string) $data['name']);

        $villaWithSameName = $this->villaRepository->findByName($name);
        if ($villaWithSameName !== null && (int)$villaWithSameName['id'] !== $villaId) {
            $validator->addError(
                'name',
                'The selected villa name is already exists.'
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $this->villaRepository->update($villaId, [
            'typology_id' => $typologyId,
            'name' => $name,
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

    public function delete(int $villaId): array
{
    $validator = new Validator();

    if ($villaId <= 0) {
        $validator->addError(
            'villa_id',
            'Invalid villa.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Villa must exist.
     */
    if (
        $this->villaRepository->findById($villaId)
        === null
    ) {
        $validator->addError(
            'villa_id',
            'Villa not found.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * A villa containing works must not be deleted.
     */
    if (
        $this->workRepository->existsByVillaId(
            $villaId
        )
    ) {
        $validator->addError(
            'villa_id',
            'Cannot delete this villa because works are assigned to it.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    $this->villaRepository->delete($villaId);

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
}