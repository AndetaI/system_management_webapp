
<?php
$pageTitle = 'Stock Out - Barkea Resort';
$activePage = 'stock';

$old = $old ?? [];
$errors = $errors ?? [];
$works = $works ?? [];
$materials = $materials ?? [];

$escape = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

// Build unique villa options from existing works.
$villas = [];

foreach ($works as $work) {
    $villas[$work['villa_id']] = $work['villa_name'];
}

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>

<main class="flex-grow-1 p-4">
    <div class="container-fluid">

        <h2 class="mb-4">Stock Out - Material Usage</h2>

        <p class="text-muted">
            Record materials used for a specific villa and work.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $messages): ?>
                        <?php foreach ((array) $messages as $message): ?>
                            <li><?= $escape($message) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="/?page=stock&action=store-out">

            <?= Csrf::field() ?>

            <div class="row g-3">

                <!-- Villa -->
                <div class="col-md-6">
                    <label for="villa_id" class="form-label">
                        Villa
                    </label>

                    <select id="villa_id"
                            name="villa_id"
                            class="form-select"
                            required>
                        <option value="">Select Villa</option>

                        <?php foreach ($villas as $id => $name): ?>
                            <option value="<?= $escape($id) ?>"
                                <?= (string) ($old['villa_id'] ?? '') === (string) $id ? 'selected' : '' ?>>
                                <?= $escape($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Work -->
                <div class="col-md-6">
                    <label for="work_id" class="form-label">
                        Work
                    </label>

                    <select id="work_id"
                            name="work_id"
                            class="form-select"
                            required>
                        <option value="">Select Work</option>

                        <?php foreach ($works as $work): ?>
                            <option
                                value="<?= $escape($work['id']) ?>"
                                data-villa-id="<?= $escape($work['villa_id']) ?>"
                                <?= (string) ($old['work_id'] ?? '') === (string) $work['id'] ? 'selected' : '' ?>>
                                <?= $escape($work['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Material -->
                <div class="col-md-6">
                    <label for="material_id" class="form-label">
                        Material
                    </label>

                    <select id="material_id"
                            name="material_id"
                            class="form-select"
                            required>
                        <option value="">Select Material</option>

                        <?php foreach ($materials as $material): ?>
                            <option value="<?= $escape($material['id']) ?>"
                                <?= (string) ($old['material_id'] ?? '') === (string) $material['id'] ? 'selected' : '' ?>>
                                <?= $escape($material['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quantity -->
                <div class="col-md-6">
                    <label for="quantity" class="form-label">
                        Quantity
                    </label>

                    <input type="number"
                           id="quantity"
                           name="quantity"
                           class="form-control"
                           min="0.01"
                           step="0.01"
                           value="<?= $escape($old['quantity'] ?? '') ?>"
                           required>
                </div>

                <!-- Usage Date -->
                <div class="col-md-6">
                    <label for="date" class="form-label">
                        Usage Date
                    </label>

                    <input type="date"
                           id="date"
                           name="date"
                           class="form-control"
                           value="<?= $escape($old['date'] ?? date('Y-m-d')) ?>"
                           required>
                </div>

            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    Record Stock Out
                </button>

                <a href="/?page=stock" class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const villaSelect = document.getElementById('villa_id');
    const workSelect = document.getElementById('work_id');

    function filterWorks() {
        const selectedVilla = villaSelect.value;

        Array.from(workSelect.options).forEach(function (option) {
            if (!option.value) {
                return;
            }

            const matchesVilla =
                option.dataset.villaId === selectedVilla;

            option.hidden = !matchesVilla;
            option.disabled = !matchesVilla;
        });

        const selectedOption = workSelect.selectedOptions[0];

        if (
            !selectedOption ||
            selectedOption.disabled
        ) {
            workSelect.value = '';
        }
    }

    villaSelect.addEventListener('change', function () {
        workSelect.value = '';
        filterWorks();
    });

    filterWorks();
});
</script>

<?php
require __DIR__ . '/../../layouts/footer.php';
?>
