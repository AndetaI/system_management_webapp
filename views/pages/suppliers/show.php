<?php

declare(strict_types=1);

$pageTitle = 'Supplier Details | Barkea Resort';
$activePage = 'suppliers';

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

$supplierEscape = static function (mixed $value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <?= $supplierEscape($supplier['name']) ?>
                </h2>
                <p class="text-muted mb-0">Supplier details</p>
            </div>

            <a href="/?page=suppliers" class="btn btn-outline-secondary">
                Back to Suppliers
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ((array) $fieldErrors as $error): ?>
                        <div><?= $supplierEscape($error) ?></div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <p><strong>Name:</strong>
                    <?= $supplierEscape($supplier['name']) ?>
                </p>

                <p><strong>Email:</strong>
                    <?= $supplierEscape($supplier['email'] ?: '-') ?>
                </p>

                <p><strong>Phone:</strong>
                    <?= $supplierEscape($supplier['phone'] ?: '-') ?>
                </p>

                <p><strong>Address:</strong>
                    <?= $supplierEscape($supplier['address'] ?: '-') ?>
                </p>

                <p><strong>Contact Person:</strong>
                    <?= $supplierEscape($supplier['contact_person'] ?: '-') ?>
                </p>

                <div class="d-flex gap-2 mt-4">

                    <?php if (Authorization::can('suppliers.update')): ?>
                        <a
                            href="/?page=suppliers&amp;action=edit&amp;id=<?= (int) $supplier['id'] ?>"
                            class="btn btn-primary"
                        >
                            Edit Supplier
                        </a>
                    <?php endif; ?>

                    <?php if (Authorization::can('suppliers.delete')): ?>
                        <form
                            method="POST"
                            action="/?page=suppliers&amp;action=delete&amp;id=<?= (int) $supplier['id'] ?>"
                            class="d-inline"
                            data-confirm="Are you sure you want to delete this supplier?"
                        >
                            <?= Csrf::field() ?>

                            <button type="submit" class="btn btn-danger">
                                Delete Supplier
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>