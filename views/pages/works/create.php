<?php
declare(strict_types=1);

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$old = static fn (string $key): string =>
    htmlspecialchars((string) ($_POST[$key] ?? ''), ENT_QUOTES, 'UTF-8');
?>

<main class="container-fluid p-4">
    <h2 class="mb-4">Create Work</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= $e(is_array($error) ? implode(', ', $error) : $error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/?page=works&action=create"
          class="card shadow-sm">
        <div class="card-body">
            <?= Csrf::field() ?>

            <div class="row g-3">

                <div class="col-md-6">
                    <label for="villa_id" class="form-label">Villa</label>
                    <select id="villa_id" name="villa_id"
                            class="form-select" required>
                        <option value="">Select Villa</option>
                        <?php foreach ($villas as $villa): ?>
                            <option value="<?= (int) $villa['id'] ?>"
                                <?= (string) $villa['id'] === ($_POST['villa_id'] ?? '') ? 'selected' : '' ?>>
                                <?= $e($villa['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Work Title</label>
                    <input id="title" type="text" name="title"
                           class="form-control" maxlength="150"
                           value="<?= $old('title') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <input id="category" type="text" name="category"
                           class="form-control" maxlength="100"
                           value="<?= $old('category') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <?php foreach (['planned', 'in_progress', 'completed'] as $status): ?>
                            <option value="<?= $status ?>"
                                <?= ($_POST['status'] ?? 'planned') === $status ? 'selected' : '' ?>>
                                <?= $e(ucwords(str_replace('_', ' ', $status))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description"
                              class="form-control" rows="3"><?= $old('description') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="planned_date" class="form-label">Planned Date</label>
                    <input id="planned_date" type="date" name="planned_date"
                           class="form-control" value="<?= $old('planned_date') ?>">
                </div>

                <div class="col-md-6">
                    <label for="actual_date" class="form-label">Actual Date</label>
                    <input id="actual_date" type="date" name="actual_date"
                           class="form-control" value="<?= $old('actual_date') ?>">
                </div>

                <div class="col-md-6">
                    <label for="assignment_type" class="form-label">
                        Assign To
                    </label>
                    <select id="assignment_type" name="assignment_type"
                            class="form-select" required>
                        <option value="">Select Assignment Type</option>
                        <option value="user" <?= ($_POST['assignment_type'] ?? '') === 'user' ? 'selected' : '' ?>>
                            User
                        </option>
                        <option value="team" <?= ($_POST['assignment_type'] ?? '') === 'team' ? 'selected' : '' ?>>
                            Team
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="assignment_id" class="form-label">
                        Responsible User / Team
                    </label>
                    <select id="assignment_id" name="assignment_id"
                            class="form-select" required>
                        <option value="">Select Responsible</option>

                        <?php foreach ($users as $user): ?>
                            <option
                                value="<?= (int) $user['id'] ?>"
                                data-type="user"
                                <?= ($_POST['assignment_type'] ?? '') === 'user' &&
                                    (string) ($_POST['assignment_id'] ?? '') === (string) $user['id']
                                    ? 'selected' : '' ?>>
                                <?= $e($user['name']) ?> (User)
                            </option>
                        <?php endforeach; ?>

                        <?php foreach ($teams as $team): ?>
                            <option
                                value="<?= (int) $team['id'] ?>"
                                data-type="team"
                                <?= ($_POST['assignment_type'] ?? '') === 'team' &&
                                    (string) ($_POST['assignment_id'] ?? '') === (string) $team['id']
                                    ? 'selected' : '' ?>>
                                <?= $e($team['name']) ?> (Team)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    Create Work
                </button>
                <a href="/?page=works" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('assignment_type');
    const idSelect = document.getElementById('assignment_id');

    function updateOptions() {
        const type = typeSelect.value;

        for (const option of idSelect.options) {
            if (!option.value) continue;

            option.hidden = option.dataset.type !== type;
            option.disabled = option.dataset.type !== type;
        }

        const selected = idSelect.selectedOptions[0];

        if (selected && selected.disabled) {
            idSelect.value = '';
        }
    }

    typeSelect.addEventListener('change', updateOptions);
    updateOptions();
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>