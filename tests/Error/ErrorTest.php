<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\Error;

test('legacy Error alias still constructs', function () {
    $error = new Error('Something went wrong');

    expect($error)->toBeInstanceOf(DomainError::class)
        ->and($error->message)->toBe('Something went wrong')
        ->and((string) $error)->toBe('Something went wrong');
});
