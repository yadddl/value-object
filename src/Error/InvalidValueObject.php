<?php

declare(strict_types=1);

namespace Yadddl\DDD\Error;

final class InvalidValueObject
{
    public function __construct(private string $type, private string $message)
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
