
<?php
$pageTitle = 'Material Details | Barkea Resort';
$activePage = 'materials';
$escape = static fn($v): string =>
    htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

require __DIR__ . '/../../layouts/header.php';
require __DIR__ . '/../../layouts/sidebar.php';
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Material Details</h1>
        <a href="/?page=materials" class="btn btn-secondary">
            Back to Materials
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <tr>
                    <th>Name</th>
                    <td><?= $escape($material['name']) ?></td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td><?= $escape($material['category']) ?></td>
                </tr>
                <tr>
                    <th>Unit</th>
                    <td><?= $escape($material['unit']) ?></td>
                </tr>
                <tr>
                    <th>Price</th>
                    <td>€<?= number_format((float) $material['price'], 2) ?></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><?= $escape($material['description'] ?? '-') ?></td>
                </tr>
            </table>

            <?php if (SessionManager::role() === 'admin'): ?>
                <div class="d-flex gap-2">
                    <a href="/?page=materials&amp;action=edit&amp;id=<?= (int) $material['id'] ?>"
                       class="btn btn-primary">
                        Edit Material
                    </a>

                    <form method="POST"
                          action="/?page=materials&amp;action=delete&amp;id=<?= (int) $material['id'] ?>"
                          data-confirm="Delete this material?">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-danger">
                            Delete
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
