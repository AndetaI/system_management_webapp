<?php

$pageTitle = 'Dashboard | Barkea Resort';
$activePage = 'dashboard';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="mb-4">
        <h1>Dashboard</h1>
        <p class="text-muted">
            Overview of Barkea Resort construction activities.
        </p>
    </div>

    <div class="alert alert-info" role="alert">
        Welcome to the Barkea Resort management system.
    </div>


    <!-- Dashboard summary cards -->

    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h2 class="h5">Villas</h2>
                    <p class="display-6 fw-bold mb-1">34</p>
                    <p class="text-muted mb-0">Total villas</p>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h2 class="h5">Active Works</h2>
                    <p class="display-6 fw-bold mb-1">0</p>
                    <p class="text-muted mb-0">Currently active</p>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h2 class="h5">Materials</h2>
                    <p class="display-6 fw-bold mb-1">0</p>
                    <p class="text-muted mb-0">Registered materials</p>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h2 class="h5">Low Stock</h2>
                    <p class="display-6 fw-bold mb-1">0</p>
                    <p class="text-muted mb-0">
                        Materials requiring attention
                    </p>
                </div>
            </div>
        </div>

    </div>


    <!-- Recent works -->

    <div class="card mb-4">

        <div class="card-header">
            <h2 class="h4 mb-0">Recent Works</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Work</th>
                            <th>Villa</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No works have been registered yet.
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>
    </div>


    <!-- Quick search -->

    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Quick Search</h2>
        </div>

        <div class="card-body">

            <form id="searchForm">

                <div class="row g-2">

                    <div class="col-md-10">

                        <label for="searchInput" class="visually-hidden">
                            Search
                        </label>

                        <input
                            type="search"
                            class="form-control"
                            id="searchInput"
                            placeholder="Search villas, works or materials..."
                        >

                    </div>

                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Search
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>