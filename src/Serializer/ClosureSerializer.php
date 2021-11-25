<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Serializer;

use Closure;

final class ClosureSerializer implements Serializer
{
    private Closure $closure;

    public function __construct()
    {
        $this->closure = static fn (object $value): object => $value;
    }

    public function with(Closure $closure): void
    {
        $this->closure = $closure;
    }

    public function serialize(mixed $object): mixed
    {
        return ($this->closure)($object);
    }
}
