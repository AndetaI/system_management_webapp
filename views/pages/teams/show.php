<?php
$pageTitle = 'Team Details | Barkea Resort'; $activePage = 'teams';
require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>
<main class="main-content flex-grow-1 p-4">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1><?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?></h1>
  <a class="btn btn-outline-secondary" href="/?page=teams">Back to Teams</a>
</div>
<p><?= nl2br(htmlspecialchars($team['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
<?php foreach ($errors as $messages): foreach ((array)$messages as $message): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endforeach; endforeach; ?>
<div class="card mb-4"><div class="card-header"><h2 class="h5 mb-0">Team Members (<?= count($members) ?>)</h2></div>
<div class="card-body table-responsive">
<table class="table table-hover"><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php if (!$members): ?><tr><td colspan="5" class="text-muted">No members assigned.</td></tr><?php endif; ?>
<?php foreach ($members as $member): ?><tr>
<td><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($member['username'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($member['role'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($member['status'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?php if (Authorization::can('teams.update')): ?>
<form method="post" action="/?page=teams&amp;action=remove-member&amp;id=<?= (int)$team['id'] ?>" data-confirm="Remove this member from the team?">
<?= Csrf::field() ?><input type="hidden" name="user_id" value="<?= (int)$member['id'] ?>">
<button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
</form><?php endif; ?></td>
</tr><?php endforeach; ?>
</tbody></table></div></div>
<?php if (Authorization::can('teams.update')): ?>
<div class="card mb-4"><div class="card-header"><h2 class="h5 mb-0">Add Staff Member</h2></div><div class="card-body">
<form method="post" action="/?page=teams&amp;action=add-member&amp;id=<?= (int)$team['id'] ?>">
<?= Csrf::field() ?>
<div class="d-flex gap-2"><select class="form-select" name="user_id" required>
<option value="">Select an active Staff user</option>
<?php foreach ($eligibleUsers as $user): ?><option value="<?= (int)$user['id'] ?>"><?= htmlspecialchars($user['name'] . ' (' . $user['username'] . ')', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
</select><button type="submit" class="btn btn-primary" <?= !$eligibleUsers ? 'disabled' : '' ?>>Add Member</button></div>
</form></div></div>
<?php endif; ?>
<?php if (Authorization::can('teams.update')): ?>
<a class="btn btn-outline-primary" href="/?page=teams&amp;action=edit&amp;id=<?= (int)$team['id'] ?>">Edit Team</a>
<?php endif; ?>
<?php if (Authorization::can('teams.delete')): ?>
<form method="post" action="/?page=teams&amp;action=delete&amp;id=<?= (int)$team['id'] ?>" class="d-inline" data-confirm="Delete this team?">
<?= Csrf::field() ?><button type="submit" class="btn btn-outline-danger">Delete Team</button>
</form>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
