<?php

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Validators\ArrayValue;
use Yadddl\ValueObject\Validators\IntegerValue;
use Yadddl\ValueObject\Validators\ValidationContext;

test('then returns callback result when valid', function () {
    $result = IntegerValue::from(10)->then(fn (int $value) => $value * 2);

    expect($result)->toBe(20);
});

test('then returns errors bag when invalid', function () {
    $result = IntegerValue::from('invalid')->then(fn (int $value) => $value * 2);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();
});

test('mergeErrors combines errors from multiple contexts', function () {
    $first = IntegerValue::from('bad');
    $second = 200
        |> IntegerValue::from(...)
        |> IntegerValue::max(150, 'Too large');

    $errors = ValidationContext::mergeErrors($first, $second);

    expect($errors->count())->toBe(2);
});

test('getErrors returns empty bag when valid', function () {
    $context = IntegerValue::from(1);

    expect($context->getErrors()->isEmpty())->toBeTrue()
        ->and($context->hasErrors())->toBeFalse();
});

test('map transforms value when valid and skips when invalid', function () {
    $mapped = IntegerValue::from(2)
        |> ValidationContext::map(fn (int $value) => $value * 3);

    $skipped = IntegerValue::from('bad')
        |> ValidationContext::map(fn ($value) => 'should-not-run');

    expect($mapped->getValue())->toBe(6)
        ->and($skipped->getValue())->toBe('bad')
        ->and($skipped->isValid())->toBeFalse();
});

test('mapArray only maps arrays', function () {
    $arrayContext = ArrayValue::from(['a' => 1])
        |> ValidationContext::mapArray(fn (array $value) => $value + ['b' => 2]);

    $scalarContext = IntegerValue::from(5)
        |> ValidationContext::mapArray(fn (array $value) => ['x' => 1]);

    expect($arrayContext->getValue())->toBe(['a' => 1, 'b' => 2])
        ->and($scalarContext->getValue())->toBe(5);
});

test('validateArray only validates arrays', function () {
    $failing = ArrayValue::from(['ok' => false])
        |> ValidationContext::validateArray(fn (array $v) => ($v['ok'] ?? false) === true, 'not ok');

    $skipped = IntegerValue::from(5)
        |> ValidationContext::validateArray(fn (array $v) => false, 'should skip');

    expect($failing->isValid())->toBeFalse()
        ->and($failing->getErrors()->getMessages()[0])->toBe('not ok')
        ->and($skipped->isValid())->toBeTrue();
});

test('extractValues returns only valid contexts', function () {
    $values = ValidationContext::extractValues([
        'good' => IntegerValue::from(7),
        'bad' => IntegerValue::from('nope'),
        'ignored' => 'not-a-context',
    ]);

    expect($values)->toBe(['good' => 7]);
});

test('mergeErrors ignores valid contexts', function () {
    $errors = ValidationContext::mergeErrors(
        IntegerValue::from(1),
        IntegerValue::from('bad'),
    );

    expect($errors->count())->toBe(1);
});
