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
                    <p class="display-6 fw-bold mb-0">0</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Low Stock</h2>
                    <p class="display-6 fw-bold mb-0">0</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Today's Movements</h2>
                    <p class="display-6 fw-bold mb-0">0</p>
                </div>
            </div>
        </div>

    </div>


    <div class="mb-3">

        <button class="btn btn-success me-2">
            Stock In
        </button>

        <button class="btn btn-warning">
            Stock Out
        </button>

    </div>


    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Stock Movements</h2>
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
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No stock movements have been registered yet.
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>