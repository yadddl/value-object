<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

readonly class NotEmptyText extends Text
{
    protected const REGEX = '/./';
}
