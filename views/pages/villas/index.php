<?php

$pageTitle = 'Villas | Barkea Resort';
$activePage = 'villas';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex flex-row justify-content-between align-items-center mb-4">

        <div>
            <h1>Villas</h1>
            <p class="text-muted mb-0">
                Manage Barkea Resort villas.
            </p>
        </div>

        <button type="button" class="btn btn-primary">
            Add Villa
        </button>

    </div>


    <!-- Search and filters -->

    <div class="card mb-4">
        <div class="card-body">

            <form>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="villaSearch" class="form-label">
                            Search
                        </label>

                        <input
                            type="search"
                            id="villaSearch"
                            class="form-control"
                            placeholder="Search villa..."
                        >
                    </div>


                    <div class="col-md-3">

                        <label for="villaGroup" class="form-label">
                            Group
                        </label>

                        <select id="villaGroup" class="form-select">
                            <option value="">All groups</option>
                            <option value="A">Group A</option>
                            <option value="B">Group B</option>
                            <option value="C">Group C</option>
                            <option value="Vila">Vila</option>
                        </select>

                    </div>


                    <div class="col-md-3">

                        <label for="villaStatus" class="form-label">
                            Status
                        </label>

                        <select id="villaStatus" class="form-select">
                            <option value="">All statuses</option>
                            <option value="planned">Planned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>

                    </div>


                    <div class="col-md-2 d-flex align-items-end">

                        <button type="submit" class="btn btn-primary w-100">
                            Go
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>


    <!-- Villas table -->

    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Villa List</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Villa</th>
                            <th>Group</th>
                            <th>Typology</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Temporary Task 3 frontend data -->

                        <tr>
                            <td>A1</td>
                            <td>A</td>
                            <td>Type 1</td>

                            <td>
                                <span class="badge text-bg-warning">
                                    In Progress
                                </span>
                            </td>

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
                            <td>A2</td>
                            <td>A</td>
                            <td>Type 1</td>

                            <td>
                                <span class="badge text-bg-secondary">
                                    Planned
                                </span>
                            </td>

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
                            <td>A7</td>
                            <td>A</td>
                            <td>Type 2</td>

                            <td>
                                <span class="badge text-bg-success">
                                    Completed
                                </span>
                            </td>

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