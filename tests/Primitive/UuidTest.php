<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid as RamseyUuid;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\Uuid;

test('creates valid uuid and normalizes to lowercase', function () {
    $uuid = Uuid::create('550E8400-E29B-41D4-A716-446655440000');

    expect($uuid)->toBeInstanceOf(Uuid::class)
        ->and($uuid->value)->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and((string) $uuid)->toBe('550e8400-e29b-41d4-a716-446655440000');
});

test('rejects invalid uuid', function () {
    expect(Uuid::create('not-a-uuid'))->toBeInstanceOf(ErrorsBag::class)
        ->and(Uuid::create(123))->toBeInstanceOf(ErrorsBag::class);
});

test('generate creates a valid uuid', function () {
    $uuid = Uuid::generate();

    expect($uuid)->toBeInstanceOf(Uuid::class)
        ->and(RamseyUuid::isValid($uuid->value))->toBeTrue();
});

test('round-trips through ramsey', function () {
    $ramsey = RamseyUuid::uuid4();
    $uuid = Uuid::fromRamsey($ramsey);

    expect($uuid->toRamsey()->toString())->toBe(strtolower($ramsey->toString()))
        ->and($uuid->equals(Uuid::create($ramsey->toString())->orFail()))->toBeTrue();
});
