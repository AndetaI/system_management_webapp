<?php

declare(strict_types=1);

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>

<main class="container py-4">

    <h1 class="mb-4">Edit Work</h1>

    <?php if (!empty($errors)): ?>

        <div
            class="alert alert-danger"
            role="alert"
        >
            <strong>Please correct the following:</strong>

            <ul class="mb-0 mt-2">

                <?php foreach ($errors as $message): ?>

                    <li>
                        <?= htmlspecialchars(
                            (string) $message,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </li>

                <?php endforeach; ?>

            </ul>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        action="/?page=works&action=update&id=<?= (int) $work['id'] ?>"
    >

        <div class="mb-3">
            <label
                for="villa_id"
                class="form-label"
            >
                Villa ID
            </label>

            <input
                type="number"
                id="villa_id"
                name="villa_id"
                class="form-control"
                value="<?= htmlspecialchars(
                    (string) $work['villa_id'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label
                for="title"
                class="form-label"
            >
                Title
            </label>

            <input
                type="text"
                id="title"
                name="title"
                class="form-control"
                maxlength="150"
                value="<?= htmlspecialchars(
                    (string) $work['title'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label
                for="category"
                class="form-label"
            >
                Category
            </label>

            <input
                type="text"
                id="category"
                name="category"
                class="form-control"
                maxlength="100"
                value="<?= htmlspecialchars(
                    (string) $work['category'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >
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
                $statuses = [
                    'planned' => 'Planned',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                ];
                ?>

                <?php foreach ($statuses as $value => $label): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $value,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $work['status'] === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $label,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>

                <?php endforeach; ?>
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
            ><?= htmlspecialchars(
                (string) ($work['description'] ?? ''),
                ENT_QUOTES,
                'UTF-8'
            ) ?></textarea>
        </div>

        <div class="mb-3">
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
                value="<?= htmlspecialchars(
                    (string) ($work['planned_date'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
        </div>

        <div class="mb-3">
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
                value="<?= htmlspecialchars(
                    (string) ($work['actual_date'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
        </div>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Save Changes
        </button>

        <a
            href="/?page=works"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </form>

</main>

<?php
require __DIR__ . '/../../layouts/footer.php';
?>