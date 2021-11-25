<?php

declare(strict_types=1);

namespace Yadddl\DDD\Error;

use JetBrains\PhpStorm\Pure;

class IntegerTooBig extends ValueError
{
    #[Pure] public function __construct(int $max, int $value)
    {
        parent::__construct("The value {$value} is too big. Maximum {$max}");
    }
}
