<?php

declare(strict_types=1);

namespace Yadddl\DDD\Examples;

use Yadddl\DDD\Error\InvalidValueObject;
use JetBrains\PhpStorm\Pure;

class Age
{
    private int $age;

    private function __construct(int $age)
    {
        $this->age = $age;
    }

    public function __toString(): string
    {
        return (string)$this->age;
    }

    #[Pure] public function equalsTo(Age $age): bool
    {
        return $this->age === $age->age;
    }

    #[Pure] public static function create(int|Age $age): Age|InvalidValueObject
    {
        if ($age instanceof self) {
            return $age;
        }

        $minimumAge = 18;

        if ($age < $minimumAge) {
            return new InvalidValueObject('age too low', "The age should be at least $minimumAge");
        }

        return new Age($age);
    }
}
