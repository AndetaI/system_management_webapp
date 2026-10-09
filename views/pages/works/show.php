<?php
declare(strict_types=1);

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

$escape = static fn (mixed $value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$responsible = 'Unassigned';

if (!empty($work['assigned_user_name'])) {
    $responsible = 'User: ' . $work['assigned_user_name'];
} elseif (!empty($work['assigned_team_name'])) {
    $responsible = 'Team: ' . $work['assigned_team_name'];
}
?>

<main class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Work Details</h2>

        <a href="/?page=works" class="btn btn-secondary">
            Back to Works
        </a>
    </div>
    <?php foreach ([
        'flash_success' => 'success', 
        'flash_error' => 'danger'] 
        as $key => $type): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div class="alert alert-<?= $type ?>" role="alert">
                <?= htmlspecialchars(
                    (string) $_SESSION[$key],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h4 class="mb-4">
                <?= $escape($work['title']) ?>
            </h4>

            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Villa</th>
                        <td><?= $escape($work['villa_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?= $escape($work['category']) ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?= $escape($work['status']) ?></td>
                    </tr>
                    <tr>
                        <th>Responsible</th>
                        <td><?= $escape($responsible) ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?= nl2br($escape($work['description'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th>Planned Date</th>
                        <td><?= $escape($work['planned_date'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Actual Date</th>
                        <td><?= $escape($work['actual_date'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if ($canEdit): ?>
                <a
                    href="/?page=works&amp;action=edit&amp;id=<?= (int) $work['id'] ?>"
                    class="btn btn-primary">
                    <?= SessionManager::role() === 'staff'
                        ? 'Edit Progress'
                        : 'Edit Work' ?>
                </a>
            <?php endif; ?>

        </div>
    </div>


    <?php if ($canUpdateProgress): ?>
        <div class="card shadow-sm mb-4">
            <div class = "card-header">
                <h5 class="mb-0">Update Work Progress</h5>
            </div>
            <div class="card-body">
                <form method="post" action="/?page=works&amp;action=updateProgress&amp;id=<?= (int) $work['id'] ?>">
                    <?=Csrf::field()?>

                    <div class="mb-3">
                        <label for="progress-status" class="form-label">
                            New Status
                        </label>
                        <select 
                            id="progress-status"
                            name="status"
                            class="form-select"
                            required>
                            <?php foreach (['planned', 'in_progress', 'completed'] as $status): ?>
                                <option 
                                value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                                <?=$work['status'] === $status ? 'selected' : ''?>>
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8') ?>
                                 </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="progress-actual-date" class="form-label">
                            Actual Completion Date
                        </label>

                        <input
                            type="date"
                            id="progress-actual-date"
                            name="actual_date"
                            class="form-control"
                            value="<?= $escape($work['actual_date'] ?? '') ?>"
                        >

                        <div class="form-text">
                            Required when the Work status is Completed.
                        </div>
                    </div>
                    <div class = "mb-3">
                        <label for="progress-note" class="form-label">
                        Progress Note
                        </label>
                        <textarea
                        id="progress-note"
                        name="note"
                        class="form-control"
                        rows="4"
                        maxlength="2000"
                        required
                        placeholder="Describe the progress made..."
                        ></textarea>
                    </div>
                         
                    <button type="submit" class="btn btn-primary">Save Progress Update</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Progress History</h5>
        </div>

        <div class="card-body">

            <?php if (empty($progressHistory)): ?>
                <p class="text-muted mb-0">
                    No progress updates have been recorded yet.
                </p>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Updated By</th>
                                <th>Previous Status</th>
                                <th>New Status</th>
                                <th>Note</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($progressHistory as $entry): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $entry['created_at'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $entry['author_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $entry['old_status'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $entry['new_status'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= nl2br(htmlspecialchars(
                                            (string) ($entry['note'] ?? ''),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>

  
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>