<?php

use Yadddl\ValueObject\Error\Error;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;

test('orFail throws with field messages', function () {
    $errors = MultipleFieldErrorsBag::fromArray([
        'email' => [new Error('Invalid email')],
    ]);

    expect(fn () => $errors->orFail())
        ->toThrow(InvalidArgumentException::class, '[email] Invalid email');
});
