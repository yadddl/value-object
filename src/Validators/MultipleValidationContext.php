<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

use BackedEnum;
use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;

/**
 * Handles validation of multiple value objects for entity creation
 * Collects all errors from multiple ValueObject|ErrorsBag results
 */
readonly class MultipleValidationContext
{
    /**
     * @param array<string, mixed> $values Validated values keyed by field name
     * @param MultipleFieldErrorsBag $errorsBag Errors organized by field name
     */
    private function __construct(
        private array $values,
        private MultipleFieldErrorsBag $errorsBag
    ) {
    }

    /**
     * Setup validation context from multiple ValueObject|ErrorsBag|BackedEnum results
     * Each named argument should be the result of a ValueObject::create() or {@see BackedEnumValue::create()} call
     *
     * Successful {@see ValidatedValue} results unwrap to `$result->value`.
     * Successful {@see BackedEnum} results are kept as the enum instance.
     *
     * @param mixed ...$results Named arguments: fieldName => (ValidatedValue|BackedEnum|ErrorsBag)
     * @return static
     */
    public static function setup(...$results): static
    {
        $values = [];
        $errorsByField = [];

        foreach ($results as $fieldName => $result) {
            if ($result instanceof ErrorsBag) {
                $errorsByField[$fieldName] = $result->getErrors();
            } elseif ($result instanceof ValidatedValue) {
                $values[$fieldName] = $result->value;
            } elseif ($result instanceof BackedEnum) {
                $values[$fieldName] = $result;
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Expected ErrorsBag, ValidatedValue, or BackedEnum for field "%s", got %s',
                    (string) $fieldName,
                    get_debug_type($result),
                ));
            }
        }

        $errorsBag = MultipleFieldErrorsBag::fromArray($errorsByField);

        return new static($values, $errorsBag);
    }

    /**
     * Check if all validations passed (no errors)
     */
    public function isValid(): bool
    {
        return $this->errorsBag->isEmpty();
    }

    /**
     * Get all validated values as an associative array
     * Returns array keyed by field names, ready to be spread into constructor
     *
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Get all accumulated errors organized by field
     */
    public function getErrors(): MultipleFieldErrorsBag
    {
        return $this->errorsBag;
    }
}
