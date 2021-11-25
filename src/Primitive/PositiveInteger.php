<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

class PositiveInteger extends Integer
{
    protected int $min = 0;
}
