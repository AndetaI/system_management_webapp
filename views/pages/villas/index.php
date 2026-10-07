<?php

declare(strict_types=1);

$pageTitle = 'Villas';
$activePage = 'villas';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

$search = trim((string) ($_GET['search'] ?? ''));
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

                <form
                    method="GET"
                    action="/"
                    class="row g-2 mb-4"
                >
                    <input
                        type="hidden"
                        name="page"
                        value="villas"
                    >

                    <div class="col-md-10">
                        <input
                            type="search"
                            name="search"
                            class="form-control"
                            placeholder="Search villas..."
                            value="<?= htmlspecialchars(
                                $search,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button
                            type="submit"
                            class="btn btn-outline-primary"
                        >
                            Search
                        </button>
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

                        <?php
                        $visibleVillas = array_filter(
                            $villas,
                            static function (array $villa) use ($search): bool {
                                if ($search === '') {
                                    return true;
                                }

                                $haystack =
                                    $villa['name'] . ' '
                                    . $villa['group_name'] . ' '
                                    . $villa['typology_name'] . ' '
                                    . $villa['status'];

                                return stripos(
                                    $haystack,
                                    $search
                                ) !== false;
                            }
                        );
                        ?>

                        <?php if ($visibleVillas === []): ?>

                            <tr>
                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >
                                    No villas found.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($visibleVillas as $villa): ?>

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