<?php
declare(strict_types=1);

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>

<main class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Work Management</h2>

        <?php if (Authorization::can('works.create')): ?>
            <a href="/?page=works&action=create"
               class="btn btn-primary">
                Add Work
            </a>
        <?php endif; ?>
    </div>
    <!-- Success message -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars(
                (string) $_SESSION['flash_success'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- Error message -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars(
                (string) $_SESSION['flash_error'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>


    <form method="GET" action="/" class="mb-4">
        <input type="hidden" name="page" value="works">

        <div class="row g-3">

            <!-- Search -->
            <div class="col-md-4">
                <label for="workSearch" class="form-label">Search</label>
                <input
                    type="search"
                    id="workSearch"
                    name="search"
                    class="form-control"
                    placeholder="Search works..."
                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label for="workStatus" class="form-label">Status</label>
                <select name="status" id="workStatus" class="form-select">
                    <option value="">All statuses</option>

                    <option value="planned"
                        <?= $status === 'planned' ? 'selected' : '' ?>>
                        Planned
                    </option>

                    <option value="in_progress"
                        <?= $status === 'in_progress' ? 'selected' : '' ?>>
                        In Progress
                    </option>

                    <option value="completed"
                        <?= $status === 'completed' ? 'selected' : '' ?>>
                        Completed
                    </option>
                </select>
            </div>

            <!-- Category -->
            <div class="col-md-2">
                <label for="workCategory" class="form-label">Category</label>
                <select name="category" id="workCategory" class="form-select">
                    <option value="">All categories</option>

                    <?php foreach ($categories as $item): ?>
                        <option value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $category === $item ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Villa -->
            <div class="col-md-2">
                <label for="workVilla" class="form-label">Villa</label>
                <select name="villa_id" id="workVilla" class="form-select">
                    <option value="">All villas</option>

                    <?php foreach ($villas as $villa): ?>
                        <option value="<?= (int) $villa['id'] ?>"
                            <?= $villaId === (int) $villa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($villa['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    Filter
                </button>

                <a href="/?page=works" class="btn btn-outline-secondary">
                    Clear
                </a>
            </div>

        </div>
    </form>


    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Villa</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Responsible</th>
                        <th>Planned Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($works === []): ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                No works found.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($works as $work): ?>

                            <?php
                            $responsible = $work['assigned_user_name']
                                ?? $work['assigned_team_name']
                                ?? 'Unassigned';
                            ?>

                            <tr class="<?= SessionManager::role() === 'staff'
                                && ($canEditWork[(int) $work['id']] ?? false)
                                    ? 'table-primary'
                                    : '' ?>">
                                <td><?= (int) $work['id'] ?></td>

                                <td>
                                    <?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($work['villa_name'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($work['category'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($work['status'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($responsible, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($work['planned_date'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <a
                                        href="/?page=works&amp;action=show&amp;id=<?= (int) $work['id'] ?>"
                                        class="btn btn-sm btn-outline-info">
                                        View
                                    </a>
                            
                                    <?php if ($canEditWork[(int) $work['id']] ?? false): ?>

                                        <?php if (SessionManager::role() === 'admin'): ?>
                                            <a
                                                href="/?page=works&amp;action=edit&amp;id=<?= (int) $work['id'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>

                                        <?php elseif (SessionManager::role() === 'staff'): ?>
                                            <a
                                                href="/?page=works&amp;action=show&amp;id=<?= (int) $work['id'] ?>#update-progress"
                                                class="btn btn-sm btn-outline-primary">
                                                Edit Progress
                                            </a>
                                        <?php endif; ?>

                                    <?php endif; ?>

                                    <?php if (Authorization::can('works.delete')): ?>
                                        <form
                                            method="POST"
                                            action="/?page=works&amp;action=delete&amp;id=<?= (int) $work['id'] ?>"
                                            class="d-inline"
                                            data-confirm="Delete this work?"
                                        >
                                            <?= Csrf::field() ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>