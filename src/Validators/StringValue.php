<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

readonly class StringValue extends ValidationContext
{
    public static function from(mixed $value): ValidationContext
    {
        return $value
            |> StringValue::of(...)
            |> StringValue::isString();
    }

    public static function isString(?string $errorMessage = null): \Closure
    {
        return static function (ValidationContext $context) use ($errorMessage) {
            $message = $errorMessage ?? 'Value must be a string';

            $newContext = $context->validate(
                fn (mixed $value) => is_string($value),
                $message
            );

            return ($newContext->isValid())
                ? StringValue::of((string) $context->getValue())
                : $newContext;
        };
    }

    public static function notEmpty(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'String cannot be empty';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => !empty($value),
            $message,
        );
    }

    public static function minLength(int $min, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String must be at least {$min} characters";

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => strlen($value) >= $min,
            $message,
        );
    }

    public static function maxLength(int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String must be at most {$max} characters";

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => strlen($value) <= $max,
            $message,
        );
    }

    public static function email(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Invalid email format';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            $message,
        );
    }

    public static function url(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Invalid URL format';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => filter_var($value, FILTER_VALIDATE_URL) !== false,
            $message,
        );
    }

    public static function hasUppercase(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one uppercase letter';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => preg_match('/[A-Z]/', $value) === 1,
            $message,
        );
    }

    public static function hasLowercase(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one lowercase letter';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => preg_match('/[a-z]/', $value) === 1,
            $message,
        );
    }

    public static function hasNumber(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one number';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => preg_match('/[0-9]/', $value) === 1,
            $message,
        );
    }

    public static function hasSpecialCharacter(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one special character';

        return static fn (ValidationContext $context) => self::validateString(
            $context,
            fn (string $value) => preg_match('/[^a-zA-Z0-9]/', $value) === 1,
            $message,
        );
    }

    public static function trim(): \Closure
    {
        return static function (ValidationContext $context) {
            if (!is_string($context->getValue())) {
                return $context;
            }

            return $context->applyMap(
                fn (string $value) => trim($value)
            );
        };
    }

    /**
     * Validate against a regex without changing the string value.
     */
    public static function matches(string $pattern, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'String does not match required pattern';

        return static function (ValidationContext $context) use ($pattern, $message) {
            if (!is_string($context->getValue())) {
                return $context;
            }

            return $context->validate(
                static fn (string $value): bool => $pattern !== '' && preg_match($pattern, $value) === 1,
                $message,
            );
        };
    }

    /**
     * Validate against a regex and replace the value with preg_match captures.
     */
    public static function regex(string $pattern, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'String does not match required pattern';

        return static function (ValidationContext $context) use ($pattern, $message) {
            if ($context->hasErrors() || !is_string($context->getValue())) {
                return $context;
            }

            /** @var non-empty-string $pattern */
            $value = $context->getValue();
            $matches = [];
            $isValid = $pattern !== '' && preg_match($pattern, $value, $matches) === 1;

            if (!$isValid) {
                return $context->addError($message);
            }

            return $context->applyMap(fn () => $matches);
        };
    }

    private static function validateString(
        ValidationContext $context,
        callable $predicate,
        string $errorMessage,
    ): ValidationContext {
        if (!is_string($context->getValue())) {
            return $context;
        }

        return $context->validate($predicate, $errorMessage);
    }
}
