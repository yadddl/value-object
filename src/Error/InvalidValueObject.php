<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

final class InvalidValueObject extends ValueError
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct(
            message: $message,
            previous: $previous
        );
    }
}
