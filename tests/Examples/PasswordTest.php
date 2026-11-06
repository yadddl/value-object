<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Examples\Password;

test('creates valid password with all requirements', function () {
    $password = Password::create('MyP@ssw0rd');

    expect($password)->toBeInstanceOf(Password::class)
        ->and($password->value)->not->toBe('MyP@ssw0rd') // Should be encrypted
        ->and(strlen($password->value))->toBeGreaterThan(50); // Hash is long
});

test('verifies correct password', function () {
    $password = Password::create('MyP@ssw0rd');

    expect($password->verify('MyP@ssw0rd'))->toBeTrue();
});

test('rejects incorrect password', function () {
    $password = Password::create('MyP@ssw0rd');

    expect($password->verify('WrongPassword'))->toBeFalse();
});

test('returns errors for password too short', function () {
    $result = Password::create('Short1!');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getErrors()[0]->getMessage())->toContain('at least 8 characters');
});

test('returns errors for password too long', function () {
    $result = Password::create('ThisIsAVeryLongPassword123!@#');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();

    $messages = array_map(fn ($e) => $e->getMessage(), $result->getErrors());
    expect($messages)->toContain('Password cannot exceed 20 characters');
});

test('returns errors for password without uppercase', function () {
    $result = Password::create('mypassword123!');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();

    $messages = array_map(fn ($e) => $e->getMessage(), $result->getErrors());
    expect(implode(' ', $messages))->toContain('uppercase letter');
});

test('returns errors for password without lowercase', function () {
    $result = Password::create('MYPASSWORD123!');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();

    $messages = array_map(fn ($e) => $e->getMessage(), $result->getErrors());
    expect(implode(' ', $messages))->toContain('lowercase letter');
});

test('returns errors for password without number', function () {
    $result = Password::create('MyPassword!');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();

    $messages = array_map(fn ($e) => $e->getMessage(), $result->getErrors());
    expect(implode(' ', $messages))->toContain('number');
});

test('returns errors for password without special character', function () {
    $result = Password::create('MyPassword123');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();

    $messages = array_map(fn ($e) => $e->getMessage(), $result->getErrors());
    expect(implode(' ', $messages))->toContain('special character');
});

test('accumulates all validation errors for weak password', function () {
    $result = Password::create('weak');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->count())->toBeGreaterThanOrEqual(4); // Multiple validation failures (length, uppercase, number, special char)
});

test('validates password at minimum length boundary', function () {
    $password = Password::create('MyP@ssw1'); // 8 characters - minimum length

    expect($password)->toBeInstanceOf(Password::class);
});

test('validates password at maximum length boundary', function () {
    $password = Password::create('MyP@ssw0rd123456');

    expect($password)->toBeInstanceOf(Password::class);
});
