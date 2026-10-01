<?php

$pageTitle = 'Teams | Barkea Resort';
$activePage = 'teams';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex flex-row justify-content-between align-items-center mb-4">

        <div>
            <h1>Teams</h1>
            <p class="text-muted mb-0">
                Manage construction teams and team members.
            </p>
        </div>

        <button class="btn btn-primary">
            Add Team
        </button>

    </div>


    <div class="card">

        <div class="card-header">
            <h2 class="h5 mb-0">Team List</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Description</th>
                            <th>Members</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No teams have been registered yet.
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>