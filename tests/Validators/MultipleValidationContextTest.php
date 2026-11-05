<?php

declare(strict_types=1);

use Tests\Serializer\Fixtures\Color;
use Tests\Support\SampleAge;
use Tests\Support\SampleName;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Validators\BackedEnumValue;
use Yadddl\ValueObject\Validators\MultipleValidationContext;

test('creates context with all valid values', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
        age: SampleAge::create(30),
    );

    expect($context->isValid())->toBeTrue()
        ->and($context->getValues())->toHaveKeys(['name', 'age'])
        ->and($context->getValues()['name'])->toBe('John Doe')
        ->and($context->getValues()['age'])->toBe(30);
});

test('creates context with errors and collects them by field', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create(''),
        age: SampleAge::create(-5),
    );

    expect($context->isValid())->toBeFalse();

    $errors = $context->getErrors();
    expect($errors)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($errors->hasErrors())->toBeTrue()
        ->and($errors->getFieldCount())->toBe(2)
        ->and($errors->getFieldsWithErrors())->toContain('name')
        ->and($errors->getFieldsWithErrors())->toContain('age');
});

test('mixes valid and invalid values', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
        age: SampleAge::create(-1),
    );

    expect($context->isValid())->toBeFalse()
        ->and($context->getValues())->toHaveKey('name')
        ->and($context->getValues())->not->toHaveKey('age');

    $errors = $context->getErrors();
    expect($errors->getFieldsWithErrors())->toContain('age')
        ->and($errors->getFieldsWithErrors())->not->toContain('name');
});

test('can get errors for specific field', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create(''),
        age: SampleAge::create(30),
    );

    $errors = $context->getErrors();

    expect($errors->getErrorsForField('name'))->not->toBeEmpty()
        ->and($errors->getErrorsForField('age'))->toBeEmpty();
});

test('setup rejects unexpected result types', function () {
    expect(fn () => MultipleValidationContext::setup(name: 'raw-string'))
        ->toThrow(InvalidArgumentException::class, 'Expected ErrorsBag, ValidatedValue, or BackedEnum for field "name"');
});

test('keeps BackedEnum instances without unwrapping to backing value', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
        color: BackedEnumValue::create(Color::class, 'blue'),
    );

    expect($context->isValid())->toBeTrue()
        ->and($context->getValues()['color'])->toBe(Color::Blue)
        ->and($context->getValues()['name'])->toBe('John Doe');
});

test('collects BackedEnumValue errors by field', function () {
    $context = MultipleValidationContext::setup(
        color: BackedEnumValue::create(Color::class, 'green'),
    );

    expect($context->isValid())->toBeFalse()
        ->and($context->getValues())->not->toHaveKey('color')
        ->and($context->getErrors()->getFieldsWithErrors())->toContain('color');
});
