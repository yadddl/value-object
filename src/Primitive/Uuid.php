<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;
use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;
use Yadddl\ValueObject\Validators\ValidationContext;

/**
 * UUID string VO. Subclass for named domain ids (OrderId, ListingId, …)
 * and override {@see mapFailures()} for typed errors.
 *
 * @example
 * final readonly class OrderId extends Uuid
 * {
 *     protected static function mapFailures(ErrorsBag $errors): ErrorsBag
 *     {
 *         return ErrorsBag::fromError(InvalidOrderId::invalid());
 *     }
 * }
 *
 * $id = OrderId::new();
 */
readonly class Uuid implements Failable, ValidatedValue, \Stringable
{
    use FailableSuccess;

    final private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): static|ErrorsBag
    {
        $context = static::validate($value);

        if (! $context->isValid()) {
            return static::mapFailures($context->getErrors());
        }

        return new static(strtolower($context->getValue()));
    }

    public static function validate(mixed $value): ValidationContext
    {
        return $value
            |> StringValue::from(...)
            |> self::isUuid('Invalid UUID format');
    }

    /**
     * Fresh random UUID (v4). Alias of {@see generate()} for identity-style call sites.
     */
    public static function new(): static
    {
        return static::generate();
    }

    /**
     * Generate a new random UUID (v4) via ramsey/uuid.
     */
    public static function generate(): static
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    public static function fromRamsey(UuidInterface $uuid): static
    {
        return new static(strtolower($uuid->toString()));
    }

    /**
     * Replace library {@see \Yadddl\ValueObject\Error\InvalidValue} bags with domain errors.
     */
    protected static function mapFailures(ErrorsBag $errors): ErrorsBag
    {
        return $errors;
    }

    public function toRamsey(): UuidInterface
    {
        return RamseyUuid::fromString($this->value);
    }

    public function equals(self $other): bool
    {
        return $other instanceof static && $other->value === $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    private static function isUuid(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Invalid UUID format';

        return static function (ValidationContext $context) use ($message) {
            if (! is_string($context->getValue())) {
                return $context;
            }

            return $context->validate(
                static fn (string $value): bool => RamseyUuid::isValid($value),
                $message,
            );
        };
    }
}
