<?php

declare(strict_types=1);

namespace Yadddl\DDD\Serializer;

final class Identity implements Serializer
{
    public function serialize(mixed $value): mixed
    {
        return $value;
    }
}
