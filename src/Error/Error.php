<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Simple error data structure containing only a message
 */
final readonly class Error
{
    public function __construct(
        public string $message
    ) {
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
