<?php

declare(strict_types=1);

use DateTimeImmutable;
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Error\ErrorsBag;

test('creates date from string', function () {
    $date = Date::create('2024-01-15');

    expect($date)->toBeInstanceOf(Date::class)
        ->and($date->years)->toBe(2024)
        ->and($date->months)->toBe(1)
        ->and($date->days)->toBe(15)
        ->and((string) $date)->toBe('2024-01-15');
});

test('rejects invalid date strings', function () {
    expect(Date::create('2024-02-30'))->toBeInstanceOf(ErrorsBag::class)
        ->and(Date::createFromFormat('nope', 'Y-m-d'))->toBeInstanceOf(ErrorsBag::class)
        ->and(Date::createFrom(30, 2, 2024))->toBeInstanceOf(ErrorsBag::class);
});

test('creates from parts datetime and copies', function () {
    $fromParts = Date::createFrom(1, 2, 2020)->orFail();
    $fromDt = Date::create(new DateTimeImmutable('2020-02-01'))->orFail();
    $copy = Date::create($fromParts)->orFail();

    expect($fromParts->equals($fromDt))->toBeTrue()
        ->and($copy->equals($fromParts))->toBeTrue()
        ->and($fromParts->format('d/m/Y'))->toBe('01/02/2020')
        ->and($fromParts->toInt())->toBe(20200201000000);
});

test('now returns a valid date', function () {
    expect(Date::now())->toBeInstanceOf(Date::class);
});
