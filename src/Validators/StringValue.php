<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

final readonly class StringValue extends ValidationContext
{
    public static function from(mixed $value): self
    {
        return self::isString()(self::of($value));
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function isString(?string $errorMessage = null): \Closure
    {
        return static function (ValidationContext $context) use ($errorMessage): self {
            $message = $errorMessage ?? 'Value must be a string';

            return self::asSelf($context)->validate(
                static fn (mixed $value): bool => is_string($value),
                $message,
                'invalid_type',
            );
        };
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function notEmpty(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'String cannot be empty';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => !empty($value),
            $message,
            'empty',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function minLength(int $min, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String must be at least {$min} characters";

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => strlen($value) >= $min,
            $message,
            'too_small',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function maxLength(int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String must be at most {$max} characters";

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => strlen($value) <= $max,
            $message,
            'too_large',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function email(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Invalid email format';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            $message,
            'invalid_format',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function url(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Invalid URL format';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => filter_var($value, FILTER_VALIDATE_URL) !== false,
            $message,
            'invalid_format',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function hasUppercase(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one uppercase letter';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => preg_match('/[A-Z]/', $value) === 1,
            $message,
            'invalid_format',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function hasLowercase(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one lowercase letter';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => preg_match('/[a-z]/', $value) === 1,
            $message,
            'invalid_format',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function hasNumber(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one number';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => preg_match('/[0-9]/', $value) === 1,
            $message,
            'invalid_format',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function hasSpecialCharacter(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Must contain at least one special character';

        return static fn (ValidationContext $context): self => self::validateString(
            $context,
            fn (string $value) => preg_match('/[^a-zA-Z0-9]/', $value) === 1,
            $message,
            'invalid_format',
        );
    }

    /**
     * @return \Closure(ValidationContext):self
     */
    public static function trim(): \Closure
    {
        return static function (ValidationContext $context): self {
            $context = self::asSelf($context);

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
     *
     * @return \Closure(ValidationContext):self
     */
    public static function matches(string $pattern, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'String does not match required pattern';

        return static function (ValidationContext $context) use ($pattern, $message): self {
            $context = self::asSelf($context);

            if (!is_string($context->getValue())) {
                return $context;
            }

            return $context->validate(
                static fn (string $value): bool => $pattern !== '' && preg_match($pattern, $value) === 1,
                $message,
                'invalid_format',
            );
        };
    }

    /**
     * Validate against a regex and replace the value with preg_match captures.
     *
     * @return \Closure(ValidationContext):self
     */
    public static function regex(string $pattern, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'String does not match required pattern';

        return static function (ValidationContext $context) use ($pattern, $message): self {
            $context = self::asSelf($context);

            if ($context->hasErrors() || !is_string($context->getValue())) {
                return $context;
            }

            /** @var non-empty-string $pattern */
            $value = $context->getValue();
            $matches = [];
            $isValid = $pattern !== '' && preg_match($pattern, $value, $matches) === 1;

            if (!$isValid) {
                return $context->addError($message, 'invalid_format');
            }

            return $context->applyMap(fn () => $matches);
        };
    }

    /**
     * Validated string value. Call only when {@see isValid()} is true after string rules.
     */
    public function stringValue(): string
    {
        $value = $this->getValue();

        if (!is_string($value)) {
            throw new \LogicException('StringValue::stringValue() requires a string getValue().');
        }

        return $value;
    }

    /**
     * Keep a context as {@see StringValue} across custom pipe steps (preserves failures).
     */
    public static function ensure(ValidationContext $context): self
    {
        return self::asSelf($context);
    }

    private static function asSelf(ValidationContext $context): self
    {
        return $context instanceof self ? $context : self::rewrap($context);
    }

    private static function validateString(
        ValidationContext $context,
        callable $predicate,
        string $errorMessage,
        string $code = 'invalid',
    ): self {
        $context = self::asSelf($context);

        if (!is_string($context->getValue())) {
            return $context;
        }

        return $context->validate($predicate, $errorMessage, $code);
    }
}
