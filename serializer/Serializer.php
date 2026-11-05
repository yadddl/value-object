<?php

declare(strict_types=1);

namespace Yadddl\Serializer;

interface Serializer
{
    public function serialize(mixed $value): mixed;
}
