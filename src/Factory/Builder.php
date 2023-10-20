<?php

namespace Yadddl\ValueObject\Factory;

use ReflectionClass;
use Yadddl\ValueObject\Error\ValidationError;

interface Builder
{
    public function build(string $type, array|int|float|string|bool $data): mixed;
}