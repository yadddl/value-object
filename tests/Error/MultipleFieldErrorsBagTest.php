<?php

use Yadddl\ValueObject\Error\Error;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Error\ValidationFailedException;

test('orFail throws ValidationFailedException with field messages', function () {
    $errors = MultipleFieldErrorsBag::fromArray([
        'email' => [new Error('Invalid email')],
    ]);

    try {
        $errors->orFail();
        expect(false)->toBeTrue();
    } catch (ValidationFailedException $exception) {
        expect($exception->getMessage())->toBe('[email] Invalid email')
            ->and($exception->getErrors())->toBe($errors);
    }
});
