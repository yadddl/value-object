<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Examples\Age;

test('creates valid age', function () {
    $age = Age::create(25);

    expect($age)->toBeInstanceOf(Age::class)
        ->and($age->value)->toBe(25);
});

test('returns errors bag for negative age', function () {
    $result = Age::create(-5);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->count())->toBe(1)
        ->and($result->getErrors()[0]->message)->toBe('Age cannot be negative');
});

test('returns errors bag for age exceeding maximum', function () {
    $result = Age::create(200);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->count())->toBe(1)
        ->and($result->getErrors()[0]->message)->toBe('Age cannot exceed 150');
});

test('validates age at minimum boundary', function () {
    $age = Age::create(0);

    expect($age)->toBeInstanceOf(Age::class)
        ->and($age->value)->toBe(0);
});

test('validates age at maximum boundary', function () {
    $age = Age::create(150);

    expect($age)->toBeInstanceOf(Age::class)
        ->and($age->value)->toBe(150);
});
