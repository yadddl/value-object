<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Collection of errors organized by field name
 * Maps field names to arrays of errors for that field
 */
final readonly class MultipleFieldErrorsBag implements Failable
{
    /**
     * @param array<string, array<Error>> $errorsByField Map of field names to their errors
     */
    public function __construct(
        private array $errorsByField
    ) {
    }

    /**
     * Create an empty errors bag
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Create from a map of field names to error arrays
     */
    public static function fromArray(array $errorsByField): self
    {
        return new self($errorsByField);
    }

    /**
     * Get all errors organized by field
     *
     * @return array<string, array<Error>> Field name => array of errors
     */
    public function getErrorsByField(): array
    {
        return $this->errorsByField;
    }

    /**
     * Get errors for a specific field
     *
     * @param string $fieldName The field name
     * @return array<Error> Errors for the field, or empty array if no errors
     */
    public function getErrorsForField(string $fieldName): array
    {
        return $this->errorsByField[$fieldName] ?? [];
    }

    /**
     * Get all field names that have errors
     *
     * @return array<string> List of field names with errors
     */
    public function getFieldsWithErrors(): array
    {
        return array_keys($this->errorsByField);
    }

    /**
     * Get all errors as a flat array (loses field information)
     *
     * @return array<Error> All errors from all fields
     */
    public function getAllErrors(): array
    {
        $allErrors = [];
        foreach ($this->errorsByField as $errors) {
            $allErrors = array_merge($allErrors, $errors);
        }
        return $allErrors;
    }

    /**
     * Get all error messages as a flat array
     *
     * @return array<string> All error messages from all fields
     */
    public function getAllMessages(): array
    {
        return array_map(
            fn (Error $error) => $error->message,
            $this->getAllErrors()
        );
    }

    /**
     * Check if there are any errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errorsByField);
    }

    /**
     * Check if the bag is empty (no errors)
     */
    public function isEmpty(): bool
    {
        return empty($this->errorsByField);
    }

    /**
     * Get the total number of errors across all fields
     */
    public function count(): int
    {
        $total = 0;
        foreach ($this->errorsByField as $errors) {
            $total += count($errors);
        }
        return $total;
    }

    /**
     * Get the number of fields with errors
     */
    public function getFieldCount(): int
    {
        return count($this->errorsByField);
    }

    /**
     * Convert to string representation
     */
    public function __toString(): string
    {
        $messages = [];
        foreach ($this->errorsByField as $field => $errors) {
            foreach ($errors as $error) {
                $messages[] = "[{$field}] {$error->message}";
            }
        }
        return implode('; ', $messages);
    }

    #[\Override]
    public function orFail(): never
    {
        throw new ValidationFailedException($this);
    }
}
