
<?php

$pageTitle = 'Add Supplier | Barkea Resort';
$activePage = 'suppliers';

$escape = static fn($value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <h1>Add Supplier</h1>
    <p class="text-muted">Register a new material supplier.</p>

    <div class="card">
        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= $escape($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="/?page=suppliers&amp;action=store">

                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label" for="name">
                        Supplier Name *
                    </label>
                    <input
                        class="form-control"
                        type="text"
                        id="name"
                        name="name"
                        maxlength="150"
                        value="<?= $escape($old['name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="contact_person">
                        Contact Person
                    </label>
                    <input
                        class="form-control"
                        type="text"
                        id="contact_person"
                        name="contact_person"
                        value="<?= $escape($old['contact_person'] ?? '') ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">
                        Email
                    </label>
                    <input
                        class="form-control"
                        type="email"
                        id="email"
                        name="email"
                        value="<?= $escape($old['email'] ?? '') ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">
                        Phone
                    </label>
                    <input
                        class="form-control"
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?= $escape($old['phone'] ?? '') ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">
                        Address
                    </label>
                    <textarea
                        class="form-control"
                        id="address"
                        name="address"
                        rows="2"
                    ><?= $escape($old['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Supplier
                </button>

                <a href="/?page=suppliers"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>
        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
