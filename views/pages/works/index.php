<?php
declare(strict_types=1);

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

$search = trim((string) ($_GET['search'] ?? ''));
$visibleWorks = array_filter(
    $works,
    static function (array $work) use ($search): bool {
        if ($search === '') {
            return true;
        }

        $responsible = $work['assigned_user_name']
            ?? $work['assigned_team_name']
            ?? '';

        $searchable = implode(' ', [
            $work['title'],
            $work['category'],
            $work['villa_name'],
            $work['status'],
            $responsible
        ]);

        return stripos($searchable, $search) !== false;
    }
);
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

        <div class="input-group">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search works..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit" class="btn btn-primary">
                Search
            </button>
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
                    <?php if ($visibleWorks === []): ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                No works found.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($visibleWorks as $work): ?>

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
                                        <a
                                            href="/?page=works&amp;action=edit&amp;id=<?= (int) $work['id'] ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <?= SessionManager::role() === 'staff'
                                                ? 'Edit Progress'
                                                : 'Edit' ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (Authorization::can('works.delete')): ?>
                                        <form
                                            method="POST"
                                            action="/?page=works&amp;action=delete&amp;id=<?= (int) $work['id'] ?>"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this work?');"
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