<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Examples\Name;

test('creates valid name', function () {
    $name = Name::create('John Doe');

    expect($name)->toBeInstanceOf(Name::class)
        ->and($name->value)->toBe('John Doe');
});

test('returns errors bag for name too short', function () {
    $result = Name::create('A');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getErrors()[0]->getMessage())->toContain('2 characters');
});

test('returns errors bag for name too long', function () {
    $longName = str_repeat('A', 151);
    $result = Name::create($longName);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getErrors()[0]->getMessage())->toContain('150 characters');
});

test('validates name at minimum length boundary', function () {
    $name = Name::create('AB');

    expect($name)->toBeInstanceOf(Name::class)
        ->and($name->value)->toBe('AB');
});

test('validates name at maximum length boundary', function () {
    $longName = str_repeat('A', 150);
    $name = Name::create($longName);

    expect($name)->toBeInstanceOf(Name::class)
        ->and($name->value)->toBe($longName);
});
