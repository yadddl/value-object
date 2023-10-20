<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

use JetBrains\PhpStorm\Pure;

class IntegerTooSmall extends ValueError
{
    #[Pure] public function __construct(int $min, int|float|string|bool $value, string $field)
    {
        parent::__construct("The value {$value} is too small. Minimum {$min}", $field);
    }
}
