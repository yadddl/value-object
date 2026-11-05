<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainException;
use Yadddl\ValueObject\Error\Error;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\ValidationFailedException;

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
    $fromError = ErrorsBag::fromError(new Error('one'));
    $fromMessages = ErrorsBag::fromMessages('a', 'b');

    expect($fromError->count())->toBe(1)
        ->and($fromError->getMessages())->toBe(['one'])
        ->and($fromMessages->count())->toBe(2)
        ->and($fromMessages->getMessages())->toBe(['a', 'b']);
});

test('add and addAll are immutable', function () {
    $original = ErrorsBag::fromMessages('first');
    $withAdd = $original->add(new Error('second'));
    $merged = $original->addAll(ErrorsBag::fromMessages('third'));

    expect($original->count())->toBe(1)
        ->and($withAdd->getMessages())->toBe(['first', 'second'])
        ->and($merged->getMessages())->toBe(['first', 'third']);
});

test('string representation joins messages', function () {
    $bag = ErrorsBag::fromMessages('alpha', 'beta');

    expect((string) $bag)->toBe('alpha; beta');
});

test('orFail throws ValidationFailedException with messages', function () {
    $bag = ErrorsBag::fromMessages('boom');

    try {
        $bag->orFail();
        expect(false)->toBeTrue();
    } catch (ValidationFailedException $exception) {
        expect($exception)->toBeInstanceOf(DomainException::class)
            ->and($exception->getMessage())->toBe('boom')
            ->and($exception->getErrors())->toBe($bag);
    }
});
