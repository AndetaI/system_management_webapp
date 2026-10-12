<?php
function userEsc(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function userErrors(array $errors, string $field): string {
    $messages = $errors[$field] ?? [];
    return $messages ? '<div class="text-danger small">' . userEsc(implode(' ', (array) $messages)) . '</div>' : '';
}
?>
<?php
$pageTitle = 'Users | Barkea Resort';
$activePage = 'users';
require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>
<main class="main-content flex-grow-1 p-4">
<div class="d-flex justify-content-between align-items-center mb-4"><h1>User Details</h1><div><a class="btn btn-outline-primary" href="/?page=users&amp;action=edit&amp;id=<?= (int)$user['id'] ?>">Edit User</a> <a class="btn btn-outline-secondary" href="/?page=users">Back</a></div></div>
<div class="card"><div class="card-body"><dl class="row"><dt class="col-sm-3">Username</dt><dd class="col-sm-9"><?= userEsc($user['username']) ?></dd><dt class="col-sm-3">Full name</dt><dd class="col-sm-9"><?= userEsc($user['name']) ?></dd><dt class="col-sm-3">Email</dt><dd class="col-sm-9"><?= userEsc($user['email']) ?></dd><dt class="col-sm-3">Role</dt><dd class="col-sm-9"><?= userEsc(ucfirst($user['role'])) ?></dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9"><?= userEsc(ucfirst($user['status'])) ?></dd><dt class="col-sm-3">Created</dt><dd class="col-sm-9"><?= userEsc($user['created_at']) ?></dd></dl></div></div>
</main>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
