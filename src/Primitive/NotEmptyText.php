<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

class NotEmptyText extends Text
{
    protected string $regex = '/./';

    protected ?string $errorMessage = 'The string should not be empty';
}
