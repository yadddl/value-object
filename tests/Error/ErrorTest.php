<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\Error;

test('error exposes message property', function () {
    $error = new Error('Something went wrong');

    expect($error->message)->toBe('Something went wrong');
});

test('error casts to string via message', function () {
    $error = new Error('Cast me');

    expect((string) $error)->toBe('Cast me');
});
