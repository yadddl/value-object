<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

use Traversable;

readonly final class NeedsTraversable
{
    private function __construct(public Traversable $items)
    {
    }

    public static function create(Traversable $items): self
    {
        return new self($items);
    }
}
