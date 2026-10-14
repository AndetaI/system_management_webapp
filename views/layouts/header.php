<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Barkea Resort' ?></title>

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">

        <!-- Mobile menu button -->
        <button
            class="navbar-toggler d-md-none me-2"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileSidebar"
            aria-controls="mobileSidebar"
            aria-label="Open navigation menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand fw-bold me-auto" href="/">
            Barkea Resort
        </a>

        
        <?php
        $currentUser = SessionManager::user();

        $displayName = $currentUser['name']
            ?? $currentUser['username']
            ?? 'User';

        $displayRole = ucfirst((string) ($currentUser['role'] ?? ''));
        ?>


        <div class="d-flex align-items-center gap-3">

            <!-- Current user profile -->
            <?php if ($currentUser !== null): ?>
                <div class="d-flex align-items-center gap-2 text-light">

                    <span class="d-inline-flex align-items-center justify-content-center
                                rounded-circle bg-secondary"
                        style="width: 36px; height: 36px;"
                        aria-hidden="true">
                        &#128100;
                    </span>

                    <div class="d-flex flex-column lh-sm">
                        <span class="fw-semibold">
                            <?= htmlspecialchars(
                                (string) $displayName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                        <small class="text-white-50">
                            <?= htmlspecialchars(
                                $displayRole,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </small>
                    </div>

                </div>
            <?php endif; ?>

            <!-- Logout -->
            <form method="POST"
                action="/?page=logout"
                class="d-inline">

                <?= Csrf::field() ?>

                <button type="submit"
                        class="btn btn-link text-light text-decoration-none p-0">
                    Logout
                </button>

            </form>

    </div>
</nav>

<div class="d-flex app-wrapper">