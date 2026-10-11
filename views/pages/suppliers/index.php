
<?php

$pageTitle = 'Suppliers | Barkea Resort';
$activePage = 'suppliers';

$escape = static fn($value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$isAdmin = SessionManager::role() === 'admin';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Suppliers</h1>
            <p class="text-muted mb-0">
                Manage material suppliers and contact information.
            </p>
        </div>

        <?php if ($isAdmin): ?>
            <a href="/?page=suppliers&action=create"
               class="btn btn-primary">
                Add Supplier
            </a>
        <?php endif; ?>
    </div>

    
    <form method="GET" action="/" class="row g-2 mb-3">
        <input type="hidden" name="page" value="suppliers">

        <div class="col-md-5">
            <input
                type="search"
                name="search"
                class="form-control"
                placeholder="Search suppliers..."
                value="<?= htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">
                Search
            </button>
        </div>

        <div class="col-auto">
            <a href="/?page=suppliers" class="btn btn-secondary">
                Clear
            </a>
        </div>
    </form>


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
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="5"
                                    class="text-center text-muted">
                                    No suppliers found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?= $escape($supplier['name']) ?></td>
                                    <td><?= $escape($supplier['contact_person']) ?></td>
                                    <td><?= $escape($supplier['email']) ?></td>
                                    <td><?= $escape($supplier['phone']) ?></td>

                                    <td>
                                        <?php if ($isAdmin): ?>
                                            <a
                                                class="btn btn-sm btn-outline-primary"
                                                href="/?page=suppliers&amp;action=edit&amp;id=<?= (int) $supplier['id'] ?>"
                                            >
                                                Edit
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">View only</span>
                                        <?php endif; ?>
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
