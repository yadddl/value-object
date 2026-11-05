<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Examples\Person;

test('creates valid person', function () {
    $person = Person::create('John Doe', 'john@example.com', 30);

    expect($person)->toBeInstanceOf(Person::class)
        ->and($person->name)->toBe('John Doe')
        ->and($person->email)->toBe('john@example.com')
        ->and($person->age)->toBe(30);
});

test('returns errors bag for invalid name', function () {
    $result = Person::create('', 'john@example.com', 30);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getFieldsWithErrors())->toContain('name');
});

test('returns errors bag for invalid email', function () {
    $result = Person::create('John Doe', 'invalid-email', 30);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getFieldsWithErrors())->toContain('email');
});

test('returns errors bag for invalid age', function () {
    $result = Person::create('John Doe', 'john@example.com', -5);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getFieldsWithErrors())->toContain('age');
});

test('accumulates errors from multiple invalid fields', function () {
    $result = Person::create('', 'invalid-email', -5);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getFieldCount())->toBe(3) // name, email, age all have errors
        ->and($result->getFieldsWithErrors())->toContain('name')
        ->and($result->getFieldsWithErrors())->toContain('email')
        ->and($result->getFieldsWithErrors())->toContain('age');
});

test('can get errors for specific field', function () {
    $result = Person::create('', 'john@example.com', 30);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class);

    $nameErrors = $result->getErrorsForField('name');
    expect($nameErrors)->not->toBeEmpty();

    $emailErrors = $result->getErrorsForField('email');
    expect($emailErrors)->toBeEmpty(); // Email is valid
});

test('returns empty errors for valid person', function () {
    $person = Person::create('Jane Smith', 'jane@example.com', 28);

    expect($person)->toBeInstanceOf(Person::class);
});

test('organizes errors by field name', function () {
    $result = Person::create('A', 'bad-email', 200);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class);

    $errorsByField = $result->getErrorsByField();
    expect($errorsByField)->toHaveKeys(['name', 'email', 'age']);

    // Each field should have at least one error
    foreach (['name', 'email', 'age'] as $field) {
        expect($errorsByField[$field])->not->toBeEmpty();
    }
});
