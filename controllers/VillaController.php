<?php

declare(strict_types=1);

class VillaController
{
    public function __construct(
        private VillaService $villaService,
        private VillaRepository $villaRepository,
        private VillaTypologyRepository $villaTypologyRepository
    ) {
    }

    /*
     * Display all villas.
     */

    public function index(): void
    {
        Authorization::requirePermission('villas.view');

        $search = trim((string) ($_GET['search'] ?? ''));

        $groupId = filter_var(
            $_GET['group_id'] ?? 0,
            FILTER_VALIDATE_INT
        );

        $typologyId = filter_var(
            $_GET['typology_id'] ?? 0,
            FILTER_VALIDATE_INT
        );

        if ($groupId === false || $groupId < 0) {
            $groupId = 0;
        }

        if ($typologyId === false || $typologyId < 0) {
            $typologyId = 0;
        }

        $status = trim((string) ($_GET['status'] ?? ''));

        $groups = $this->villaRepository->findFilterGroups();
        $typologies = $this->villaRepository->findFilterTypologies();
        $statuses = $this->villaRepository->findFilterStatuses();


        $typologyName = trim((string) ($_GET['typology'] ?? ''));

        if (
            $typologyName !== ''
            && !in_array($typologyName, $typologies, true)
        ) {
            $typologyName = '';
        }
        // Accept only existing group IDs.
        $validGroupIds = array_map(
            'intval',
            array_column($groups, 'id')
        );

        if ($groupId > 0 && !in_array($groupId, $validGroupIds, true)) {
            $groupId = 0;
        }



        // Accept only existing status values.
        if ($status !== '' && !in_array($status, $statuses, true)) {
            $status = '';
        }

        $villas = $this->villaRepository->search(
            $search,
            $groupId,
            $typologyName,
            $status
        );

        require __DIR__ . '/../views/pages/villas/index.php';
    }

    /*
     * Display one villa.
     */
    public function show(int $villaId): void
    {
        Authorization::requirePermission('villas.view');

        $villa = $this->villaRepository->findById($villaId);

        if ($villa === null) {
            http_response_code(404);
            echo 'Villa not found.';
            return;
        }

        require __DIR__ . '/../views/pages/villas/show.php';
    }

    /*
     * Display the create form.
     */
    public function create(): void
    {
        Authorization::requirePermission('villas.create');

        $typologies = $this->villaTypologyRepository->findAll();

        $errors = [];

        require __DIR__ . '/../views/pages/villas/create.php';
    }

    /*
     * Process creation.
     */
    public function store(): void
    {
        Authorization::requirePermission('villas.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        Csrf::requireValidToken();

        $result = $this->villaService->create($_POST);

        if ($result['success']) {
            header(
                'Location: /?page=villas&action=show&id='
                . $result['id']
            );
            exit;
        }

        $errors = $result['errors'];

        $typologies = $this->villaTypologyRepository->findAll();

        require __DIR__ . '/../views/pages/villas/create.php';
    }

    /*
     * Display the edit form.
     */
    public function edit(int $villaId): void
    {
        Authorization::requirePermission('villas.update');

        $villa = $this->villaRepository->findById($villaId);

        if ($villa === null) {
            http_response_code(404);
            echo 'Villa not found.';
            return;
        }

        $typologies = $this->villaTypologyRepository->findAll();

        $errors = [];

        require __DIR__ . '/../views/pages/villas/edit.php';
    }

    /*
     * Process update.
     */
    public function update(int $villaId): void
    {
        Authorization::requirePermission('villas.update');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        Csrf::requireValidToken();

        $result = $this->villaService->update(
            $villaId,
            $_POST
        );

        if ($result['success']) {
            header(
                'Location: /?page=villas&action=show&id='
                . $villaId
            );
            exit;
        }

        $errors = $result['errors'];

        $villa = array_merge(
            $this->villaRepository->findById($villaId) ?? [],
            $_POST
        );

        $typologies = $this->villaTypologyRepository->findAll();

        require __DIR__ . '/../views/pages/villas/edit.php';
    }

    /*
     * Delete a villa.
     */
    public function delete(int $villaId): void
    {
        Authorization::requirePermission('villas.delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        Csrf::requireValidToken();

        $result = $this->villaService->delete($villaId);

        if ($result['success']) {
            header('Location: /?page=villas');
            exit;
        }

        $villa = $this->villaRepository->findById($villaId);

        if ($villa === null) {
            http_response_code(404);
            echo 'Villa not found.';
            return;
        }

        $errors = $result['errors'];

        require __DIR__ . '/../views/pages/villas/show.php';
    }
}