<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Examples\Email;

test('creates valid email', function () {
    $email = Email::create('john@example.com');

    expect($email)->toBeInstanceOf(Email::class)
        ->and($email->value)->toBe('john@example.com');
});

test('returns errors bag for invalid email format', function () {
    $result = Email::create('invalid-email');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getErrors()[0]->message)->toContain('email');
});

test('validates various email formats', function (string $emailAddress) {
    $email = Email::create($emailAddress);

    expect($email)->toBeInstanceOf(Email::class)
        ->and($email->value)->toBe($emailAddress);
})->with([
    'user@example.com',
    'user.name@example.com',
    'user+tag@example.co.uk',
    'user123@example-domain.com',
]);

test('rejects invalid email formats', function (string $invalidEmail) {
    $result = Email::create($invalidEmail);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();
})->with([
    'not-an-email',
    '@example.com',
    'user@',
    'user@.com',
    'user space@example.com',
]);
