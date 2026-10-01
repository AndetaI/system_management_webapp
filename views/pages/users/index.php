<?php

$pageTitle = 'Users | Barkea Resort';
$activePage = 'users';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <!-- Page heading -->
    <div class="d-flex flex-row justify-content-between align-items-center mb-4">

        <div>
            <h1>Users</h1>
            <p class="text-muted mb-0">
                Manage system users and their roles.
            </p>
        </div>

        <button type="button" class="btn btn-primary">
            Add User
        </button>

    </div>


    <!-- Search and filters -->
    <div class="card mb-4">

        <div class="card-body">

            <form>

                <div class="row g-3">

                    <!-- Search -->
                    <div class="col-md-5">

                        <label for="userSearch" class="form-label">
                            Search
                        </label>

                        <input
                            type="search"
                            class="form-control"
                            id="userSearch"
                            placeholder="Search by username or email..."
                        >

                    </div>


                    <!-- Role filter -->
                    <div class="col-md-3">

                        <label for="userRole" class="form-label">
                            Role
                        </label>

                        <select id="userRole" class="form-select">

                            <option value="">
                                All roles
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                            <option value="manager">
                                Manager
                            </option>

                            <option value="staff">
                                Staff
                            </option>

                        </select>

                    </div>


                    <!-- Status filter -->
                    <div class="col-md-2">

                        <label for="userStatus" class="form-label">
                            Status
                        </label>

                        <select id="userStatus" class="form-select">

                            <option value="">
                                All
                            </option>

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!-- Filter button -->
                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Filter
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- User list -->
    <div class="card">

        <div class="card-header">

            <h2 class="h5 mb-0">
                User List
            </h2>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>

                    </thead>


                    <tbody>

                        <!-- Temporary frontend data -->

                        <tr>

                            <td>admin</td>

                            <td>admin@example.com</td>

                            <td>
                                <span class="badge text-bg-primary">
                                    Admin
                                </span>
                            </td>

                            <td>
                                <span class="badge text-bg-success">
                                    Active
                                </span>
                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Edit
                                </button>

                            </td>

                        </tr>


                        <tr>

                            <td>manager01</td>

                            <td>manager@example.com</td>

                            <td>
                                <span class="badge text-bg-info">
                                    Manager
                                </span>
                            </td>

                            <td>
                                <span class="badge text-bg-success">
                                    Active
                                </span>
                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Edit
                                </button>

                            </td>

                        </tr>


                        <tr>

                            <td>staff01</td>

                            <td>staff@example.com</td>

                            <td>
                                <span class="badge text-bg-secondary">
                                    Staff
                                </span>
                            </td>

                            <td>
                                <span class="badge text-bg-secondary">
                                    Inactive
                                </span>
                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                >
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