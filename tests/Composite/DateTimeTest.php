<?php

declare(strict_types=1);

use DateTimeImmutable;
use DateTimeInterface;
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Composite\DateTime;
use Yadddl\ValueObject\Composite\Time;
use Yadddl\ValueObject\Error\ErrorsBag;

test('creates datetime from string', function () {
    $dateTime = DateTime::create('2024-01-15 08:05:09');

    expect($dateTime)->toBeInstanceOf(DateTime::class)
        ->and((string) $dateTime)->toBe('2024-01-15 08:05:09')
        ->and($dateTime->format('Y/m/d H:i'))->toBe('2024/01/15 08:05')
        ->and($dateTime->toDateTimeInterface())->toBeInstanceOf(DateTimeInterface::class);
});

test('rejects invalid datetime strings', function () {
    expect(DateTime::create('not-a-datetime'))->toBeInstanceOf(ErrorsBag::class);
});

test('creates from datetime interface and copies self', function () {
    $native = new DateTimeImmutable('2024-03-04 05:06:07');
    $created = DateTime::create($native)->orFail();
    $copy = DateTime::create($created)->orFail();

    expect($created->equals($copy))->toBeTrue()
        ->and($created->date->equals(Date::createFromDateTimeInterface($native)))->toBeTrue()
        ->and($created->time->equals(Time::createFromDateTimeInterface($native)))->toBeTrue()
        ->and($created->toInt())->toBe($created->date->toInt() + $created->time->toInt());
});

test('now returns a valid datetime', function () {
    expect(DateTime::now())->toBeInstanceOf(DateTime::class);
});
