<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Serializer;

interface SerializerFactory
{
    public function __invoke(): Serializer;
}
