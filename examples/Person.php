<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Error\ValidationError;
use ReflectionException;
use function Yadddl\ValueObject\factory;

final class Person
{
    private function __construct(private Name $name, private Age $age)
    {
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getAge(): Age
    {
        return $this->age;
    }

    /**
     * @throws ReflectionException
     */
    public static function create(string|Name $name, int|Age $age): Person|ValidationError
    {
        return factory(__CLASS__, Name::create($name), Age::create($age));
    }
}
