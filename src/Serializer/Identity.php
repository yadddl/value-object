<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Serializer;

final class Identity implements Serializer
{
    public function serialize(mixed $object): mixed
    {
        return $object;
    }
}
