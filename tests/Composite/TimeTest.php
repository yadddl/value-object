<?php

declare(strict_types=1);

use DateTimeImmutable;
use Yadddl\ValueObject\Composite\Time;
use Yadddl\ValueObject\Error\ErrorsBag;

test('creates time from string', function () {
    $time = Time::create('08:05:09');

    expect($time)->toBeInstanceOf(Time::class)
        ->and($time->hours)->toBe(8)
        ->and($time->minutes)->toBe(5)
        ->and($time->seconds)->toBe(9)
        ->and((string) $time)->toBe('08:05:09')
        ->and($time->toInt())->toBe(80509);
});

test('rejects invalid time strings', function () {
    expect(Time::create('25:00:00'))->toBeInstanceOf(ErrorsBag::class)
        ->and(Time::fromString('xx'))->toBeInstanceOf(ErrorsBag::class);
});

test('creates from datetime and now', function () {
    $fromDt = Time::createFromDateTimeInterface(new DateTimeImmutable('2024-01-01 13:14:15'));

    expect($fromDt->hours)->toBe(13)
        ->and($fromDt->minutes)->toBe(14)
        ->and($fromDt->seconds)->toBe(15)
        ->and(Time::now())->toBeInstanceOf(Time::class)
        ->and($fromDt->equals(Time::create('13:14:15')->orFail()))->toBeTrue();
});
