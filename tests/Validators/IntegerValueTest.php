<?php

use Yadddl\ValueObject\Validators\IntegerValue;

test('accepts integer values', function () {
    $context = IntegerValue::from(42);

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe(42);
});

test('rejects non-integer values', function () {
    $context = IntegerValue::from('42');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be an integer');
});

test('accumulates range validation errors', function () {
    $context = 200
        |> IntegerValue::from(...)
        |> IntegerValue::min(0, 'Too small')
        |> IntegerValue::max(150, 'Too large');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Too large');
});

test('skips later validators after type failure', function () {
    $context = 'not-a-number'
        |> IntegerValue::from(...)
        |> IntegerValue::min(0, 'Too small');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be an integer');
});

test('min fails below threshold with default message', function () {
    $context = -1
        |> IntegerValue::from(...)
        |> IntegerValue::min(0);

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be at least 0');
});

test('max fails above threshold with default message', function () {
    $context = 10
        |> IntegerValue::from(...)
        |> IntegerValue::max(5);

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be at most 5');
});

test('between accepts values inside range', function () {
    $context = 5
        |> IntegerValue::from(...)
        |> IntegerValue::between(1, 10);

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe(5);
});

test('between rejects values outside range with default message', function () {
    $context = 50
        |> IntegerValue::from(...)
        |> IntegerValue::between(1, 10);

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be between 1 and 10');
});

test('between skips when value is not an integer', function () {
    $context = 'x'
        |> IntegerValue::from(...)
        |> IntegerValue::between(1, 10, 'Out of range');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be an integer');
});

test('max skips when value is not an integer', function () {
    $context = 'x'
        |> IntegerValue::from(...)
        |> IntegerValue::max(10, 'Too large');

    expect($context->getErrors()->count())->toBe(1);
});
