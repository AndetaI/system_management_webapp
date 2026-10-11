<?php

declare(strict_types=1);

$pageTitle = 'Villas';
$activePage = 'villas';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Villas</h2>
                <p class="text-muted mb-0">
                    Manage and monitor Barkea Resort villas.
                </p>
            </div>

            <?php if (Authorization::can('villas.create')): ?>
                <a
                    href="/?page=villas&action=create"
                    class="btn btn-primary"
                >
                    Add Villa
                </a>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                
                <form method="GET" action="/" class="row g-3 mb-4">

                    <input type="hidden" name="page" value="villas">

                    <!-- Search -->
                    <div class="col-md-3">
                        <label for="villaSearch" class="form-label">Search</label>

                        <input
                            type="search"
                            name="search"
                            id="villaSearch"
                            class="form-control"
                            placeholder="Search villas..."
                            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <!-- Group -->
                    <div class="col-md-2">
                        <label for="villaGroup" class="form-label">Group</label>

                        <select name="group_id" id="villaGroup" class="form-select">
                            <option value="">All groups</option>

                            <?php foreach ($groups as $group): ?>
                                <option
                                    value="<?= (int) $group['id'] ?>"
                                    <?= $groupId === (int) $group['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Typology -->
                                        
                    <div class="col-md-2">
                        <label for="villaTypology" class="form-label">
                            Typology
                        </label>

                        <select
                            name="typology"
                            id="villaTypology"
                            class="form-select"
                        >
                            <option value="">All typologies</option>

                            <?php foreach ($typologies as $item): ?>
                                <option
                                    value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $typologyName === $item ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- Status -->
                    <div class="col-md-2">
                        <label for="villaStatus" class="form-label">Status</label>

                        <select name="status" id="villaStatus" class="form-select">
                            <option value="">All statuses</option>

                            <?php foreach ($statuses as $item): ?>
                                <option
                                    value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $status === $item ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars(
                                        ucwords(str_replace('_', ' ', $item)),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="/?page=villas" class="btn btn-outline-secondary">
                            Clear
                        </a>
                    </div>

                </form>


                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Villa</th>
                                <th>Group</th>
                                <th>Typology</th>
                                <th>Status</th>
                                <th>Planned Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if ($villas === []): ?>

                            <tr>
                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >
                                    No villas found.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($villas as $villa): ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $villa['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $villa['group_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $villa['typology_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $villa['status']
                                                )
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $villa['planned_date'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="/?page=villas&action=show&id=<?= (int) $villa['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            View
                                        </a>

                                        <?php if (
                                            Authorization::can('villas.update')
                                        ): ?>

                                            <a
                                                href="/?page=villas&action=edit&id=<?= (int) $villa['id'] ?>"
                                                class="btn btn-sm btn-outline-secondary"
                                            >
                                                Edit
                                            </a>

                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>