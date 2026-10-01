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
            View project, construction and material reports.
        </p>
    </div>


    <div class="row g-3">

        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Villa Report</h2>

                    <p class="text-muted">
                        View construction information by villa.
                    </p>

                    <button class="btn btn-outline-primary">
                        View Report
                    </button>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Work Report</h2>

                    <p class="text-muted">
                        View work status and progress.
                    </p>

                    <button class="btn btn-outline-primary">
                        View Report
                    </button>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Material Report</h2>

                    <p class="text-muted">
                        View material usage information.
                    </p>

                    <button class="btn btn-outline-primary">
                        View Report
                    </button>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Stock Report</h2>

                    <p class="text-muted">
                        View current warehouse stock.
                    </p>

                    <button class="btn btn-outline-primary">
                        View Report
                    </button>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Cost Report</h2>

                    <p class="text-muted">
                        View material and construction costs.
                    </p>

                    <button class="btn btn-outline-primary">
                        View Report
                    </button>
                </div>
            </div>
        </div>

    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>