
<?php

$pageTitle = 'Stock Movements | Barkea Resort';
$activePage = 'stock';

$escape = static fn($value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="mb-4">
        <h1>Stock Movement History</h1>
        <p class="text-muted">
            View all material deliveries and warehouse withdrawals.
        </p>
    </div>

    <div class="mb-3">
        <a href="/?page=stock" class="btn btn-secondary">
            Back to Warehouse
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Material</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Description</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted">
                                    No stock movements found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td>
                                        <?= $escape($movement['date']) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movement['material_name']
                                            ?? $movement['material_id']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movement['supplier_name'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php if ($movement['type'] === 'IN'): ?>
                                            <span class="badge bg-success">
                                                IN
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                OUT
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= $escape($movement['quantity']) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movement['description'] ?? '-'
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
