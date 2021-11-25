<?php

declare(strict_types=1);

namespace Yadddl\DDD\Error;

use JetBrains\PhpStorm\Pure;

class IntegerTooSmall extends ValueError
{
    #[Pure] public function __construct(int $min, int $value)
    {
        parent::__construct("The value {$value} is too small. Minimum {$min}");
    }
}
