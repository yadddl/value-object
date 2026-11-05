<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

readonly class ArrayValue extends ValidationContext
{
    public static function from(mixed $value): ValidationContext
    {
        return $value
                |> ArrayValue::of(...)
                |> ArrayValue::isArray();
    }

    public static function isArray(?string $errorMessage = null): \Closure
    {
        return static function (ValidationContext $context) use ($errorMessage) {
            $message = $errorMessage ?? "Value must be an array";

            $newContext = $context->validate(
                fn (mixed $value) => is_array($value),
                $message
            );

            return ($newContext->isValid())
                ? ArrayValue::of($context->getValue())
                : $newContext;
        };
    }

    /**
     * Map array values - only operates if value is an array, otherwise passes through
     *
     * @param callable $mapper Function that transforms the array
     * @return \Closure Closure that takes ValidationContext and returns ValidationContext
     */
    #[\Override]
    public static function map(callable $mapper): \Closure
    {
        return static fn (ValidationContext $context) => is_array($context->getValue())
            ? $context->applyMap($mapper)
            : $context;
    }

    public static function mapKeys(array $map): \Closure
    {
        return static fn (ValidationContext $context) => is_array($context->getValue())
            ? $context->applyMap(static function (array $array) use ($map) {
                $result = [];

                foreach ($map as $index => $newIndex) {
                    $result[$newIndex] = $array[$index];
                }

                return $result;
            })
            : $context;
    }

    /**
     * Validate array values - only operates if value is an array, otherwise passes through
     *
     * @param callable $predicate Function that validates the array
     * @param string $errorMessage Error message if validation fails
     * @return \Closure Closure that takes ValidationContext and returns ValidationContext
     */
    #[\Override]
    public static function validateArray(callable $predicate, string $errorMessage): \Closure
    {
        return static fn (ValidationContext $context) => is_array($context->getValue())
            ? $context->validate($predicate, $errorMessage)
            : $context;
    }
}
