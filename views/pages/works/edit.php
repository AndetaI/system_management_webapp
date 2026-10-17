<?php

declare(strict_types=1);

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

$isAdmin = SessionManager::role() === 'admin';

$escape = static function (mixed $value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$assignmentType = $_POST['assignment_type']
    ?? (
        ($work['user_id'] ?? null) !== null
            ? 'user'
            : (
                ($work['team_id'] ?? null) !== null
                    ? 'team'
                    : ''
            )
    );

$assignmentId = $_POST['assignment_id']
    ?? (
        $work['user_id']
        ?? $work['team_id']
        ?? ''
    );

$statuses = [
    'planned' => 'Planned',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
];

?>

<main class="container py-4">

    <h1 class="mb-4">Edit Work</h1>


    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Please correct the following:</strong>

            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $message): ?>
                    <li><?= $escape($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
    <form
        method="POST"
        action="/?page=works&amp;action=update&amp;id=<?= (int) $work['id'] ?>"
    >
        <?= Csrf::field() ?>

        <?php if ($isAdmin): ?>

            <!-- Villa -->
            <div class="mb-3">
                <label for="villa_id" class="form-label">
                    Villa
                </label>

                <select
                    id="villa_id"
                    name="villa_id"
                    class="form-select"
                    required
                >
                    <option value="">Select Villa</option>

                    <?php foreach ($villas as $villa): ?>
                        <option
                            value="<?= (int) $villa['id'] ?>"
                            <?= (string) $work['villa_id'] === (string) $villa['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= $escape($villa['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Title -->
            <div class="mb-3">
                <label for="title" class="form-label">
                    Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    maxlength="150"
                    value="<?= $escape($work['title'] ?? '') ?>"
                    required
                >
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label for="category" class="form-label">
                    Category
                </label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    class="form-control"
                    maxlength="100"
                    value="<?= $escape($work['category'] ?? '') ?>"
                    required
                >
            </div>

        <?php else: ?>

            <!-- Read-only Work information for Staff -->
            <div class="mb-3">
                <label class="form-label">Villa</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?= $escape($work['villa_name'] ?? $work['villa_id']) ?>"
                    readonly
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?= $escape($work['title'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?= $escape($work['category'] ?? '') ?>"
                    readonly
                >
            </div>

        <?php endif; ?>

        <!-- Status: Admin and assigned Staff -->
        <div class="mb-3">
            <label class="form-label">Current Status</label>

            <input
                type="text"
                class="form-control"
                value="<?= htmlspecialchars(
                    (string) $work['status'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                readonly
            >

            <div class="form-text">
                To change the status, submit a Progress Update below.
            </div>
        </div>

        <!-- Description / Progress Notes -->
        <div class="mb-3">
            <label for="description" class="form-label">
                Work Description
            </label>

            <textarea
                id="description"
                name="description"
                class="form-control"
                rows="4"
                maxlength="5000"
            ><?= $escape($work['description'] ?? '') ?></textarea>

            <?php if (!$isAdmin): ?>
                <div class="form-text">
                    Update the current status and describe the progress
                    made on this Work.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin): ?>

            <!-- Planned Date -->
            <div class="mb-3">
                <label for="planned_date" class="form-label">
                    Planned Date
                </label>

                <input
                    type="date"
                    id="planned_date"
                    name="planned_date"
                    class="form-control"
                    value="<?= $escape($work['planned_date'] ?? '') ?>"
                >
            </div>

            <!-- Actual Date -->
            <div class="mb-3">
                <label for="actual_date" class="form-label">
                    Actual Date
                </label>

                <input
                    type="date"
                    id="actual_date"
                    name="actual_date"
                    class="form-control"
                    value="<?= $escape($work['actual_date'] ?? '') ?>"
                >
            </div>

            <!-- Assignment -->
            <div class="row g-3 mb-4">

                <div class="col-md-6">
                    <label
                        for="assignment_type"
                        class="form-label"
                    >
                        Assign To
                    </label>

                    <select
                        id="assignment_type"
                        name="assignment_type"
                        class="form-select"
                        required
                    >
                        <option value="">
                            Select Assignment Type
                        </option>

                        <option
                            value="user"
                            <?= $assignmentType === 'user'
                                ? 'selected'
                                : '' ?>
                        >
                            User
                        </option>

                        <option
                            value="team"
                            <?= $assignmentType === 'team'
                                ? 'selected'
                                : '' ?>
                        >
                            Team
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label
                        for="assignment_id"
                        class="form-label"
                    >
                        Responsible User / Team
                    </label>

                    <select
                        id="assignment_id"
                        name="assignment_id"
                        class="form-select"
                        required
                    >
                        <option value="">
                            Select Responsible
                        </option>

                        <?php foreach ($users as $user): ?>
                            <option
                                value="<?= (int) $user['id'] ?>"
                                data-type="user"
                                <?= $assignmentType === 'user'
                                    && (string) $assignmentId === (string) $user['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= $escape($user['name']) ?> (User)
                            </option>
                        <?php endforeach; ?>

                        <?php foreach ($teams as $team): ?>
                            <option
                                value="<?= (int) $team['id'] ?>"
                                data-type="team"
                                <?= $assignmentType === 'team'
                                    && (string) $assignmentId === (string) $team['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= $escape($team['name']) ?> (Team)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

        <?php else: ?>

            <!-- Staff can see the responsible person/team,
                 but cannot change the assignment. -->
            <div class="mb-3">
                <label class="form-label">
                    Responsible
                </label>

                <?php
                $responsible = 'Unassigned';

                if (($work['user_id'] ?? null) !== null) {
                    $responsible = 'User: '
                        . ($work['assigned_user_name'] ?? '');
                } elseif (($work['team_id'] ?? null) !== null) {
                    $responsible = 'Team: '
                        . ($work['assigned_team_name'] ?? '');
                }
                ?>

                <input
                    type="text"
                    class="form-control"
                    value="<?= $escape($responsible) ?>"
                    readonly
                >
            </div>

        <?php endif; ?>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Save Changes
        </button>

        <a
            href="/?page=works&amp;action=show&amp;id=<?= (int) $work['id'] ?>"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </form>
    <?php endif; ?>
    <!-- Progress form for Admin and assigned Staff -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0">Update Work Progress</h5>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="/?page=works&amp;action=updateProgress&amp;id=<?= (int) $work['id'] ?>"
            >
                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label for="progress_status" class="form-label">
                        Work Status
                    </label>

                    <select
                        id="progress_status"
                        name="status"
                        class="form-select"
                        required
                    >
                        <?php foreach ([
                            'planned' => 'Planned',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed'
                        ] as $value => $label): ?>
                            <option
                                value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $work['status'] === $value ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="progress_actual_date" class="form-label">
                        Actual Completion Date
                    </label>

                    <input
                        type="date"
                        id="progress_actual_date"
                        name="actual_date"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            (string) ($work['actual_date'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                    <div class="form-text">
                        Required when status is Completed.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="progress_note" class="form-label">
                        Progress Notes
                    </label>

                    <textarea
                        id="progress_note"
                        name="note"
                        class="form-control"
                        rows="4"
                        maxlength="2000"
                        required
                        placeholder="Describe what has been done..."
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Progress
                </button>
            </form>
        </div>
    </div>

</main>


<?php
require __DIR__ . '/../../layouts/footer.php';
?>
