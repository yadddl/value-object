<?php

declare(strict_types=1);

use Yadddl\ValueObject\Validators\StringValue;

test('matches accepts strings that satisfy the pattern', function () {
    $context = 'abc'
        |> StringValue::from(...)
        |> StringValue::matches('/^[a-z]+$/');

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe('abc');
});

test('matches rejects strings that do not satisfy the pattern', function () {
    $context = '123'
        |> StringValue::from(...)
        |> StringValue::matches('/^[a-z]+$/', 'letters only');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('letters only');
});

test('matches passes through when value is not a string', function () {
    $context = 10
        |> StringValue::from(...)
        |> StringValue::matches('/.*/');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1);
});
