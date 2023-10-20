<?php

namespace Yadddl\ValueObject\Unit\Examples;

use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Primitive\PositiveInteger;

final readonly class SimpleCompositeObject
{
    public function __construct(
        public NotEmptyText    $name,
        public PositiveInteger $age,
    ) {}
}