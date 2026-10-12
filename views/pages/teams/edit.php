<?php
$pageTitle = 'Edit Team | Barkea Resort'; $activePage = 'teams';
require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>
<main class="main-content flex-grow-1 p-4">
<h1>Edit Team</h1>
<form method="post" action="/?page=teams&amp;action=update&amp;id=<?= (int)$team['id'] ?>" class="card p-4" style="max-width:720px">
<?= Csrf::field() ?>
<?php foreach ($errors as $messages): foreach ((array)$messages as $message): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endforeach; endforeach; ?>
<div class="mb-3"><label class="form-label" for="name">Team name</label>
<input required maxlength="100" class="form-control" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="mb-3"><label class="form-label" for="description">Description</label>
<textarea class="form-control" id="description" name="description" rows="3" maxlength="1000"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
<div><button class="btn btn-primary" type="submit">Save Changes</button> <a class="btn btn-secondary" href="/?page=teams&amp;action=show&amp;id=<?= (int)$team['id'] ?>">Cancel</a></div>
</form>
</main>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
