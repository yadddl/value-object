<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

use JetBrains\PhpStorm\Pure;

class InvalidInteger extends ValueError
{
    #[Pure] public function __construct(int|float|string|bool $value)
    {
        parent::__construct("The value {$value} is not valid");
    }
}
