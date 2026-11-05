<?php

use Yadddl\ValueObject\Validators\ArrayValue;
use Yadddl\ValueObject\Validators\IntegerValue;

test('accepts array values', function () {
    $context = ArrayValue::from(['a' => 1]);

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe(['a' => 1]);
});

test('rejects non-array values', function () {
    $context = ArrayValue::from('not-an-array');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be an array');
});

test('maps array keys', function () {
    $context = ['full', 'amount', 'EUR']
        |> ArrayValue::from(...)
        |> ArrayValue::mapKeys([1 => 'amount', 2 => 'currency']);

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe([
            'amount' => 'amount',
            'currency' => 'EUR',
        ]);
});

test('map transforms arrays', function () {
    $context = [1, 2]
        |> ArrayValue::from(...)
        |> ArrayValue::map(fn (array $values) => array_map(fn (int $n) => $n * 2, $values));

    expect($context->getValue())->toBe([2, 4]);
});

test('map and mapKeys and validateArray pass through non-arrays', function () {
    $context = 'nope'
        |> ArrayValue::from(...)
        |> ArrayValue::map(fn (array $v) => $v)
        |> ArrayValue::mapKeys([0 => 'a'])
        |> ArrayValue::validateArray(fn (array $v) => true, 'bad');

    expect($context->isValid())->toBeFalse()
        ->and($context->getValue())->toBe('nope');
});

test('validateArray adds error when predicate fails', function () {
    $context = ['a' => 1]
        |> ArrayValue::from(...)
        |> ArrayValue::validateArray(fn (array $v) => isset($v['b']), 'missing b');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('missing b');
});

test('isArray uses custom error message', function () {
    $context = IntegerValue::from(1)
        |> ArrayValue::isArray('custom array error');

    expect($context->getErrors()->getMessages()[0])->toBe('custom array error');
});
