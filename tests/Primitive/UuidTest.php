<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid as RamseyUuid;
use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\InvalidEntity;
use Yadddl\ValueObject\Primitive\Uuid;

final class SampleInvalidId extends InvalidEntity
{
    public static function invalid(): self
    {
        return new self('Sample id is invalid');
    }
}

final readonly class SampleId extends Uuid
{
    protected static function mapFailures(ErrorsBag $errors): ErrorsBag
    {
        return ErrorsBag::fromError(SampleInvalidId::invalid());
    }
}

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

test('generate and new create a valid uuid', function () {
    $uuid = Uuid::generate();
    $fresh = Uuid::new();

    expect($uuid)->toBeInstanceOf(Uuid::class)
        ->and(RamseyUuid::isValid($uuid->value))->toBeTrue()
        ->and($fresh)->toBeInstanceOf(Uuid::class)
        ->and(RamseyUuid::isValid($fresh->value))->toBeTrue();
});

test('round-trips through ramsey', function () {
    $ramsey = RamseyUuid::uuid4();
    $uuid = Uuid::fromRamsey($ramsey);

    expect($uuid->toRamsey()->toString())->toBe(strtolower($ramsey->toString()))
        ->and($uuid->equals(Uuid::create($ramsey->toString())->orFail()))->toBeTrue();
});

test('subclass new returns subclass instance with typed failures', function () {
    $id = SampleId::new();

    expect($id)->toBeInstanceOf(SampleId::class)
        ->and(RamseyUuid::isValid($id->value))->toBeTrue();

    $bag = SampleId::create('not-a-uuid');
    expect($bag)->toBeInstanceOf(ErrorsBag::class);
    /** @var ErrorsBag $bag */
    expect($bag->getErrors()[0])->toBeInstanceOf(SampleInvalidId::class)
        ->and($bag->getErrors()[0])->toBeInstanceOf(DomainError::class);
});

test('equals is type-safe per subclass', function () {
    $a = SampleId::create('550e8400-e29b-41d4-a716-446655440000')->orFail();
    $b = SampleId::create('550e8400-e29b-41d4-a716-446655440000')->orFail();
    $c = Uuid::create('550e8400-e29b-41d4-a716-446655440000')->orFail();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});
