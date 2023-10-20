<?php

declare(strict_types=1);

namespace Yadddl\ValueObject;

use ReflectionException;
use Yadddl\ValueObject\Error\ValidationError;
use Yadddl\ValueObject\Factory\BuilderImpl;

/**
 * @template T of object
 *
 * @psalm-param class-string<T> $target
 * @param array|int|string|bool|float $data
 * @return ValidationError|object
 *
 */
function factory(string $target, array|int|string|bool|float $data)
{
    $factory = new BuilderImpl();
    return $factory->build($target, $data);
}
