<?php

declare(strict_types=1);

require_once __DIR__ . '/../util/Validator.php';

echo "Starting Validator tests..." . PHP_EOL;


// --------------------------------------------------
// TEST 1: VALID DATA
// --------------------------------------------------

$validator = new Validator();

$validator
    ->required(
        'name',
        'Cement',
        'Name is required.'
    )
    ->email(
        'email',
        'test@example.com',
        'Email is invalid.'
    )
    ->positive(
        'price',
        10.50,
        'Price must be greater than zero.'
    )
    ->nonNegative(
        'stock',
        0,
        'Stock cannot be negative.'
    )
    ->in(
        'status',
        'planned',
        ['planned', 'in_progress', 'completed'],
        'Invalid status.'
    )
    ->date(
        'planned_date',
        '2026-10-02',
        'Invalid date.'
    );

if (!$validator->passes()) {
    throw new RuntimeException(
        'Valid data was incorrectly rejected.'
    );
}

echo "VALID DATA test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 2: REQUIRED FIELD
// --------------------------------------------------

$validator = new Validator();

$validator->required(
    'name',
    '',
    'Name is required.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Empty required field was accepted.'
    );
}

if ($validator->first('name') !== 'Name is required.') {
    throw new RuntimeException(
        'Required field message is incorrect.'
    );
}

echo "REQUIRED FIELD test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 3: INVALID EMAIL
// --------------------------------------------------

$validator = new Validator();

$validator->email(
    'email',
    'not-an-email',
    'Email is invalid.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Invalid email was accepted.'
    );
}

echo "EMAIL test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 4: MINIMUM LENGTH
// --------------------------------------------------

$validator = new Validator();

$validator->minLength(
    'username',
    'ab',
    3,
    'Username must contain at least 3 characters.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Short username was accepted.'
    );
}

echo "MIN LENGTH test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 5: MAXIMUM LENGTH
// --------------------------------------------------

$validator = new Validator();

$validator->maxLength(
    'username',
    'abcdefghijk',
    10,
    'Username cannot exceed 10 characters.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Long username was accepted.'
    );
}

echo "MAX LENGTH test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 6: NUMERIC
// --------------------------------------------------

$validator = new Validator();

$validator->numeric(
    'price',
    'abc',
    'Price must be numeric.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Non-numeric value was accepted.'
    );
}

echo "NUMERIC test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 7: POSITIVE NUMBER
// --------------------------------------------------

$validator = new Validator();

$validator->positive(
    'quantity',
    -5,
    'Quantity must be greater than zero.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Negative quantity was accepted.'
    );
}

echo "POSITIVE NUMBER test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 8: NON-NEGATIVE NUMBER
// --------------------------------------------------

$validator = new Validator();

$validator->nonNegative(
    'stock',
    -1,
    'Stock cannot be negative.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Negative stock was accepted.'
    );
}

echo "NON-NEGATIVE NUMBER test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 9: ALLOWED VALUES
// --------------------------------------------------

$validator = new Validator();

$validator->in(
    'status',
    'invalid_status',
    ['planned', 'in_progress', 'completed'],
    'Invalid status.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Invalid status was accepted.'
    );
}

echo "ALLOWED VALUES test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 10: INVALID DATE
// --------------------------------------------------

$validator = new Validator();

$validator->date(
    'planned_date',
    '2026-02-30',
    'Invalid date.'
);

if (!$validator->fails()) {
    throw new RuntimeException(
        'Invalid date was accepted.'
    );
}

echo "DATE test passed." . PHP_EOL;


// --------------------------------------------------
// TEST 11: MULTIPLE ERRORS
// --------------------------------------------------

$validator = new Validator();

$validator
    ->required(
        'name',
        '',
        'Name is required.'
    )
    ->email(
        'email',
        'wrong-email',
        'Email is invalid.'
    )
    ->positive(
        'price',
        -20,
        'Price must be greater than zero.'
    );

$errors = $validator->errors();

if (
    !isset($errors['name']) ||
    !isset($errors['email']) ||
    !isset($errors['price'])
) {
    throw new RuntimeException(
        'Multiple validation errors were not stored correctly.'
    );
}

echo "MULTIPLE ERRORS test passed." . PHP_EOL;


echo "All Validator tests passed." . PHP_EOL;
