<?php

declare(strict_types=1);

namespace Yadddl\DDD\Primitive;

class PositiveInteger extends Integer
{
    protected int $min = 0;
}
