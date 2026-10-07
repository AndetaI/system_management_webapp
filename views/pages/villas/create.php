<?php

declare(strict_types=1);

$pageTitle = 'Add Villa';
$activePage = 'villas';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

function oldVillaValue(string $field): string
{
    return htmlspecialchars(
        (string) ($_POST[$field] ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2>Add Villa</h2>
            <p class="text-muted">
                Create a new villa.
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Please correct the following errors:</strong>

                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $fieldErrors): ?>
                        <?php foreach ((array) $fieldErrors as $error): ?>
                            <li>
                                <?= htmlspecialchars(
                                    (string) $error,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <form
                    method="POST"
                    action="/?page=villas&action=store"
                >

                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label
                            for="name"
                            class="form-label"
                        >
                            Villa Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            maxlength="50"
                            class="form-control"
                            value="<?= oldVillaValue('name') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label
                            for="typology_id"
                            class="form-label"
                        >
                            Typology
                        </label>

                        <select
                            id="typology_id"
                            name="typology_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Select typology
                            </option>

                            <?php foreach ($typologies as $typology): ?>
                                <option
                                    value="<?= (int) $typology['id'] ?>"
                                    <?= (
                                        (string) ($_POST['typology_id'] ?? '')
                                        === (string) $typology['id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars(
                                        $typology['group_name'],
                                        . ' - '
                                        . $typology['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label
                            for="status"
                            class="form-label"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                            required
                        >
                            <?php
                            $selectedStatus =
                                $_POST['status'] ?? 'planned';
                            ?>

                            <option
                                value="planned"
                                <?= $selectedStatus === 'planned'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Planned
                            </option>

                            <option
                                value="in_progress"
                                <?= $selectedStatus === 'in_progress'
                                    ? 'selected'
                                    : '' ?>
                            >
                                In Progress
                            </option>

                            <option
                                value="completed"
                                <?= $selectedStatus === 'completed'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Completed
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label
                            for="description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="4"
                        ><?= oldVillaValue('description') ?></textarea>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label
                                for="planned_date"
                                class="form-label"
                            >
                                Planned Date
                            </label>

                            <input
                                type="date"
                                id="planned_date"
                                name="planned_date"
                                class="form-control"
                                value="<?= oldVillaValue(
                                    'planned_date'
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label
                                for="actual_date"
                                class="form-label"
                            >
                                Actual Date
                            </label>

                            <input
                                type="date"
                                id="actual_date"
                                name="actual_date"
                                class="form-control"
                                value="<?= oldVillaValue(
                                    'actual_date'
                                ) ?>"
                            >
                        </div>

                    </div>

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Save Villa
                        </button>

                        <a
                            href="/?page=villas"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>