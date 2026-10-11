
<?php
$pageTitle = 'Materials | Barkea Resort';
$activePage = 'materials';

$escape = static fn($value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>

<main class="main-content flex-grow-1 p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Materials</h1>
            <p class="text-muted mb-0">Manage construction materials.</p>
        </div>

        <?php if (SessionManager::role() === 'admin'): ?>
            <a href="/?page=materials&amp;action=create"
               class="btn btn-primary">
                Add Material
            </a>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/">
                <input type="hidden" name="page" value="materials">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="materialSearch" class="form-label">
                            Search
                        </label>
                        <input type="search"
                               name="search"
                               id="materialSearch"
                               class="form-control"
                               value="<?= $escape($search) ?>"
                               placeholder="Search material...">
                    </div>

                    <div class="col-md-3">
                        <label for="materialCategory" class="form-label">
                            Category
                        </label>
                        <select name="category"
                                id="materialCategory"
                                class="form-select">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $item): ?>
                                <option value="<?= $escape($item) ?>"
                                    <?= $category === $item ? 'selected' : '' ?>>
                                    <?= $escape($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="materialStock" class="form-label">
                            Stock Status
                        </label>

                        <select name="stock"
                                id="materialStock"
                                class="form-select">

                            <option value="">All stock levels</option>

                            <option value="in_stock"
                                <?= $stockStatus === 'in_stock' ? 'selected' : '' ?>>
                                In Stock (10 or more)
                            </option>

                            <option value="low_stock"
                                <?= $stockStatus === 'low_stock' ? 'selected' : '' ?>>
                                Low Stock (below 10)
                            </option>

                            <option value="out_of_stock"
                                <?= $stockStatus === 'out_of_stock' ? 'selected' : '' ?>>
                                Out of Stock
                            </option>

                        </select>
                    </div>


                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Filter
                        </button>
                        <a href="/?page=materials" class="btn btn-secondary">
                            Clear
                        </a>
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
                        <?php foreach ($materials as $material): ?>
                            <tr>
                                <td><?= $escape($material['name']) ?></td>
                                <td><?= $escape($material['category']) ?></td>
                                <td><?= $escape($material['unit']) ?></td>
                                <td>€<?= number_format((float) $material['price'], 2) ?></td>
                                <td><?= $escape($material['quantity']) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="/?page=materials&amp;action=show&amp;id=<?= (int) $material['id'] ?>">
                                        View
                                    </a>

                                    <?php if (SessionManager::role() === 'admin'): ?>
                                        <a class="btn btn-sm btn-outline-secondary"
                                           href="/?page=materials&amp;action=edit&amp;id=<?= (int) $material['id'] ?>">
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($materials)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No materials found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
