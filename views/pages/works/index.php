<?php

$pageTitle = 'Works | Barkea Resort';
$activePage = 'works';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex flex-row justify-content-between align-items-center mb-4">
        <div>
            <h1>Works</h1>
            <p class="text-muted mb-0">
                Manage construction works and activities.
            </p>
        </div>

        <?php if (Authorization::can('works.create')): ?>
            <a
                href="/?page=works&action=create"
                class="btn btn-primary"
            >
                Add Work
            </a>
        <?php endif; ?>
    </div>


    <!-- Search and filters -->

    <div class="card mb-4">
        <div class="card-body">

            <form>
                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="workSearch" class="form-label">
                            Search
                        </label>

                        <input
                            type="search"
                            class="form-control"
                            id="workSearch"
                            placeholder="Search work..."
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="workVilla" class="form-label">
                            Villa
                        </label>

                        <select id="workVilla" class="form-select">
                            <option value="">All villas</option>
                            <option>A1</option>
                            <option>A2</option>
                            <option>A3</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="workStatus" class="form-label">
                            Status
                        </label>

                        <select id="workStatus" class="form-select">
                            <option value="">All statuses</option>
                            <option>Planned</option>
                            <option>In Progress</option>
                            <option>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Filter
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>


    <!-- Works table -->

    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Work List</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Work</th>
                            <th>Villa</th>
                            <th>Category</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Electrical Installation</td>
                            <td>A1</td>
                            <td>Electrical</td>
                            <td>Team Electrical</td>

                            <td>
                                <span class="badge text-bg-warning">
                                    In Progress
                                </span>
                            </td>

                            <td>60%</td>

                            <td>
                                <button 
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                disabled
                                >
                                    View
                                </button>

                                <?php if (Authorization::can('works.update')): ?>
                                    <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    disabled
                                    >
                                        Edit
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Interior Painting</td>
                            <td>A2</td>
                            <td>Finishing</td>
                            <td>John Doe</td>

                            <td>
                                <span class="badge text-bg-secondary">
                                    Planned
                                </span>
                            </td>

                            <td>0%</td>

                            <td>
                                <button 
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                disabled
                                >
                                    View
                                </button>

                                <?php if (Authorization::can('works.update')): ?>
                                    <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    disabled
                                    >
                                        Edit
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>