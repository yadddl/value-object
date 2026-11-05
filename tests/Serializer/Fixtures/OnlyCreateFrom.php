<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

readonly final class OnlyCreateFrom
{
    private function __construct(public int $value)
    {
    }

    public static function createFrom(int $value): self
    {
        return new self($value);
    }
}
