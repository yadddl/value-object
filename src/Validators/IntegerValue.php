<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

final readonly class IntegerValue extends ValidationContext
{
    public static function from(mixed $value): self
    {
        return self::isInteger()(self::of($value));
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function isInteger(?string $errorMessage = null): \Closure
    {
        return static function (ValidationContext $context) use ($errorMessage): self {
            $message = $errorMessage ?? 'Value must be an integer';

            return self::asSelf($context)->validate(
                static fn (mixed $value): bool => is_int($value),
                $message,
                'invalid_type',
            );
        };
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function min(int $min, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Value must be at least {$min}";

        return static function (ValidationContext $context) use ($min, $message): self {
            $context = self::asSelf($context);

            if (!is_int($context->getValue())) {
                return $context;
            }

            return $context->validate(
                fn (int $value) => $value >= $min,
                $message,
                'too_small',
            );
        };
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function max(int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Value must be at most {$max}";

        return static function (ValidationContext $context) use ($max, $message): self {
            $context = self::asSelf($context);

            if (!is_int($context->getValue())) {
                return $context;
            }

            return $context->validate(
                fn (int $value) => $value <= $max,
                $message,
                'too_large',
            );
        };
    }

    /**
     * Validate integer is within a range
     *
     * @return \Closure(ValidationContext):self
     */
    public static function between(int $min, int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Value must be between {$min} and {$max}";

        return static function (ValidationContext $context) use ($min, $max, $message): self {
            $context = self::asSelf($context);

            if (!is_int($context->getValue())) {
                return $context;
            }

            return $context->validate(
                fn (int $value) => $value >= $min && $value <= $max,
                $message,
                'out_of_range',
            );
        };
    }

    /**
     * Validated int value. Call only when {@see isValid()} is true after integer rules.
     */
    public function intValue(): int
    {
        $value = $this->getValue();

        if (!is_int($value)) {
            throw new \LogicException('IntegerValue::intValue() requires an int getValue().');
        }

        return $value;
    }

    /**
     * Keep a context as {@see IntegerValue} across custom pipe steps (preserves failures).
     */
    public static function ensure(ValidationContext $context): self
    {
        return self::asSelf($context);
    }

    private static function asSelf(ValidationContext $context): self
    {
        return $context instanceof self ? $context : self::rewrap($context);
    }
}
