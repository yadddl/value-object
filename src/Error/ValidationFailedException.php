<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Thrown when an error bag is forced through {@see Failable::orFail()}.
 *
 * Maps typically to HTTP 422 — the original bag is available via {@see getErrors()}.
 */
final class ValidationFailedException extends DomainException
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
