<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

readonly final class NoFactory
{
    public function __construct(public string $value)
    {
    }
}
