
<?php

$pageTitle = 'Edit Supplier | Barkea Resort';
$activePage = 'suppliers';

$escape = static fn($value): string =>
    htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$id = (int) $supplier['id'];

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';

?>

<main class="main-content flex-grow-1 p-4">

    <h1>Edit Supplier</h1>

    <div class="card mb-4">
        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= $escape($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="/?page=suppliers&amp;action=update&amp;id=<?= $id ?>">

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
                        value="<?= $escape($supplier['name']) ?>"
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
                        value="<?= $escape($supplier['contact_person']) ?>"
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
                        value="<?= $escape($supplier['email']) ?>"
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
                        value="<?= $escape($supplier['phone']) ?>"
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
                    ><?= $escape($supplier['address']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>

                <a href="/?page=suppliers"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </form>
        </div>
    </div>

    <div class="card border-danger">
        <div class="card-body">
            <h2 class="h5">Delete Supplier</h2>
            <p class="text-muted">
                Suppliers referenced by materials cannot be deleted.
            </p>

            <form method="POST"
                  action="/?page=suppliers&amp;action=delete&amp;id=<?= $id ?>"
                  onsubmit="return confirm('Delete this supplier?');">

                <?= Csrf::field() ?>
                
                <button type="submit" class="btn btn-danger">
                    Delete Supplier
                </button>
            </form>
        </div>
    </div>

</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
