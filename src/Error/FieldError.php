<?php

declare(strict_types=1);

namespace Yadddl\DDD\Error;

use JetBrains\PhpStorm\Pure;

final class FieldError
{
    public function __construct(private string $key, private InvalidValueObject $error)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getError(): InvalidValueObject
    {
        return $this->error;
    }

    #[Pure] public function addPrefix(string $prefix): FieldError
    {
        $key = sprintf("%s.%s", $prefix, $this->key);

        return new FieldError($key, $this->error);
    }
}
