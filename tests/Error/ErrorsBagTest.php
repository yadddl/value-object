<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\InvalidValue;
use Yadddl\ValueObject\Error\ValidationFailedError;

test('empty bag has no errors', function () {
    $bag = ErrorsBag::empty();

    expect($bag->isEmpty())->toBeTrue()
        ->and($bag->hasErrors())->toBeFalse()
        ->and($bag->count())->toBe(0)
        ->and($bag->getErrors())->toBe([])
        ->and($bag->getMessages())->toBe([])
        ->and((string) $bag)->toBe('');
});

test('fromError and fromMessages build bags', function () {
    $fromError = ErrorsBag::fromError(new InvalidValue('one'));
    $fromMessages = ErrorsBag::fromMessages('a', 'b');

    expect($fromError->count())->toBe(1)
        ->and($fromError->getMessages())->toBe(['one'])
        ->and($fromMessages->count())->toBe(2)
        ->and($fromMessages->getMessages())->toBe(['a', 'b'])
        ->and($fromMessages->getErrors()[0])->toBeInstanceOf(InvalidValue::class);
});

test('add and addAll are immutable', function () {
    $original = ErrorsBag::fromMessages('first');
    $withAdd = $original->add(new InvalidValue('second'));
    $merged = $original->addAll(ErrorsBag::fromMessages('third'));

    expect($original->count())->toBe(1)
        ->and($withAdd->getMessages())->toBe(['first', 'second'])
        ->and($merged->getMessages())->toBe(['first', 'third']);
});

test('string representation joins messages', function () {
    $bag = ErrorsBag::fromMessages('alpha', 'beta');

    expect((string) $bag)->toBe('alpha; beta');
});

test('orFail throws ValidationFailedError with messages', function () {
    $bag = ErrorsBag::fromMessages('boom');

    try {
        $bag->orFail();
        expect(false)->toBeTrue();
    } catch (ValidationFailedError $exception) {
        expect($exception)->toBeInstanceOf(DomainError::class)
            ->and($exception->getMessage())->toBe('boom')
            ->and($exception->getErrors())->toBe($bag);
    }
});
