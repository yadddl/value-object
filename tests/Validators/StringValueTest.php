<?php

use Yadddl\ValueObject\Validators\StringValue;

test('accepts string values', function () {
    $context = StringValue::from('hello');

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe('hello');
});

test('rejects non-string values', function () {
    $context = StringValue::from(123);

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be a string');
});

test('accumulates multiple string validation errors', function () {
    $context = 'abc'
        |> StringValue::from(...)
        |> StringValue::minLength(5, 'Too short')
        |> StringValue::hasNumber('Needs a number');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(2);
});

test('regex maps captures to an array', function () {
    $context = '100.50€'
        |> StringValue::from(...)
        |> StringValue::regex('/^(\d+(?:\.\d+)?)(€)$/u');

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe(['100.50€', '100.50', '€']);
});

test('trim strips whitespace on strings', function () {
    $context = '  hello  '
        |> StringValue::from(...)
        |> StringValue::trim();

    expect($context->isValid())->toBeTrue()
        ->and($context->getValue())->toBe('hello');
});

test('trim passes through when value is not a string', function () {
    $context = 123
        |> StringValue::from(...)
        |> StringValue::trim();

    expect($context->isValid())->toBeFalse()
        ->and($context->getValue())->toBe(123);
});

test('string validators pass through when value is not a string', function () {
    $context = 123
        |> StringValue::from(...)
        |> StringValue::notEmpty('empty')
        |> StringValue::minLength(1, 'short')
        |> StringValue::maxLength(10, 'long')
        |> StringValue::email('email')
        |> StringValue::hasUppercase('upper')
        |> StringValue::hasLowercase('lower')
        |> StringValue::hasNumber('number')
        |> StringValue::hasSpecialCharacter('special');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1);
});

test('regex skips when context already has errors', function () {
    $context = 123
        |> StringValue::from(...)
        |> StringValue::regex('/.*/', 'no match');

    expect($context->getErrors()->count())->toBe(1)
        ->and($context->getErrors()->getMessages()[0])->toBe('Value must be a string');
});

test('regex fails with default message', function () {
    $context = 'abc'
        |> StringValue::from(...)
        |> StringValue::regex('/^\d+$/');

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('String does not match required pattern');
});

test('notEmpty rejects empty string with default message', function () {
    $context = ''
        |> StringValue::from(...)
        |> StringValue::notEmpty();

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->getMessages()[0])->toBe('String cannot be empty');
});

test('url accepts valid urls and rejects invalid ones', function () {
    $valid = 'https://example.com'
        |> StringValue::from(...)
        |> StringValue::url();

    $invalid = 'notaurl'
        |> StringValue::from(...)
        |> StringValue::url('bad url');

    expect($valid->isValid())->toBeTrue()
        ->and($invalid->isValid())->toBeFalse()
        ->and($invalid->getErrors()->getMessages()[0])->toBe('bad url');
});

test('url passes through when value is not a string', function () {
    $context = 10
        |> StringValue::from(...)
        |> StringValue::url();

    expect($context->isValid())->toBeFalse()
        ->and($context->getErrors()->count())->toBe(1);
});