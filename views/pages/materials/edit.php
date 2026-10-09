
<?php
$pageTitle = 'Edit Material | Barkea Resort';
$activePage = 'materials';
$escape = static fn($v): string =>
    htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>

<main class="main-content flex-grow-1 p-4">
    <h1>Edit Material</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            Please correct the invalid fields.
        </div>
    <?php endif; ?>

    <form method="POST"
          action="/?page=materials&amp;action=update&amp;id=<?= (int) $material['id'] ?>">
        <?= Csrf::field() ?>

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Material Name</label>
                        <input name="name" class="form-control"
                               maxlength="150" required
                               value="<?= $escape($material['name']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input name="category" class="form-control"
                               maxlength="100" required
                               value="<?= $escape($material['category']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <input name="unit" class="form-control"
                               maxlength="50" required
                               value="<?= $escape($material['unit']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Price (€)</label>
                        <input type="number" name="price"
                               class="form-control"
                               min="0" max="99999999.99" step="0.01"
                               required
                               value="<?= $escape($material['price']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= (int) $supplier['id'] ?>"
                                    <?= (int) $material['supplier_id'] === (int) $supplier['id'] ? 'selected' : '' ?>>
                                    <?= $escape($supplier['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"
                                  rows="3"><?= $escape($material['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
                <a href="/?page=materials" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
