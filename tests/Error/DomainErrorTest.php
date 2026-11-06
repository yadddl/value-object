<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\Error;

test('domain error exposes message property', function () {
    $error = new DomainError('Something went wrong');

    expect($error->message)->toBe('Something went wrong');
});

test('domain error casts to string via message', function () {
    $error = new DomainError('Cast me');

    expect((string) $error)->toBe('Cast me');
});

test('deprecated Error alias is the same class as DomainError', function () {
    $error = new Error('legacy');

    expect($error)->toBeInstanceOf(DomainError::class)
        ->and($error->message)->toBe('legacy');
});
