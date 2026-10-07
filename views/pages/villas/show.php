<?php

declare(strict_types=1);

$pageTitle = 'Villa Details';
$activePage = 'villas';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

function villaEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$statusLabel = ucfirst(
    str_replace('_', ' ', (string) $villa['status'])
);
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h2 class="mb-1">
                    <?= villaEscape($villa['name']) ?>
                </h2>

                <p class="text-muted mb-0">
                    Villa details
                </p>
            </div>

            <a
                href="/?page=villas"
                class="btn btn-outline-secondary"
            >
                Back to Villas
            </a>

        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">

                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ((array) $fieldErrors as $error): ?>

                        <div>
                            <?= villaEscape($error) ?>
                        </div>

                    <?php endforeach; ?>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">
                        Villa Name
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape($villa['name']) ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">
                        Status
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape($statusLabel) ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">
                        Group
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape($villa['group_name']) ?>
                    </div>
                </div>

                <hr>
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">
                        Typology
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape($villa['typology_name']) ?>
                    </div>
                </div>
                

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">
                        Description
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape(
                            $villa['description'] ?: '-'
                        ) ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">
                        Planned Date
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape(
                            $villa['planned_date'] ?: '-'
                        ) ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-4 fw-semibold">
                        Actual Date
                    </div>

                    <div class="col-md-8">
                        <?= villaEscape(
                            $villa['actual_date'] ?: '-'
                        ) ?>
                    </div>
                </div>

                <div class="d-flex gap-2">

                    <?php if (
                        Authorization::can('villas.update')
                    ): ?>

                        <a
                            href="/?page=villas&action=edit&id=<?= (int) $villa['id'] ?>"
                            class="btn btn-primary"
                        >
                            Edit Villa
                        </a>

                    <?php endif; ?>

                    <?php if (
                        Authorization::can('villas.delete')
                    ): ?>

                        <form
                            method="POST"
                            action="/?page=villas&action=delete&id=<?= (int) $villa['id'] ?>"
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this villa?');"
                        >
                            <?= Csrf::field() ?>

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >
                                Delete Villa
                            </button>
                        </form>

                    <?php endif; ?>

                </div>

            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>