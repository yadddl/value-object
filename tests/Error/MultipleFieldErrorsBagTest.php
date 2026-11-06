<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\InvalidValue;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Error\ValidationFailedError;

test('orFail throws ValidationFailedError with field messages', function () {
    $errors = MultipleFieldErrorsBag::fromArray([
        'email' => [new InvalidValue('Invalid email')],
    ]);

    try {
        $errors->orFail();
        expect(false)->toBeTrue();
    } catch (ValidationFailedError $exception) {
        expect($exception->getMessage())->toBe('[email] Invalid email')
            ->and($exception->getErrors())->toBe($errors);
    }
});
