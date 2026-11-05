<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\Integer;
use Yadddl\ValueObject\Primitive\PositiveInteger;

test('creates valid integer', function () {
    $integer = Integer::create(42);

    expect($integer)->toBeInstanceOf(Integer::class)
        ->and($integer->value)->toBe(42)
        ->and((string) $integer)->toBe('42');
});

test('rejects non integers', function () {
    expect(Integer::create('42'))->toBeInstanceOf(ErrorsBag::class);
});

test('equals compares values', function () {
    $a = Integer::create(1)->orFail();
    $b = Integer::create(1)->orFail();
    $c = Integer::create(2)->orFail();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

test('positive integer accepts zero and rejects negatives', function () {
    expect(PositiveInteger::create(0))->toBeInstanceOf(PositiveInteger::class)
        ->and(PositiveInteger::create(10))->toBeInstanceOf(PositiveInteger::class)
        ->and(PositiveInteger::create(-1))->toBeInstanceOf(ErrorsBag::class);
});
