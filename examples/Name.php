<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Error\InvalidValueObject;
use JetBrains\PhpStorm\Pure;
use Stringable;

class Name implements Stringable
{
    private string $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    #[Pure] public function equalsTo(Name $name): bool
    {
        return $this->name === $name->name;
    }

    #[Pure] public static function create(string|Name $name): Name|InvalidValueObject
    {
        if ($name instanceof self) {
            return $name;
        }

        $minimumNameLength = 5;

        if (strlen($name) < $minimumNameLength) {
            return new InvalidValueObject( __CLASS__ . " should be at least $minimumNameLength characters");
        }

        return new Name($name);
    }
}
