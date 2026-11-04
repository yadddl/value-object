<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\Error;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;

test('empty multiple field bag helpers', function () {
    $bag = MultipleFieldErrorsBag::empty();

    expect($bag->isEmpty())->toBeTrue()
        ->and($bag->hasErrors())->toBeFalse()
        ->and($bag->count())->toBe(0)
        ->and($bag->getFieldCount())->toBe(0)
        ->and($bag->getErrorsByField())->toBe([])
        ->and($bag->getAllErrors())->toBe([])
        ->and($bag->getAllMessages())->toBe([])
        ->and((string) $bag)->toBe('');
});

test('aggregates errors across fields', function () {
    $bag = MultipleFieldErrorsBag::fromArray([
        'name' => [new Error('too short')],
        'age' => [new Error('negative'), new Error('too big')],
    ]);

    expect($bag->hasErrors())->toBeTrue()
        ->and($bag->count())->toBe(3)
        ->and($bag->getFieldCount())->toBe(2)
        ->and($bag->getAllMessages())->toBe(['too short', 'negative', 'too big'])
        ->and((string) $bag)->toBe('[name] too short; [age] negative; [age] too big');
});
