<?php
$pageTitle = 'Teams | Barkea Resort'; $activePage = 'teams';
require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>
<main class="main-content flex-grow-1 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1>Teams</h1><p class="text-muted">Manage construction teams and team members.</p></div>
    <?php if (Authorization::can('teams.create')): ?>
      <a class="btn btn-primary" href="/?page=teams&amp;action=create">Add Team</a>
    <?php endif; ?>
  </div>
  <div class="card"><div class="card-body table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Team</th><th>Description</th><th>Members</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (!$teams): ?><tr><td colspan="4" class="text-center text-muted">No teams have been registered yet.</td></tr><?php endif; ?>
        <?php foreach ($teams as $team): ?>
          <tr>
            <td><?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($team['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$team['member_count'] ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="/?page=teams&amp;action=show&amp;id=<?= (int)$team['id'] ?>">View</a>
              <?php if (Authorization::can('teams.update')): ?>
                <a class="btn btn-sm btn-outline-secondary" href="/?page=teams&amp;action=edit&amp;id=<?= (int)$team['id'] ?>">Edit</a>
                <form method="post" action="/?page=teams&amp;action=delete&amp;id=<?= (int)$team['id'] ?>" class="d-inline" data-confirm="Delete this team?">
                  <?= Csrf::field() ?><button type="submit" class="btn btn-outline-danger">Delete Team</button>
                </form>
              <?php endif; ?>
              
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</main>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
