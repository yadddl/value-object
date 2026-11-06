<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Thrown when an error bag is forced through {@see Failable::orFail()}.
 *
 * Generic container — not tied to a field or entity. Inspect {@see getErrors()}
 * for the typed {@see DomainError} instances collected in the bag.
 */
final class ValidationFailedError extends DomainError
{
    public function __construct(
        private readonly ErrorsBag|MultipleFieldErrorsBag $errors,
        ?\Throwable $previous = null,
    ) {
        parent::__construct((string) $this->errors, 0, $previous);
    }

    public function getErrors(): ErrorsBag|MultipleFieldErrorsBag
    {
        return $this->errors;
    }
}
