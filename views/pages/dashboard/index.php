<?php

$pageTitle = 'Dashboard | Barkea Resort';
$activePage = 'dashboard';

$workCounts = [
    'planned' => 0,
    'in_progress' => 0,
    'completed' => 0,
];

foreach ($worksByStatus as $item) {
    $status = (string) $item['status'];

    if (array_key_exists($status, $workCounts)) {
        $workCounts[$status] = (int) $item['total'];
    }
}

$totalWorks = array_sum($workCounts);

$completionPercentage = $totalWorks > 0
    ? round(($workCounts['completed'] / $totalWorks) * 100, 1)
    : 0;



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


    <!-- Dashboard summary cards -->

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4 dashboard-stats">

        <div class="col">
            <div class="card dashboard-card dashboard-card-villas h-100">
                <div class="card-body">
                    <h2 class="h5">Villas</h2>
                    <p class="display-6 fw-bold mb-1">
                        <?= (int) $summary['total_villas'] ?>
                    </p>
                    <p class="text-muted mb-0">Total villas</p>
                </div>
            </div>
        </div>


        <div class="col">
            <div class="card dashboard-card dashboard-card-works h-100">
                <div class="card-body">
                    <h2 class="h5">Active Works</h2>
                    <p class="display-6 fw-bold mb-1">
                        <?= (int) $summary['active_works'] ?>
                    </p>
                    <p class="text-muted mb-0">Currently active</p>
                </div>
            </div>
        </div>


        <div class="col">
            <div class="card dashboard-card dashboard-card-materials h-100">
                <div class="card-body">
                    <h2 class="h5">Materials</h2>
                    <p class="display-6 fw-bold mb-1">
                        <?= (int) $summary['total_materials'] ?>
                    </p>
                    <p class="text-muted mb-0">Registered materials</p>
                </div>
            </div>
        </div>


        <div class="col">
            <div class="card dashboard-card dashboard-card-low h-100">
                <div class="card-body">
                    <h2 class="h5">Low Stock</h2>
                    <p class="display-6 fw-bold mb-1">
                        <?= (int) $summary['low_stock'] ?>
                    </p>
                    <p class="text-muted mb-0">
                        Materials requiring attention
                    </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card dashboard-card dashboard-card-out h-100">
                <div class="card-body">
                    <h2 class="h5">Out of Stock</h2>
                    <p class="display-6 fw-bold mb-1">
                        <?= (int) $summary['out_of_stock'] ?>
                    </p>
                    <p class="text-muted mb-0">
                        Materials requiring attention
                    </p>
                </div>
            </div>
        </div>

    </div>

    
    <!-- Work Progress -->
    <div class="card mb-4 work-progress-card">
        <div class="card-header">
            <h2 class="h5 mb-0">Work Progress Overview</h2>
        </div>

        <div class="card-body">

            <div class="row text-center mb-4">

                <div class="col-md-3">
                    <h3 class="h4"><?= $totalWorks ?></h3>
                    <p class="text-muted">Total Works</p>
                </div>

                <div class="col-md-3">
                    <h3 class="h4"><?= $workCounts['planned'] ?></h3>
                    <p class="mb-0">
                        <span class="badge bg-primary">
                            Planned
                        </span>
                    </p>
                </div>

                <div class="col-md-3">
                    <h3 class="h4"><?= $workCounts['in_progress'] ?></h3>
                   <p class="mb-0">
                    <span class="badge bg-warning text-dark">
                        In Progress
                    </span>
                </p>
                </div>

                <div class="col-md-3">
                    <h3 class="h4"><?= $workCounts['completed'] ?></h3>
                    <p class="mb-0">
                        <span class="badge bg-success">
                            Completed
                        </span>
                    </p>
                </div>

            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Overall Work Completion</span>
                <strong><?= $completionPercentage ?>%</strong>
            </div>

            <div class="progress dashboard-progress">
                <div
                    class="progress-bar bg-success"
                    role="progressbar"
                    style="width: <?= $completionPercentage ?>%;"
                    aria-valuenow="<?= $completionPercentage ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <?= $completionPercentage ?>%
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
                            <th>Actions</th>
                        </tr>
                    </thead>

                    
                    <tbody>
                        <?php if (empty($recentWorks)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No works have been registered yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentWorks as $work): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $work['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $work['villa_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $work['category'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            ucwords(str_replace('_', ' ', $work['status'])),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="/?page=works&amp;action=show&amp;id=<?= (int) $work['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            View
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

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>