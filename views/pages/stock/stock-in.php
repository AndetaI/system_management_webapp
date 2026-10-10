
<?php

$pageTitle = 'Stock In | Barkea Resort';
$activePage = 'stock';

$escape = static fn($value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <h1>Stock In</h1>
    <p class="text-muted">
        Register a new material delivery and update warehouse stock.
    </p>

    <div class="card">
        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $fieldErrors): ?>
                        <?php foreach ((array) $fieldErrors as $error): ?>
                            <div><?= $escape($error) ?></div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="/?page=stock&amp;action=store-in">

                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label" for="supplier_id">
                        Supplier *
                    </label>

                    <select
                        class="form-select"
                        id="supplier_id"
                        name="supplier_id"
                        required
                    >
                        <option value="">Select Supplier</option>

                        <?php foreach ($suppliers as $supplier): ?>
                            <option
                                value="<?= (int) $supplier['id'] ?>"
                                <?= (string) ($old['supplier_id'] ?? '') === (string) $supplier['id']
                                    ? 'selected' : '' ?>
                            >
                                <?= $escape($supplier['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="material_id">
                        Material *
                    </label>

                    <select
                        class="form-select"
                        id="material_id"
                        name="material_id"
                        required
                    >
                        <option value="">Select Material</option>

                        <?php foreach ($materials as $material): ?>
                            <option
                                value="<?= (int) $material['id'] ?>"
                                <?= (string) ($old['material_id'] ?? '') === (string) $material['id']
                                    ? 'selected' : '' ?>
                            >
                                <?= $escape($material['name']) ?>
                                (<?= $escape($material['unit']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="form-text">
                        Select a material associated with the chosen supplier.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="quantity">
                        Received Quantity *
                    </label>

                    <input
                        class="form-control"
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="0.01"
                        max="99999999.99"
                        step="0.01"
                        value="<?= $escape($old['quantity'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        maxlength="255"
                        rows="3"
                        placeholder="Delivery reference or additional notes"
                    ><?= $escape($old['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Register Delivery
                </button>

                <a href="/?page=stock"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>
        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
