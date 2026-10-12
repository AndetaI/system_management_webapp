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
<div class="d-flex justify-content-between align-items-center mb-4"><h1>Users</h1>
<a class="btn btn-primary" href="/?page=users&amp;action=create">Add User</a></div>
<form method="get" action="/" class="card card-body mb-4"><input type="hidden" name="page" value="users">
<div class="row g-3"><div class="col-md-5"><label class="form-label">Search</label><input class="form-control" name="search" value="<?= userEsc($search) ?>" placeholder="Username, name or email"></div>
<div class="col-md-3"><label class="form-label">Role</label><select name="role" class="form-select"><option value="">All roles</option>
<?php foreach (['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'] as $key=>$label): ?><option value="<?= $key ?>" <?= $role===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>
<?php foreach (['active'=>'Active','inactive'=>'Inactive'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div>
<div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div></div></form>
<div class="card"><div class="card-header"><h2 class="h5 mb-0">User List</h2></div><div class="card-body table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php if (!$users): ?><tr><td colspan="6" class="text-center text-muted">No users found.</td></tr><?php endif; ?>
<?php foreach ($users as $user): ?><tr><td><?= userEsc($user['username']) ?></td><td><?= userEsc($user['name']) ?></td><td><?= userEsc($user['email']) ?></td><td><?= userEsc(ucfirst($user['role'])) ?></td><td><span class="badge <?= $user['status']==='active'?'text-bg-success':'text-bg-secondary' ?>"><?= userEsc(ucfirst($user['status'])) ?></span></td><td><a class="btn btn-sm btn-outline-primary" href="/?page=users&amp;action=show&amp;id=<?= (int)$user['id'] ?>">View</a> <a class="btn btn-sm btn-outline-secondary" href="/?page=users&amp;action=edit&amp;id=<?= (int)$user['id'] ?>">Edit</a></td></tr><?php endforeach; ?>
</tbody></table></div></div>
</main>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
