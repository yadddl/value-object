<?php

declare(strict_types=1);

namespace Tests\Support;

enum HttpStatus: int
{
    case Ok = 200;
    case NotFound = 404;
}
