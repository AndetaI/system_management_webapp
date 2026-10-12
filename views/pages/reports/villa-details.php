
<?php

$pageTitle = 'Villa Material Details | Barkea Resort';
$activePage = 'reports';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="mb-4">
        <h1>
            Material Report —
            <?= htmlspecialchars(
                (string) $villa['name'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <p class="text-muted">
            Material quantities and estimated costs
            for the selected villa.
        </p>

        <a href="/?page=reports"
           class="btn btn-outline-secondary">
            Back to Reports
        </a>
    </div>

    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Material Usage</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Quantity Used</th>
                            <th>Unit Price</th>
                            <th>Estimated Cost</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($materialDetails)): ?>
                            <tr>
                                <td colspan="4"
                                    class="text-center text-muted">
                                    No material usage recorded
                                    for this villa.
                                </td>
                            </tr>
                        <?php else: ?>

                            <?php foreach ($materialDetails as $material): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $material['material_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (float) $material['total_quantity'],
                                            2
                                        ) ?>

                                        <?= htmlspecialchars(
                                            (string) $material['unit'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        €<?= number_format(
                                            (float) $material['unit_price'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        €<?= number_format(
                                            (float) $material['total_cost'],
                                            2
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
