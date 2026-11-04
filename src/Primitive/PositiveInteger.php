<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

readonly class PositiveInteger extends Integer
{
    protected const MIN = 0;
}
