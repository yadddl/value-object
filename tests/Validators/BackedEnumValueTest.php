<?php

declare(strict_types=1);

use Tests\Serializer\Fixtures\Color;
use Tests\Serializer\Fixtures\Priority;
use Tests\Support\HttpStatus;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Validators\BackedEnumValue;

test('creates string-backed enum from backing value', function () {
    $result = BackedEnumValue::create(Color::class, 'red');

    expect($result)->toBe(Color::Red);
});

test('creates int-backed enum from backing value', function () {
    $result = BackedEnumValue::create(HttpStatus::class, 200);

    expect($result)->toBe(HttpStatus::Ok);
});

test('returns existing case instance unchanged', function () {
    $result = BackedEnumValue::create(Color::class, Color::Blue);

    expect($result)->toBe(Color::Blue);
});

test('returns ErrorsBag for unknown backing value', function () {
    $result = BackedEnumValue::create(Color::class, 'green');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->getMessages())->toContain('Invalid value for Color');
});

test('allows custom invalid message', function () {
    $result = BackedEnumValue::create(Color::class, 'green', 'Pick a known color');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->getMessages())->toContain('Pick a known color');
});

test('returns ErrorsBag for incompatible scalar type', function () {
    expect(BackedEnumValue::create(Color::class, 1))->toBeInstanceOf(ErrorsBag::class)
        ->and(BackedEnumValue::create(HttpStatus::class, '200'))->toBeInstanceOf(ErrorsBag::class)
        ->and(BackedEnumValue::create(Color::class, null))->toBeInstanceOf(ErrorsBag::class);
});

test('returns ErrorsBag when class is not a backed enum', function () {
    expect(BackedEnumValue::create(Priority::class, 'High'))->toBeInstanceOf(ErrorsBag::class)
        ->and(BackedEnumValue::create(stdClass::class, 'x'))->toBeInstanceOf(ErrorsBag::class)
        ->and(BackedEnumValue::create('NotARealClass', 'x'))->toBeInstanceOf(ErrorsBag::class);
});

test('rejects instance of a different enum', function () {
    $result = BackedEnumValue::create(Color::class, HttpStatus::Ok);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->getMessages())->toContain('Invalid value for Color');
});
