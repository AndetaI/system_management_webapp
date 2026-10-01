<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Barkea Resort' ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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

        <a class="text-light text-decoration-none" href="#">
            Logout
        </a>

    </div>
</nav>

<div class="d-flex app-wrapper">