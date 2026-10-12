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
<h1>Edit User</h1><div class="card"><div class="card-body"><form method="post" action="/?page=users&amp;action=update&amp;id=<?= (int)$user['id'] ?>">
<?= Csrf::field() ?>
<?php $isEdit = true; ?>
<?php foreach ($errors as $field => $messages): ?><?php if (!in_array($field, ['username','name','email','password','role','status'], true)): ?><div class="alert alert-danger"><?= userEsc(implode(' ', (array)$messages)) ?></div><?php endif; ?><?php endforeach; ?>
<div class="row g-3">
<?php foreach (['username'=>'Username','name'=>'Full name','email'=>'Email'] as $field=>$label): ?><div class="col-md-6"><label class="form-label" for="<?= $field ?>"><?= $label ?></label><input id="<?= $field ?>" class="form-control" name="<?= $field ?>" value="<?= userEsc($old[$field] ?? '') ?>" <?= $field==='email'?'type="email"':'type="text"' ?> required><?= userErrors($errors,$field) ?></div><?php endforeach; ?>
<div class="col-md-6"><label class="form-label" for="password"><?= $isEdit?'New password (leave blank to keep current)':'Password' ?></label><input id="password" type="password" class="form-control" name="password" autocomplete="new-password" <?= $isEdit?'':'required' ?>><?= userErrors($errors,'password') ?></div>
<div class="col-md-6"><label class="form-label" for="role">Role</label><select id="role" name="role" class="form-select" required><?php foreach (['staff'=>'Staff','manager'=>'Manager','admin'=>'Admin'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($old['role'] ?? 'staff')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select><?= userErrors($errors,'role') ?></div>
<div class="col-md-6"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select" required><?php foreach (['active'=>'Active','inactive'=>'Inactive'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($old['status'] ?? 'active')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select><?= userErrors($errors,'status') ?></div>
</div><div class="mt-4"><button class="btn btn-primary" type="submit"><?= $isEdit?'Save Changes':'Create User' ?></button> <a class="btn btn-outline-secondary" href="/?page=users">Cancel</a></div>
</form></div></div></main>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
