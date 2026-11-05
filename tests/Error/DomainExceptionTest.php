<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainException;
use Yadddl\ValueObject\Error\DomainLogicException;
use Yadddl\ValueObject\Error\Error;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Error\ValidationFailedException;

test('ValidationFailedException exposes ErrorsBag and extends DomainException', function () {
    $bag = ErrorsBag::fromMessages('boom');
    $exception = new ValidationFailedException($bag);

    expect($exception)->toBeInstanceOf(DomainException::class)
        ->and($exception)->toBeInstanceOf(\DomainException::class)
        ->and($exception->getMessage())->toBe('boom')
        ->and($exception->getErrors())->toBe($bag);
});

test('ValidationFailedException exposes MultipleFieldErrorsBag', function () {
    $bag = MultipleFieldErrorsBag::fromArray([
        'email' => [new Error('bad')],
    ]);
    $exception = new ValidationFailedException($bag);

    expect($exception->getErrors())->toBe($bag)
        ->and($exception->getMessage())->toBe('[email] bad');
});

test('DomainLogicException is a DomainException for catch hierarchies', function () {
    $exception = new DomainLogicException('cannot close twice');

    expect($exception)->toBeInstanceOf(DomainException::class)
        ->and($exception->getMessage())->toBe('cannot close twice');

    try {
        throw $exception;
    } catch (DomainException $caught) {
        expect($caught)->toBe($exception);
    }
});
