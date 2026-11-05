<?php

declare(strict_types=1);

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone as NativeTimeZone;
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Composite\DateTime;
use Yadddl\ValueObject\Composite\DateTimeWithTimeZone;
use Yadddl\ValueObject\Composite\Time;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\TimeZone;

test('creates zoned datetime from string', function () {
    $dateTime = DateTimeWithTimeZone::create('2024-01-15 08:05:09 Europe/Rome');

    expect($dateTime)->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and((string) $dateTime)->toBe('2024-01-15 08:05:09 Europe/Rome')
        ->and($dateTime->timeZone->value)->toBe('Europe/Rome')
        ->and($dateTime->format('Y/m/d H:i e'))->toBe('2024/01/15 08:05 Europe/Rome')
        ->and($dateTime->toDateTimeInterface())->toBeInstanceOf(DateTimeInterface::class)
        ->and($dateTime->toDateTimeImmutable()->getTimezone()?->getName())->toBe('Europe/Rome');
});

test('rejects invalid zoned datetime strings', function () {
    expect(DateTimeWithTimeZone::create('not-a-datetime'))->toBeInstanceOf(ErrorsBag::class)
        ->and(DateTimeWithTimeZone::create('2024-01-15 08:05:09 Not/AZone'))->toBeInstanceOf(ErrorsBag::class)
        ->and(DateTimeWithTimeZone::create('2024-02-30 08:05:09 UTC'))->toBeInstanceOf(ErrorsBag::class);
});

test('creates zoned datetime from offset timezone spellings', function (string $input, string $canonicalZone) {
    $dateTime = DateTimeWithTimeZone::create($input);

    expect($dateTime)->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and($dateTime->timeZone->value)->toBe($canonicalZone)
        ->and((string) $dateTime)->toBe("2024-01-15 08:05:09 {$canonicalZone}");
})->with([
    '+02:00' => ['2024-01-15 08:05:09 +02:00', '+02:00'],
    '+0200 normalized' => ['2024-01-15 08:05:09 +0200', '+02:00'],
]);

test('round-trips offset timezone through structural converter strings', function () {
    $converter = \Yadddl\Serializer\StructuralConverter::create();
    $original = DateTimeWithTimeZone::create('2024-01-15 08:05:09 +0200')->orFail();

    expect($converter->toArray($original))->toBe('2024-01-15 08:05:09 +02:00')
        ->and($converter->fromArray('2024-01-15 08:05:09 +02:00', DateTimeWithTimeZone::class)->equals($original))->toBeTrue();
});

test('creates from datetime interface and copies self', function () {
    $native = new DateTimeImmutable('2024-03-04 05:06:07', new NativeTimeZone('UTC'));
    $created = DateTimeWithTimeZone::create($native)->orFail();
    $copy = DateTimeWithTimeZone::create($created)->orFail();

    expect($created->equals($copy))->toBeTrue()
        ->and($created->date->equals(Date::createFromDateTimeInterface($native)))->toBeTrue()
        ->and($created->time->equals(Time::createFromDateTimeInterface($native)))->toBeTrue()
        ->and($created->timeZone->value)->toBe('UTC');
});

test('createFromDateTimeInterface returns ErrorsBag when timezone is missing', function () {
    $native = new class extends DateTimeImmutable {
        public function __construct()
        {
            parent::__construct('2024-01-15 08:05:09', new NativeTimeZone('UTC'));
        }

        public function getTimezone(): NativeTimeZone|false
        {
            return false;
        }
    };

    expect(DateTimeWithTimeZone::createFromDateTimeInterface($native))->toBeInstanceOf(ErrorsBag::class)
        ->and(DateTimeWithTimeZone::create($native))->toBeInstanceOf(ErrorsBag::class);
});

test('creates from date time and timezone parts', function () {
    $date = Date::create('2024-01-15')->orFail();
    $time = Time::create('08:05:09')->orFail();
    $zone = TimeZone::create('Asia/Tokyo')->orFail();

    $dateTime = DateTimeWithTimeZone::createFromDateTimeAndTimeZone($date, $time, $zone);

    expect((string) $dateTime)->toBe('2024-01-15 08:05:09 Asia/Tokyo')
        ->and($dateTime->withoutTimeZone())->toBeInstanceOf(DateTime::class)
        ->and((string) $dateTime->withoutTimeZone())->toBe('2024-01-15 08:05:09')
        ->and($dateTime->equals(
            DateTimeWithTimeZone::createFromDateTimeAndTimeZone($date, $time, TimeZone::utc())
        ))->toBeFalse();
});

test('now returns a valid zoned datetime', function () {
    $utc = DateTimeWithTimeZone::now(TimeZone::utc());

    expect(DateTimeWithTimeZone::now())->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and(DateTimeWithTimeZone::now('UTC'))->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and(DateTimeWithTimeZone::now(new NativeTimeZone('Europe/Rome')))->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and($utc)->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and($utc->timeZone->value)->toBe('UTC');
});

test('now returns ErrorsBag for invalid timezone string', function () {
    expect(DateTimeWithTimeZone::now('Not/AZone'))->toBeInstanceOf(ErrorsBag::class);
});
