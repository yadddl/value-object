<?php

declare(strict_types=1);

namespace Yadddl\DDD\Serializer;

interface SerializerFactory
{
    public function __invoke(): Serializer;
}
