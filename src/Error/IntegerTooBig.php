<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

use JetBrains\PhpStorm\Pure;

class IntegerTooBig extends ValueError
{
    #[Pure] public function __construct(int $max, int|float|string|bool $value, string $field)
    {
        parent::__construct("The value {$value} is too big. Maximum {$max}", $field);
    }
}
