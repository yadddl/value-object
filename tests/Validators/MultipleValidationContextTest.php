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

test('map returns errors without invoking callback when invalid', function () {
    $called = false;

    $result = MultipleValidationContext::setup(
        name: SampleName::create(''),
        age: SampleAge::create(30),
    )->map(function (SampleName $name, SampleAge $age) use (&$called) {
        $called = true;

        return 'ok';
    });

    expect($called)->toBeFalse()
        ->and($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->getFieldsWithErrors())->toContain('name');
});

test('map passes typed VO and enum objects by parameter name', function () {
    $result = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
        age: SampleAge::create(30),
        color: BackedEnumValue::create(Color::class, 'blue'),
    )->map(function (SampleAge $age, Color $color, SampleName $name) {
        return [
            'name' => $name,
            'age' => $age,
            'color' => $color,
        ];
    });

    expect($result['name'])->toBeInstanceOf(SampleName::class)
        ->and($result['name']->value)->toBe('John Doe')
        ->and($result['age'])->toBeInstanceOf(SampleAge::class)
        ->and($result['age']->value)->toBe(30)
        ->and($result['color'])->toBe(Color::Blue);
});

test('map allows subset of fields and optional unused parameters', function () {
    $result = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
        age: SampleAge::create(30),
    )->map(function (SampleName $name, ?SampleAge $unused = null) {
        return $name->value.'|'.($unused === null ? 'skipped' : 'bound');
    });

    // `$unused` is optional and not a setup field → default null (age is available but not requested by name)
    expect($result)->toBe('John Doe|skipped');
});

test('map rejects unknown required parameter names', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
    );

    expect(fn () => $context->map(fn (SampleName $name, SampleAge $age) => true))
        ->toThrow(InvalidArgumentException::class, 'map() parameter "$age" does not match a validated field');
});

test('map rejects incompatible parameter type hints', function () {
    $context = MultipleValidationContext::setup(
        name: SampleName::create('John Doe'),
    );

    expect(fn () => $context->map(fn (SampleAge $name) => true))
        ->toThrow(InvalidArgumentException::class, 'map() parameter "$name" expects');
});
