<?php

declare(strict_types=1);

class UserController
{
    public function __construct(private UserRepository $users, private UserService $service) {}

    private function admin(string $permission): void
    {
        Authorization::requirePermission($permission);
    }

    private function post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }
        Csrf::requireValidToken();
    }

    private function getUser(int $id): array
    {
        $user = $id > 0 ? $this->users->findById($id) : null;
        if ($user === null) {
            http_response_code(404);
            exit('User not found.');
        }
        return $user;
    }

    public function index(): void
    {
        $this->admin('users.view');
        $users = $this->users->findAll();
        $search = trim((string) ($_GET['search'] ?? ''));
        $role = (string) ($_GET['role'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        $users = array_values(array_filter($users, static function (array $user) use ($search, $role, $status): bool {
            return ($search === '' || stripos($user['username'], $search) !== false || stripos($user['email'], $search) !== false || stripos($user['name'], $search) !== false)
                && ($role === '' || $user['role'] === $role)
                && ($status === '' || $user['status'] === $status);
        }));
        require __DIR__ . '/../views/pages/users/index.php';
    }

    public function create(): void
    {
        $this->admin('users.create');
        $old = []; $errors = [];
        require __DIR__ . '/../views/pages/users/create.php';
    }

    public function store(): void
    {
        $this->admin('users.create'); $this->post();
        $result = $this->service->create($_POST);
        if ($result['success']) {
            header('Location: /?page=users&action=show&id=' . (int) $result['id']); exit;
        }
        $old = $_POST; unset($old['password']);
        $errors = $result['errors'];
        require __DIR__ . '/../views/pages/users/create.php';
    }

    public function show(int $id): void
    {
        $this->admin('users.view');
        $user = $this->getUser($id);
        require __DIR__ . '/../views/pages/users/show.php';
    }

    public function edit(int $id): void
    {
        $this->admin('users.update');
        $user = $this->getUser($id);
        $old = $user; $errors = [];
        require __DIR__ . '/../views/pages/users/edit.php';
    }

    public function update(int $id): void
    {
        $this->admin('users.update'); $this->post();
        $user = $this->getUser($id);
        $actorId = SessionManager::userId();
        if ($actorId !== null && $id === $actorId && ($_POST['role'] ?? '') !== 'admin') {
            http_response_code(403); exit('You cannot remove your own Admin role.');
        }
        if ($actorId !== null && $id === $actorId && ($_POST['status'] ?? '') !== 'active') {
            http_response_code(403); exit('You cannot deactivate your own account.');
        }
        $result = $this->service->update($id, $_POST);
        if ($result['success']) {
            header('Location: /?page=users&action=show&id=' . $id); exit;
        }
        $old = $_POST; unset($old['password']);
        $errors = $result['errors'];
        require __DIR__ . '/../views/pages/users/edit.php';
    }
}
