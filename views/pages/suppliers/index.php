<?php

$pageTitle = 'Suppliers | Barkea Resort';
$activePage = 'suppliers';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex flex-row justify-content-between align-items-center mb-4">

        <div>
            <h1>Suppliers</h1>
            <p class="text-muted mb-0">
                Manage material suppliers and contact information.
            </p>
        </div>

        <button class="btn btn-primary">
            Add Supplier
        </button>

    </div>


    <div class="card mb-4">

        <div class="card-body">

            <label for="supplierSearch" class="form-label">
                Search
            </label>

            <input
                type="search"
                id="supplierSearch"
                class="form-control"
                placeholder="Search supplier..."
            >

        </div>

    </div>


    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Supplier List</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No suppliers have been registered yet.
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>