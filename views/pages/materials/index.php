<?php

$pageTitle = 'Materials | Barkea Resort';
$activePage = 'materials';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex flex-row justify-content-between align-items-center mb-4">

        <div>
            <h1>Materials</h1>
            <p class="text-muted mb-0">
                Manage construction materials.
            </p>
        </div>

        <button class="btn btn-primary">
            Add Material
        </button>

    </div>


    <div class="card mb-4">
        <div class="card-body">

            <form>
                <div class="row g-3">

                    <div class="col-md-5">
                        <label for="materialSearch" class="form-label">
                            Search
                        </label>

                        <input
                            type="search"
                            class="form-control"
                            id="materialSearch"
                            placeholder="Search material..."
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="materialCategory" class="form-label">
                            Category
                        </label>

                        <select id="materialCategory" class="form-select">
                            <option value="">All categories</option>
                            <option>Construction</option>
                            <option>Electrical</option>
                            <option>Plumbing</option>
                            <option>Finishing</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            Filter
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>


    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Material List</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Cement</td>
                            <td>Construction</td>
                            <td>Bag</td>
                            <td>€0.00</td>
                            <td>0</td>

                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    View
                                </button>

                                <button class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>Electrical Cable</td>
                            <td>Electrical</td>
                            <td>Meter</td>
                            <td>€0.00</td>
                            <td>0</td>

                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    View
                                </button>

                                <button class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </button>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>