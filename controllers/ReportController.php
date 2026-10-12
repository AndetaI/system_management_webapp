<?php

declare(strict_types=1);

class ReportController
{
    public function __construct(
        private ReportRepository $reportRepository
    ) {
    }

    public function index(): void
    {
        // Only Admin and Manager have reports.view
        Authorization::requirePermission('reports.view');

        $summary = $this->reportRepository->getSummary();

        $villaCosts = $this->reportRepository
            ->getMaterialCostsByVilla();

        require __DIR__ . '/../views/pages/reports/index.php';
    }

    
    public function villaDetails(): void
    {
        Authorization::requirePermission('reports.view');

        $villaId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if ($villaId === false || $villaId === null || $villaId <= 0) {
            http_response_code(400);
            echo 'Invalid villa ID.';
            return;
        }

        $villa = $this->reportRepository->findVillaById($villaId);

        if ($villa === null) {
            http_response_code(404);
            echo 'Villa not found.';
            return;
        }

        $materialDetails = $this->reportRepository
            ->getMaterialUsageDetails($villaId);

        require __DIR__ . '/../views/pages/reports/villa-details.php';
    }

}
