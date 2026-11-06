<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Collection of errors for entity validation
 * Accumulates all validation errors from multiple properties
 */
final readonly class ErrorsBag implements Failable
{
    /**
     * @param array<DomainError> $errors List of errors
     */
    public function __construct(
        private array $errors
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
     * Create an errors bag from a single error
     */
    public static function fromError(DomainError $error): self
    {
        return new self([$error]);
    }

    /**
     * Create an errors bag from messages (as generic {@see InvalidValue} instances)
     */
    public static function fromMessages(string ...$messages): self
    {
        return new self(array_map(static fn (string $msg) => new InvalidValue($msg), $messages));
    }

    /**
     * Add an error to the bag
     */
    public function add(DomainError $error): self
    {
        return new self([...$this->errors, $error]);
    }

    /**
     * Add multiple errors to the bag
     */
    public function addAll(ErrorsBag $other): self
    {
        return new self([...$this->errors, ...$other->errors]);
    }

    /**
     * Get all errors
     *
     * @return array<DomainError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all error messages
     *
     * @return array<string>
     */
    public function getMessages(): array
    {
        return array_map(static fn (DomainError $error) => $error->getMessage(), $this->errors);
    }

    /**
     * Check if the bag is empty (no errors)
     */
    public function isEmpty(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if the bag has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get the number of errors
     */
    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * Convert to string (all messages joined)
     */
    public function __toString(): string
    {
        return implode('; ', $this->getMessages());
    }

    #[\Override]
    public function orFail(): never
    {
        throw new ValidationFailedError($this);
    }
}
