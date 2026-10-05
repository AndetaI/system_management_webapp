<?php

declare(strict_types=1);

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private WorkAssignmentRepository $workAssignmentRepository
    
        ) {
    }

    public function create(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required(
                'username',
                $data['username'] ?? null,
                'Username is required.'
            )
            ->minLength(
                'username',
                $data['username'] ?? null,
                3,
                'Username must contain at least 3 characters.'
            )
            ->maxLength(
                'username',
                $data['username'] ?? null,
                50,
                'Username cannot exceed 50 characters.'
            )
            ->required(
                'name',
                $data['name'] ?? null,
                'Name is required.'
            )
            ->maxLength(
                'name',
                $data['name'] ?? null,
                100,
                'Name cannot exceed 100 characters.'
            )
            ->required(
                'email',
                $data['email'] ?? null,
                'Email is required.'
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
            ->required(
                'password',
                $data['password'] ?? null,
                'Password is required.'
            )
            ->minLength(
                'password',
                $data['password'] ?? null,
                8,
                'Password must contain at least 8 characters.'
            )
            ->required(
                'role',
                $data['role'] ?? null,
                'Role is required.'
            )
            ->in(
                'role',
                $data['role'] ?? null,
                ['admin', 'manager', 'staff'],
                'Invalid user role.'
            )
            ->required(
                'status',
                $data['status'] ?? null,
                'Status is required.'
            )
            ->in(
                'status',
                $data['status'] ?? null,
                ['active', 'inactive'],
                'Invalid user status.'
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $username = trim((string) $data['username']);
        $email = trim((string) $data['email']);

        if ($this->userRepository->findByUsername($username) !== null) {
            $validator->addError(
                'username',
                'The username is already in use.'
            );
        }

        if ($this->userRepository->findByEmail($email) !== null) {
            $validator->addError(
                'email',
                'The email is already in use.'      
            );
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $passwordHash = password_hash(
            (string) $data['password'],
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Password hashing failed.'
            );
        }

        $userId = $this->userRepository->create([
            'username' => $username,
            'name' => trim((string) $data['name']),
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        return [
            'success' => true,
            'id' => $userId,
        ];
    }
    public function update(
    int $userId,
    array $data
): array {
    $validator = new Validator();

    /*
     * Validate user ID.
     */
    if ($userId <= 0) {
        $validator->addError(
            'user_id',
            'Invalid user.'
        );
    }

    /*
     * Validate editable fields.
     */
    $validator
        ->required(
            'username',
            $data['username'] ?? null,
            'Username is required.'
        )
        ->minLength(
            'username',
            $data['username'] ?? null,
            3,
            'Username must contain at least 3 characters.'
        )
        ->maxLength(
            'username',
            $data['username'] ?? null,
            50,
            'Username cannot exceed 50 characters.'
        )
        ->required(
            'name',
            $data['name'] ?? null,
            'Name is required.'
        )
        ->maxLength(
            'name',
            $data['name'] ?? null,
            100,
            'Name cannot exceed 100 characters.'
        )
        ->required(
            'email',
            $data['email'] ?? null,
            'Email is required.'
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
        ->required(
            'role',
            $data['role'] ?? null,
            'Role is required.'
        )
        ->in(
            'role',
            $data['role'] ?? null,
            ['admin', 'manager', 'staff'],
            'Invalid user role.'
        )
        ->required(
            'status',
            $data['status'] ?? null,
            'Status is required.'
        )
        ->in(
            'status',
            $data['status'] ?? null,
            ['active', 'inactive'],
            'Invalid user status.'
        );

    /*
     * Password is optional during an update.
     *
     * Only validate it when the administrator/user
     * actually submitted a new password.
     */
    $newPassword = $data['password'] ?? null;

    if (
        $newPassword !== null &&
        trim((string) $newPassword) !== ''
    ) {
        $validator->minLength(
            'password',
            $newPassword,
            8,
            'Password must contain at least 8 characters.'
        );
    }

    /*
     * Stage 1: basic input validation.
     */
    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * User being edited must exist.
     */
    $existingUser = $this->userRepository->findById(
        $userId
    );

    if ($existingUser === null) {
        $validator->addError(
            'user_id',
            'The selected user does not exist.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    $username = trim((string) $data['username']);
    $email = trim((string) $data['email']);

    /*
     * Username must be unique, except for the
     * current user's own username.
     */
    $userWithUsername =
        $this->userRepository->findByUsername($username);

    if (
        $userWithUsername !== null &&
        (int) $userWithUsername['id'] !== $userId
    ) {
        $validator->addError(
            'username',
            'This username is already in use.'
        );
    }

    /*
     * Email must also be unique, except for the
     * current user's own email.
     */
    $userWithEmail =
        $this->userRepository->findByEmail($email);

    if (
        $userWithEmail !== null &&
        (int) $userWithEmail['id'] !== $userId
    ) {
        $validator->addError(
            'email',
            'This email address is already in use.'
        );
    }

    /*
     * Stage 2: database/business validation.
     */
    if ($validator->fails()) {
        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    $passwordHash = null;

    /*
     * Password is changed separately and only when
     * a new password was intentionally submitted.
     */
    if (
        $newPassword !== null &&
        trim((string) $newPassword) !== ''
    ) {
        $passwordHash = password_hash(
            (string) $newPassword,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Password hashing failed.'
            );
        }
    }

    
    /*
     * Update normal user information.
     */
    $this->userRepository->update(
        $userId,
        [
            'username' => $username,
            'name' => trim((string) $data['name']),
            'email' => $email,
            'role' => $data['role'],
            'status' => $data['status'],
        ]
    );
        if ($passwordHash !== null) {
        $this->userRepository->updatePassword(
            $userId,
            $passwordHash
        );
    }

    return [
        'success' => true,
    ];
}
public function delete(int $userId): array
{
    $validator = new Validator();

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

    /*
     * User must exist.
     */
    if (
        $this->userRepository->findById($userId)
        === null
    ) {
        $validator->addError(
            'user_id',
            'User not found.'
        );

        return [
            'success' => false,
            'errors' => $validator->errors(),
        ];
    }

    /*
     * A user directly assigned to a work
     * must not be deleted.
     */
    if (
        $this->workAssignmentRepository
            ->existsByUserId($userId)
    ) {
        $validator->addError(
            'user_id',
            'Cannot delete this user because they are assigned to a work.'
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
    $this->userRepository->delete($userId);

    return [
        'success' => true,
    ];
}
}