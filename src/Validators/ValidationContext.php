<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

use Yadddl\ValueObject\Error\Error;
use Yadddl\ValueObject\Error\ErrorsBag;

/**
 * Validation Context Pattern
 *
 * Holds a value being validated and accumulates errors as validations run.
 * Can be passed through PHP 8.5 pipes for elegant functional validation.
 */
readonly class ValidationContext
{
    /**
     * @param mixed $value The value being validated
     * @param array<string> $errors Accumulated error messages
     */
    private function __construct(
        private mixed $value,
        private array $errors
    ) {
    }

    /**
     * Create a new validation context for a value
     */
    protected static function of(mixed $value): self
    {
        return new static($value, []);
    }

    /**
     * Get the value being validated
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get all accumulated errors as ErrorsBag
     */
    public function getErrors(): ErrorsBag
    {
        if (empty($this->errors)) {
            return ErrorsBag::empty();
        }

        $errorObjects = array_map(
            fn ($message) => new Error($message),
            $this->errors
        );

        return new ErrorsBag($errorObjects);
    }

    /**
     * Check if validation has any errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed (no errors)
     */
    public function isValid(): bool
    {
        return !$this->hasErrors();
    }

    /**
     * Add an error to the context
     */
    public function addError(string $error): self
    {
        return new static($this->value, [...$this->errors, $error]);
    }

    /**
     * Validate using a predicate function
     * If validation fails, adds the error message to the context
     * Returns the context (for pipe chaining)
     */
    public function validate(callable $predicate, string $errorMessage): self
    {
        $isValid = $predicate($this->value);

        if (!$isValid) {
            return $this->addError($errorMessage);
        }

        return $this;
    }

    /**
     * Map the value if validation is still valid
     * Useful for transformations during validation
     *
     * @internal Use ValidationContext::map() for pipe chains
     */
    protected function applyMap(callable $mapper): static
    {
        if ($this->hasErrors()) {
            return $this;
        }

        return new static($mapper($this->value), $this->errors);
    }

    /**
     * Static map function for use in pipe chains
     * Returns a closure that applies the mapper to the context's value
     *
     * @param callable $mapper Function that transforms the value
     * @return \Closure Closure that takes ValidationContext and returns ValidationContext
     */
    public static function map(callable $mapper): \Closure
    {
        return static fn (ValidationContext $context) => $context->applyMap($mapper);
    }

    /**
     * Static mapArray function for use in pipe chains
     * Maps array values only if the value is an array, otherwise passes through
     *
     * @param callable $mapper Function that transforms the array
     * @return \Closure Closure that takes ValidationContext and returns ValidationContext
     */
    public static function mapArray(callable $mapper): \Closure
    {
        return static fn (ValidationContext $context) =>
            is_array($context->getValue())
                ? $context->applyMap($mapper)
                : $context;
    }

    /**
     * Static validateArray function for use in pipe chains
     * Validates array values only if the value is an array, otherwise passes through
     *
     * @param callable $predicate Function that validates the array
     * @param string $errorMessage Error message if validation fails
     * @return \Closure Closure that takes ValidationContext and returns ValidationContext
     */
    public static function validateArray(callable $predicate, string $errorMessage): \Closure
    {
        return static fn (ValidationContext $context) =>
            is_array($context->getValue())
                ? $context->validate($predicate, $errorMessage)
                : $context;
    }

    /**
     * Promise-like "then" method: if validation passed, execute callback with the value
     * If validation failed, return ErrorsBag immediately
     * This allows elegant chaining: context->then(fn($value) => new ValueObject($value))
     *
     * @param callable $callback Function that receives the validated value and returns the result
     * @return ErrorsBag|mixed ErrorsBag if validation failed, callback result if valid
     */
    public function then(callable $callback)
    {
        return $this->hasErrors()
            ? $this->getErrors()
            : $callback($this->getValue());
    }

    /**
     * Merge errors from multiple contexts into an ErrorsBag
     * Useful when validating entity properties separately
     */
    public static function mergeErrors(self ...$contexts): ErrorsBag
    {
        $errorsBag = ErrorsBag::empty();

        foreach ($contexts as $context) {
            if ($context->hasErrors()) {
                $errorsBag = $errorsBag->addAll($context->getErrors());
            }
        }
        return $errorsBag;
    }

    /**
     * Extract validated values from multiple contexts
     * Returns array of field => value for valid contexts
     */
    public static function extractValues(array $fieldContexts): array
    {
        $values = [];
        foreach ($fieldContexts as $field => $context) {
            if ($context instanceof self && $context->isValid()) {
                $values[$field] = $context->getValue();
            }
        }
        return $values;
    }
}
