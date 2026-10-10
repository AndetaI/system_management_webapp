<?php

$pageTitle = 'Warehouse | Barkea Resort';
$activePage = 'stock';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="mb-4">
        <h1>Warehouse / Stock</h1>
        <p class="text-muted">
            Manage material stock and warehouse movements.
        </p>
    </div>


    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Materials in Stock</h2>
                    <p class="display-6 fw-bold mb-0">
                        <?= $materialsInStock ?>
                    </p>                
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Low Stock</h2>
                    <p class="display-6 fw-bold mb-0">
                        <?= $lowStock ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Today's Movements</h2>
                    <p class="display-6 fw-bold mb-0">
                        <?= $todayMovements ?>
                    </p>
                </div>
            </div>
        </div>

    </div>


    <div class="mb-3">

        <?php if (SessionManager::role() === 'admin'): ?>
            <a href="/?page=stock&amp;action=in"
            class="btn btn-success me-2">
                Stock In
            </a>
        <?php endif; ?>

        <button class="btn btn-warning">
            Stock Out
        </button>

    </div>


    <div class="card">

       <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Recent Stock Movements</h2>

            <a href="/?page=stock&amp;action=movements"
            class="btn btn-outline-primary btn-sm">
                View All Movements
            </a>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Material</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Description</th>
                        </tr>
                    </thead>

                    
                    <tbody>
                        <?php if (empty($recentMovements)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No stock movements have been registered yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentMovements as $movement): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $movement['date'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($movement['material_name'] ?? $movement['material_id']),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php if ($movement['type'] === 'IN'): ?>
                                            <span class="badge bg-success">IN</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">OUT</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $movement['quantity'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($movement['description'] ?? '-'),
                                            ENT_QUOTES,
                                            'UTF-8'
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