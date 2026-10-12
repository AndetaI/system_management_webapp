<?php

declare(strict_types=1);

class DashboardController
{
    public function __construct(
        private DashboardRepository $dashboardRepository
    ) {
    }

    public function index(): void
    {
        Authorization::requirePermission('dashboard.view');

        $summary = $this->dashboardRepository->getSummary();

        $worksByStatus = $this->dashboardRepository
            ->getWorksByStatus();

        $recentWorks = $this->dashboardRepository
            ->getRecentWorks();

        require __DIR__ . '/../views/pages/dashboard/index.php';
    }
}
