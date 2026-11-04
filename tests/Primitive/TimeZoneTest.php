<?php

declare(strict_types=1);

use DateTimeZone as NativeTimeZone;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\TimeZone;

test('creates valid timezone identifiers', function () {
    $zone = TimeZone::create('Europe/Rome');

    expect($zone)->toBeInstanceOf(TimeZone::class)
        ->and($zone->value)->toBe('Europe/Rome')
        ->and((string) $zone)->toBe('Europe/Rome')
        ->and($zone->toDateTimeZone())->toBeInstanceOf(NativeTimeZone::class);
});

test('rejects invalid timezone identifiers', function () {
    expect(TimeZone::create('Not/AZone'))->toBeInstanceOf(ErrorsBag::class)
        ->and(TimeZone::create(123))->toBeInstanceOf(ErrorsBag::class);
});

test('utc and fromDateTimeZone helpers', function () {
    $utc = TimeZone::utc();
    $fromNative = TimeZone::fromDateTimeZone(new NativeTimeZone('America/New_York'));

    expect($utc->value)->toBe('UTC')
        ->and($fromNative->value)->toBe('America/New_York')
        ->and($utc->equals(TimeZone::create('UTC')->orFail()))->toBeTrue()
        ->and($utc->equals($fromNative))->toBeFalse();
});
