<!-- =========================
     Desktop Sidebar
     ========================= -->

<aside class="sidebar bg-light border-end p-3 d-none d-md-block">

    <p class="text-uppercase text-muted mb-3">
        Management
    </p>

    <nav class="nav flex-column">

        <?php if (Authorization::can('dashboard.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
                href="/"
            >
                Dashboard
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('villas.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'villas' ? 'active' : '' ?>"
                href="/?page=villas"
            >
                Villas
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('works.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'works' ? 'active' : '' ?>"
                href="/?page=works"
            >
                Works
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('materials.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'materials' ? 'active' : '' ?>"
                href="/?page=materials"
            >
                Materials
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('stock.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'stock' ? 'active' : '' ?>"
                href="/?page=stock"
            >
                Warehouse / Stock
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('suppliers.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>"
                href="/?page=suppliers"
            >
                Suppliers
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('teams.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'teams' ? 'active' : '' ?>"
                href="/?page=teams"
            >
                Teams
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('users.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>"
                href="/?page=users"
            >
                Users
            </a>
        <?php endif; ?>

        <?php if (Authorization::can('reports.view')): ?>
            <a
                class="nav-link <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>"
                href="/?page=reports"
            >
                Reports
            </a>
        <?php endif; ?>

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

            <?php if (Authorization::can('dashboard.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
                    href="/"
                >
                    Dashboard
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('villas.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'villas' ? 'active' : '' ?>"
                    href="/?page=villas"
                >
                    Villas
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('works.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'works' ? 'active' : '' ?>"
                    href="/?page=works"
                >
                    Works
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('materials.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'materials' ? 'active' : '' ?>"
                    href="/?page=materials"
                >
                    Materials
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('stock.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'stock' ? 'active' : '' ?>"
                    href="/?page=stock"
                >
                    Warehouse / Stock
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('suppliers.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>"
                    href="/?page=suppliers"
                >
                    Suppliers
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('teams.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'teams' ? 'active' : '' ?>"
                    href="/?page=teams"
                >
                    Teams
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('users.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>"
                    href="/?page=users"
                >
                    Users
                </a>
            <?php endif; ?>

            <?php if (Authorization::can('reports.view')): ?>
                <a
                    class="nav-link <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>"
                    href="/?page=reports"
                >
                    Reports
                </a>
            <?php endif; ?>

        </nav>

    </div>

</div>