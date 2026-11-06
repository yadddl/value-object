<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Examples\Currency;
use Yadddl\ValueObject\Examples\Money;

test('creates money from string with EUR currency', function () {
    $money = Money::fromString('100.00€');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(10000) // 100.00 in cents
        ->and($money->currency)->toBe(Currency::EUR);
});

test('creates money from string with USD currency', function () {
    $money = Money::fromString('50.50$');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(5050) // 50.50 in cents
        ->and($money->currency)->toBe(Currency::USD);
});

test('creates money from string without decimal part', function () {
    $money = Money::fromString('100€');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(10000) // 100.00 in cents
        ->and($money->currency)->toBe(Currency::EUR);
});

test('creates money from string with one decimal place', function () {
    $money = Money::fromString('99.5€');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(9950) // 99.50 in cents
        ->and($money->currency)->toBe(Currency::EUR);
});

test('creates money from string with whitespace', function () {
    $money = Money::fromString('  100.00€  ');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(10000)
        ->and($money->currency)->toBe(Currency::EUR);
});

test('returns errors bag for empty string', function () {
    $result = Money::fromString('');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();

    // Check that it contains the empty string error
    $messages = $result->getMessages();
    expect($messages)->toContain('Money string cannot be empty');
});

test('returns errors bag for invalid format', function () {
    $result = Money::fromString('invalid');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->getErrors()[0]->getMessage())->toContain('Invalid money format');
});

test('returns errors bag for format without currency symbol', function () {
    $result = Money::fromString('100.00');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();
});

test('returns errors bag for format with wrong currency symbol position', function () {
    $result = Money::fromString('€100.00');

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue();
});

test('creates money using create method', function () {
    $money = Money::create(10000, Currency::EUR);

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(10000)
        ->and($money->currency)->toBe(Currency::EUR);
});

test('returns errors bag for negative amount in create', function () {
    $result = Money::create(-100, Currency::EUR);

    expect($result)->toBeInstanceOf(ErrorsBag::class)
        ->and($result->hasErrors())->toBeTrue()
        ->and($result->count())->toBe(1)
        ->and($result->getErrors()[0]->getMessage())->toBe('Amount cannot be negative');
});

test('creates money with zero amount', function () {
    $money = Money::create(0, Currency::USD);

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(0)
        ->and($money->currency)->toBe(Currency::USD);
});

test('converts money to string with two decimal places', function () {
    $money = Money::create(10050, Currency::EUR);

    expect((string)$money)->toBe('100.50€');
});

test('converts money to string with trailing zeros', function () {
    $money = Money::create(10000, Currency::USD);

    expect((string)$money)->toBe('100.00$');
});

test('converts money to string with single decimal place', function () {
    $money = Money::create(10005, Currency::EUR);

    expect((string)$money)->toBe('100.05€');
});

test('converts money to string with zero amount', function () {
    $money = Money::create(0, Currency::USD);

    expect((string)$money)->toBe('0.00$');
});

test('handles large amounts correctly', function () {
    $money = Money::fromString('999999.99€');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(99999999)
        ->and((string)$money)->toBe('999999.99€');
});

test('converts decimal amounts to cents correctly', function () {
    // 99.99 should convert to 9999 cents
    $money = Money::fromString('99.99€');

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->amount)->toBe(9999); // 99.99 * 100 = 9999
});
