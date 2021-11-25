<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Serializer;

interface Serializer
{
    /**
     * @psalm-param mixed $object
     * @psalm-return mixed
     */
    public function serialize(mixed $object): mixed;
}
