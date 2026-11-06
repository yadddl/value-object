<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Domain validation / invariant message (value object for error bags).
 *
 * Prefer this over PHP's {@see \Error} / {@see \Exception} and over the deprecated {@see Error} alias.
 */
final readonly class DomainError
{
    public function __construct(
        public string $message,
    ) {
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
