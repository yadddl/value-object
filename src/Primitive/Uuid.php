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

final readonly class Uuid implements Failable, ValidatedValue, \Stringable
{
    use FailableSuccess;

    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): self|ErrorsBag
    {
        $context = self::validate($value);

        return $context->isValid()
            ? new self(strtolower($context->getValue()))
            : $context->getErrors();
    }

    public static function validate(mixed $value): ValidationContext
    {
        return $value
            |> StringValue::from(...)
            |> self::isUuid('Invalid UUID format');
    }

    /**
     * Generate a new random UUID (v4) via ramsey/uuid.
     */
    public static function generate(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    public static function fromRamsey(UuidInterface $uuid): self
    {
        return new self(strtolower($uuid->toString()));
    }

    public function toRamsey(): UuidInterface
    {
        return RamseyUuid::fromString($this->value);
    }

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
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
            if (!is_string($context->getValue())) {
                return $context;
            }

            return $context->validate(
                static fn (string $value): bool => RamseyUuid::isValid($value),
                $message,
            );
        };
    }
}
