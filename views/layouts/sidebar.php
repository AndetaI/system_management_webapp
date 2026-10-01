<!-- =========================
     Desktop Sidebar
     ========================= -->

<aside class="sidebar bg-light border-end p-3 d-none d-md-block">

    <p class="text-uppercase text-muted mb-3">
        Management
    </p>

    <nav class="nav flex-column">

        <a
            class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
            href="/"
        >
            Dashboard
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'villas' ? 'active' : '' ?>"
            href="/?page=villas"
        >
            Villas
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'works' ? 'active' : '' ?>"
            href="/?page=works"
        >
            Works
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'materials' ? 'active' : '' ?>"
            href="/?page=materials"
        >
            Materials
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'stock' ? 'active' : '' ?>"
            href="/?page=stock"
        >
            Warehouse / Stock
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>"
            href="/?page=suppliers"
        >
            Suppliers
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'teams' ? 'active' : '' ?>"
            href="/?page=teams"
        >
            Teams
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>"
            href="/?page=users"
        >
            Users
        </a>

        <a
            class="nav-link <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>"
            href="/?page=reports"
        >
            Reports
        </a>

    </nav>

</aside>


<!-- =========================
     Mobile Sidebar
     ========================= -->

<div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="mobileSidebar"
    aria-labelledby="mobileSidebarLabel"
>

    <div class="offcanvas-header">

        <h2 class="offcanvas-title h5" id="mobileSidebarLabel">
            Management
        </h2>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="Close"
        ></button>

    </div>


    <div class="offcanvas-body">

        <nav class="nav flex-column">

            <a
                class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
                href="/"
            >
                Dashboard
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'villas' ? 'active' : '' ?>"
                href="/?page=villas"
            >
                Villas
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'works' ? 'active' : '' ?>"
                href="/?page=works"
            >
                Works
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'materials' ? 'active' : '' ?>"
                href="/?page=materials"
            >
                Materials
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'stock' ? 'active' : '' ?>"
                href="/?page=stock"
            >
                Warehouse / Stock
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>"
                href="/?page=suppliers"
            >
                Suppliers
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'teams' ? 'active' : '' ?>"
                href="/?page=teams"
            >
                Teams
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>"
                href="/?page=users"
            >
                Users
            </a>

            <a
                class="nav-link <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>"
                href="/?page=reports"
            >
                Reports
            </a>

        </nav>

    </div>

</div>