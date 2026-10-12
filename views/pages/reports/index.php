
<?php

$pageTitle = 'Reports | Barkea Resort';
$activePage = 'reports';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="mb-4">
        <h1>Reports</h1>
        <p class="text-muted">
            Material usage and estimated costs across Barkea Resort.
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h2 class="h5">Material Usage Records</h2>

                    <p class="display-6 fw-bold mb-1">
                        <?= (int) $summary['total_usage_records'] ?>
                    </p>

                    <p class="text-muted mb-0">
                        Total recorded material usage transactions
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h2 class="h5">Estimated Material Cost</h2>

                    <p class="display-6 fw-bold mb-1">
                        €<?= number_format(
                            (float) $summary['total_material_cost'],
                            2
                        ) ?>
                    </p>

                    <p class="text-muted mb-0">
                        Based on current material prices
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Costs by Villa -->
    <div class="card mb-4">

        <div class="card-header">
            <h2 class="h5 mb-0">
                Material Costs by Villa
            </h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Villa</th>
                            <th>Usage Records</th>
                            <th>Estimated Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($villaCosts)): ?>
                            <tr>
                                <td colspan="4"
                                    class="text-center text-muted">
                                    No villas found.
                                </td>
                            </tr>
                        <?php else: ?>

                            <?php foreach ($villaCosts as $villa): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $villa['villa_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= (int) $villa['usage_records'] ?>
                                    </td>

                                    <td>
                                        €<?= number_format(
                                            (float) $villa['total_cost'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="/?page=reports&amp;action=villa-details&amp;id=<?= (int) $villa['villa_id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    <p class="small text-muted">
        Note: Estimated costs are calculated using the
        current prices stored for each material.
        They may differ from historical purchase costs.
    </p>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
