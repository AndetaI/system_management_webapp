<?php

declare(strict_types=1);

return [

    'admin' => [
        'dashboard.view',

        'villas.view',
        'villas.create',
        'villas.update',
        'villas.delete',

        'works.view',
        'works.create',
        'works.update',
        'works.delete',

        'materials.view',
        'materials.create',
        'materials.update',
        'materials.delete',

        'stock.view',
        'stock.update',

        'suppliers.view',
        'suppliers.create',
        'suppliers.update',
        'suppliers.delete',

        'teams.view',
        'teams.create',
        'teams.update',
        'teams.delete',

        'users.view',
        'users.create',
        'users.update',
        'users.delete',

        'reports.view',
    ],

    'manager' => [
        'dashboard.view',

        'villas.view',

        'works.view',

        'materials.view',

        'stock.view',

        'suppliers.view',

        'teams.view',

        'reports.view',
    ],

    'staff' => [
        'dashboard.view',

        'villas.view',

        'works.view',
        'works.update',

        'materials.view',
    ],
];
