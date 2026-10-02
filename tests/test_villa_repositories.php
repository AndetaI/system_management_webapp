<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../repositories/VillaGroupRepository.php';
require_once __DIR__ . '/../repositories/VillaTypologyRepository.php';
require_once __DIR__ . '/../repositories/VillaRepository.php';

try {
    $pdo = Database::getConnection();

    $groupRepository = new VillaGroupRepository($pdo);
    $typologyRepository = new VillaTypologyRepository($pdo);
    $villaRepository = new VillaRepository($pdo);

    $suffix = (string) time();


    // 1. CREATE VILLA GROUP
    $groupId = $groupRepository->create([
        'name' => 'Test Group ' . $suffix,
        'description' => 'Temporary group for repository testing',
    ]);

    echo "GROUP CREATE successful. ID: {$groupId}" . PHP_EOL;


    // 2. CREATE TYPOLOGY
    $typologyId = $typologyRepository->create([
        'group_id' => $groupId,
        'name' => 'Test Typology ' . $suffix,
        'description' => 'Temporary typology for repository testing',
        'floors' => 2,
        'area' => 150.50,
    ]);

    echo "TYPOLOGY CREATE successful. ID: {$typologyId}" . PHP_EOL;


    // 3. CREATE VILLA
    $villaId = $villaRepository->create([
        'typology_id' => $typologyId,
        'name' => 'TEST-' . $suffix,
        'status' => 'planned',
        'description' => 'Temporary villa for repository testing',
        'planned_date' => '2026-10-01',
        'actual_date' => null,
    ]);

    echo "VILLA CREATE successful. ID: {$villaId}" . PHP_EOL;


    // 4. READ VILLA GROUP
    $group = $groupRepository->findById($groupId);

    if ($group === null) {
        throw new RuntimeException('Villa group could not be found.');
    }

    echo "GROUP READ successful." . PHP_EOL;


    // 5. READ TYPOLOGY
    $typology = $typologyRepository->findById($typologyId);

    if ($typology === null) {
        throw new RuntimeException('Villa typology could not be found.');
    }

    echo "TYPOLOGY READ successful." . PHP_EOL;


    // 6. READ VILLA
    $villa = $villaRepository->findById($villaId);

    if ($villa === null) {
        throw new RuntimeException('Villa could not be found.');
    }

    echo "VILLA READ successful. Villa: {$villa['name']}" . PHP_EOL;


    // 7. UPDATE VILLA
    $villaRepository->update($villaId, [
        'typology_id' => $typologyId,
        'name' => 'TEST-' . $suffix,
        'status' => 'in_progress',
        'description' => 'Updated repository test villa',
        'planned_date' => '2026-10-01',
        'actual_date' => null,
    ]);

    $updatedVilla = $villaRepository->findById($villaId);

    if (
        $updatedVilla === null ||
        $updatedVilla['status'] !== 'in_progress' ||
        $updatedVilla['description'] !== 'Updated repository test villa'
    ) {
        throw new RuntimeException('Villa was not updated correctly.');
    }

    echo "VILLA UPDATE successful." . PHP_EOL;


    // 8. TEST findAll()
    $groups = $groupRepository->findAll();
    $typologies = $typologyRepository->findAll();
    $villas = $villaRepository->findAll();

    echo "READ ALL successful." . PHP_EOL;
    echo "Groups found: " . count($groups) . PHP_EOL;
    echo "Typologies found: " . count($typologies) . PHP_EOL;
    echo "Villas found: " . count($villas) . PHP_EOL;


    // 9. DELETE IN CORRECT FOREIGN-KEY ORDER

    $villaRepository->delete($villaId);

    if ($villaRepository->findById($villaId) !== null) {
        throw new RuntimeException('Villa was not deleted.');
    }

    echo "VILLA DELETE successful." . PHP_EOL;


    $typologyRepository->delete($typologyId);

    if ($typologyRepository->findById($typologyId) !== null) {
        throw new RuntimeException('Villa typology was not deleted.');
    }

    echo "TYPOLOGY DELETE successful." . PHP_EOL;


    $groupRepository->delete($groupId);

    if ($groupRepository->findById($groupId) !== null) {
        throw new RuntimeException('Villa group was not deleted.');
    }

    echo "GROUP DELETE successful." . PHP_EOL;

    echo "All villa repository tests passed." . PHP_EOL;

} catch (Throwable $exception) {
    echo "Villa repository test failed." . PHP_EOL;
}
