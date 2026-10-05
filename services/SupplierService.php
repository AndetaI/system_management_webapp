<?php

declare(strict_types=1);

class SupplierService
{
    public function __construct(
        private SupplierRepository $supplierRepository,
        private MaterialRepository $materialRepository
    ) {
    }

    public function create(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Supplier name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                150,
                'Supplier name cannot exceed 150 characters.'
            )
            ->email(
                'email',
                $data['email'] ?? null,
                'Please enter a valid email address.'
            )
            ->maxLength(
                'email',
                $data['email'] ?? null,
                255,
                'Email cannot exceed 255 characters.'
            )
            ->maxLength(
                'phone',
                $data['phone'] ?? null,
                30,
                'Phone number cannot exceed 30 characters.'
            )
            ->maxLength(
                'address',
                $data['address'] ?? null,
                255,
                'Address cannot exceed 255 characters.'
            )
            ->maxLength(
                'contact_person',
                $data['contact_person'] ?? null,
                100,
                'Contact person cannot exceed 100 characters.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $supplierId = $this->supplierRepository->create([
            'name' => trim((string) $data['name']),
            'email' => $this->nullableString(
                $data['email'] ?? null
            ),
            'phone' => $this->nullableString(
                $data['phone'] ?? null
            ),
            'address' => $this->nullableString(
                $data['address'] ?? null
            ),
            'contact_person' => $this->nullableString(
                $data['contact_person'] ?? null
            ),
        ]);

        return [
            'success' => true,
            'id' => $supplierId,
        ];
    }

    public function update(int $supplierId, array $data): array
    {
        $validator = new Validator();

        if($supplierId <= 0) {
            $validator->addError(
                'supplier_id',
                'Invalid supplier.'
            );
        }

        $validator
            ->required(
                'name',
                $data['name'] ?? null,
                'Supplier name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                150,
                'Supplier name cannot exceed 150 characters.'
            )
            ->email(
                'email',
                $data['email'] ?? null,
                'Please enter a valid email address.'
            )
            ->maxLength(
                'email',
                $data['email'] ?? null,
                255,
                'Email cannot exceed 255 characters.'
            )
            ->maxLength(
                'phone',
                $data['phone'] ?? null,
                30,
                'Phone number cannot exceed 30 characters.'
            )
            ->maxLength(
                'address',
                $data['address'] ?? null,
                255,
                'Address cannot exceed 255 characters.'
            )
            ->maxLength(
                'contact_person',
                $data['contact_person'] ?? null,
                100,
                'Contact person cannot exceed 100 characters.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        if ($this->supplierRepository->findById($supplierId) === null) {
            return [
                'success' => false,
                'errors' => [
                    'supplier_id' => ['Supplier not found.'],
                ],
            ];
        }

        $this->supplierRepository->update($supplierId, [
            'name' => trim((string) $data['name']),
            'email' => $this->nullableString(
                $data['email'] ?? null
            ),
            'phone' => $this->nullableString(
                $data['phone'] ?? null
            ),
            'address' => $this->nullableString(
                $data['address'] ?? null
            ),
            'contact_person' => $this->nullableString(
                $data['contact_person'] ?? null
            ),
        ]);

        return [
            'success' => true,
        ];
    }
     

    public function delete(int $supplierId): array
{
    $validator = new Validator();

    if ($supplierId <= 0) {
        $validator->addError(
            'supplier_id',
            'Invalid supplier.'
        );
    }

    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Supplier must exist.
     */
    if (
        $this->supplierRepository->findById(
            $supplierId
        ) === null
    ) {
        $validator->addError(
            'supplier_id',
            'Supplier not found.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * Do not delete a supplier while materials
     * still reference it.
     */
    if (
        $this->materialRepository->existsBySupplierId(
            $supplierId
        )
    ) {
        $validator->addError(
            'supplier_id',
            'Cannot delete this supplier because materials are still assigned to it.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    $this->supplierRepository->delete(
        $supplierId
    );

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