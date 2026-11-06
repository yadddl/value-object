<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\InvalidField;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Error\ValidationFailedError;

test('ValidationFailedError exposes ErrorsBag and extends DomainError directly', function () {
    $bag = ErrorsBag::fromMessages('required');
    $error = new ValidationFailedError($bag);

    expect($error)->toBeInstanceOf(DomainError::class)
        ->and($error)->not->toBeInstanceOf(InvalidField::class)
        ->and($error->getErrors())->toBe($bag)
        ->and($error->getMessage())->toContain('required');
});

test('ValidationFailedError exposes MultipleFieldErrorsBag', function () {
    $bag = MultipleFieldErrorsBag::fromArray([
        'email' => ErrorsBag::fromMessages('invalid')->getErrors(),
    ]);
    $error = new ValidationFailedError($bag);

    expect($error->getErrors())->toBe($bag);
});
