<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

enum Currency: string
{
    case EUR = '€';
    case USD = '$';

    // ...other currencies
}
